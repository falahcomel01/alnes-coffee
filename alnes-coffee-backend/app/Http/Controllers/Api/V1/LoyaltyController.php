<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Services\LoyaltyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly LoyaltyService $loyaltyService) {}

    // ── Cek poin & info customer by phone ───────────────────────
    public function checkPoints(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            return $this->notFoundResponse('Customer belum terdaftar.');
        }

        $summary = $this->loyaltyService->getPointSummary($customer);

        return $this->successResponse(
            data: [
                'customer' => [
                    'id'                  => $customer->id,
                    'name'                => $customer->name,
                    'phone'               => $customer->phone,
                    'tier'                => $customer->tier,
                    'points_balance'      => $customer->points_balance,
                    'total_points_earned' => $customer->total_points_earned,
                    'tier_updated_at'     => $customer->tier_updated_at,
                ],
                'next_tier' => $summary['next_tier'],
                'history'   => $summary['history']->map(fn($p) => [
                    'type'           => $p->type,
                    'points'         => $p->points,
                    'balance_after'  => $p->balance_after,
                    'description'    => $p->description,
                    'created_at'     => $p->created_at,
                ]),
            ],
            message: 'Data poin berhasil diambil.'
        );
    }

    // ── List reward yang tersedia ────────────────────────────────
    public function rewards(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            return $this->notFoundResponse('Customer belum terdaftar.');
        }

        $rewards = LoyaltyReward::where('is_active', true)
            ->where(fn($q) => $q
                ->whereNull('expired_at')
                ->orWhere('expired_at', '>', now())
            )
            ->where(fn($q) => $q
                ->whereNull('stock')
                ->orWhere('stock', '>', 0)
            )
            ->orderBy('points_required')
            ->get()
            ->map(fn($reward) => [
                'id'              => $reward->id,
                'name'            => $reward->name,
                'description'     => $reward->description,
                'image'           => $reward->image,
                'type'            => $reward->type,
                'points_required' => $reward->points_required,
                'value'           => $reward->value,
                'stock'           => $reward->stock,
                'min_tier'        => $reward->min_tier,
                'expired_at'      => $reward->expired_at,
                'can_redeem'      => $customer->points_balance >= $reward->points_required
                                     && $reward->isAvailableForTier($customer->tier),
            ]);

        return $this->successResponse(
            data: [
                'customer_points' => $customer->points_balance,
                'customer_tier'   => $customer->tier,
                'rewards'         => $rewards,
            ],
            message: 'Daftar reward berhasil diambil.'
        );
    }

    // ── Redeem reward ────────────────────────────────────────────
    public function redeem(Request $request): JsonResponse
    {
        $request->validate([
            'phone'     => ['required', 'string'],
            'reward_id' => ['required', 'exists:loyalty_rewards,id'],
            'order_id'  => ['nullable', 'exists:orders,id'],
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            return $this->notFoundResponse('Customer belum terdaftar.');
        }

        $reward = LoyaltyReward::findOrFail($request->reward_id);
        $order  = $request->order_id
            ? \App\Models\Order::find($request->order_id)
            : null;

        try {
            $redemption = $this->loyaltyService->redeemReward($customer, $reward, $order);

            return $this->successResponse(
                data: [
                    'redemption_id'   => $redemption->id,
                    'reward_name'     => $reward->name,
                    'points_used'     => $redemption->points_used,
                    'points_balance'  => $customer->fresh()->points_balance,
                    'status'          => $redemption->status,
                    'expired_at'      => $redemption->expired_at,
                ],
                message: 'Reward berhasil ditukar!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    // ── Riwayat transaksi poin ───────────────────────────────────
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            return $this->notFoundResponse('Customer belum terdaftar.');
        }

        $history = $customer->loyaltyPoints()
            ->with(['order:id,invoice_number', 'rule:id,name'])
            ->latest()
            ->paginate(20);

        return $this->successResponse(
            data: [
                'customer_points' => $customer->points_balance,
                'customer_tier'   => $customer->tier,
                'history'         => $history->map(fn($p) => [
                    'id'             => $p->id,
                    'type'           => $p->type,
                    'points'         => $p->points,
                    'balance_before' => $p->balance_before,
                    'balance_after'  => $p->balance_after,
                    'description'    => $p->description,
                    'invoice'        => $p->order?->invoice_number,
                    'rule'           => $p->rule?->name,
                    'expired_at'     => $p->expired_at,
                    'created_at'     => $p->created_at,
                ]),
                'pagination' => [
                    'current_page' => $history->currentPage(),
                    'last_page'    => $history->lastPage(),
                    'total'        => $history->total(),
                ],
            ],
            message: 'Riwayat poin berhasil diambil.'
        );
    }
}