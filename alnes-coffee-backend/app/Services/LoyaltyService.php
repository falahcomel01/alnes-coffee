<?php

namespace App\Services;

use App\Models\{Customer, Order, LoyaltyPoint, LoyaltyRule, LoyaltyReward, LoyaltyRedemption};
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public function earnPoints(Order $order): ?LoyaltyPoint
    {
        $customer = Customer::where('phone', $order->customer_phone)->first();
        if (!$customer) return null;

        $rule = LoyaltyRule::where('is_active', true)
            ->where('type', 'transaction')
            ->where('minimum_transaction', '<=', $order->grand_total)
            ->orderByDesc('earn_per_amount')
            ->first();

        if (!$rule) return null;

        $basePoints  = $rule->calculatePoints((float) $order->grand_total);
        $tierBonus   = $this->getTierMultiplier($customer->tier);
        $finalPoints = (int) floor($basePoints * $tierBonus);

        if ($finalPoints <= 0) return null;

        return DB::transaction(function () use ($customer, $order, $rule, $finalPoints) {
            $balanceBefore = $customer->points_balance;
            $balanceAfter  = $balanceBefore + $finalPoints;

            $point = LoyaltyPoint::create([
                'customer_id'    => $customer->id,
                'order_id'       => $order->id,
                'rule_id'        => $rule->id,
                'type'           => 'earn',
                'points'         => $finalPoints,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'description'    => "Poin dari order {$order->invoice_number}",
                'expired_at'     => now()->addYear(),
            ]);

            $customer->increment('points_balance', $finalPoints);
            $customer->increment('total_points_earned', $finalPoints);
            $customer->recalculateTier();
            $order->update(['customer_id' => $customer->id]);

            return $point;
        });
    }

    public function redeemReward(Customer $customer, LoyaltyReward $reward, ?Order $order = null): LoyaltyRedemption
    {
        if ($customer->points_balance < $reward->points_required) {
            throw new \Exception('Poin tidak mencukupi.');
        }
        if (!$reward->is_active || $reward->isExpired()) {
            throw new \Exception('Reward tidak tersedia.');
        }
        if (!$reward->hasStock()) {
            throw new \Exception('Stok reward habis.');
        }
        if (!$reward->isAvailableForTier($customer->tier)) {
            throw new \Exception("Reward ini membutuhkan tier minimal {$reward->min_tier}.");
        }

        return DB::transaction(function () use ($customer, $reward, $order) {
            $balanceBefore = $customer->points_balance;
            $balanceAfter  = $balanceBefore - $reward->points_required;

            LoyaltyPoint::create([
                'customer_id'    => $customer->id,
                'order_id'       => $order?->id,
                'type'           => 'redeem',
                'points'         => -$reward->points_required,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'description'    => "Redeem reward: {$reward->name}",
            ]);

            $customer->decrement('points_balance', $reward->points_required);

            if (!is_null($reward->stock)) {
                $reward->decrement('stock');
            }

            return LoyaltyRedemption::create([
                'customer_id'       => $customer->id,
                'loyalty_reward_id' => $reward->id,
                'order_id'          => $order?->id,
                'points_used'       => $reward->points_required,
                'status'            => 'active',
                'expired_at'        => now()->addDays(30),
            ]);
        });
    }

    public function adjustPoints(Customer $customer, int $points, string $description, int $adminId): LoyaltyPoint
    {
        return DB::transaction(function () use ($customer, $points, $description, $adminId) {
            $balanceBefore = $customer->points_balance;
            $balanceAfter  = max(0, $balanceBefore + $points);

            $point = LoyaltyPoint::create([
                'customer_id'    => $customer->id,
                'adjusted_by'    => $adminId,
                'type'           => 'adjustment',
                'points'         => $points,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'description'    => $description,
            ]);

            if ($points > 0) {
                $customer->increment('points_balance', $points);
                $customer->increment('total_points_earned', $points);
            } else {
                $customer->decrement('points_balance', abs($points));
            }

            $customer->recalculateTier();

            return $point;
        });
    }

    public function expirePoints(): int
    {
        $expired = LoyaltyPoint::where('type', 'earn')
            ->where('expired_at', '<=', now())
            ->whereHas('customer', fn($q) => $q->where('points_balance', '>', 0))
            ->get();

        $count = 0;

        foreach ($expired as $point) {
            $customer = $point->customer;
            if (!$customer || $customer->points_balance <= 0) continue;

            DB::transaction(function () use ($customer, $point) {
                $expire = min($point->points, $customer->points_balance);

                LoyaltyPoint::create([
                    'customer_id'    => $customer->id,
                    'type'           => 'expire',
                    'points'         => -$expire,
                    'balance_before' => $customer->points_balance,
                    'balance_after'  => $customer->points_balance - $expire,
                    'description'    => 'Poin kadaluarsa otomatis',
                ]);

                $customer->decrement('points_balance', $expire);
            });

            $count++;
        }

        return $count;
    }

    public function findOrCreateCustomer(string $phone, string $name, ?string $email = null): Customer
    {
        return Customer::firstOrCreate(
            ['phone' => $phone],
            ['name' => $name, 'email' => $email, 'tier' => 'bronze']
        );
    }

    public function getPointSummary(Customer $customer): array
    {
        return [
            'balance'      => $customer->points_balance,
            'total_earned' => $customer->total_points_earned,
            'tier'         => $customer->tier,
            'next_tier'    => $this->getNextTier($customer),
            'history'      => $customer->loyaltyPoints()->latest()->take(10)->get(),
        ];
    }

    private function getTierMultiplier(string $tier): float
    {
        return match($tier) {
            'silver'   => 1.25,
            'gold'     => 1.50,
            'platinum' => 2.00,
            default    => 1.00,
        };
    }

    private function getNextTier(Customer $customer): ?array
    {
        $thresholds = Customer::TIER_THRESHOLDS;
        $tiers      = array_keys($thresholds);
        $current    = array_search($customer->tier, $tiers);
        $next       = $tiers[$current + 1] ?? null;

        if (!$next) return null;

        return [
            'tier'          => $next,
            'points_needed' => max(0, $thresholds[$next] - $customer->total_points_earned),
        ];
    }
}