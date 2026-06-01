<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\Reservation;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    use ApiResponse;

    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'date'        => ['required', 'date', 'after_or_equal:today'],
            'time'        => ['required', 'date_format:H:i'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $bookedTableIds = Reservation::where('reservation_date', $request->date)
            ->where('reservation_time', $request->time)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('table_id');

        $availableTables = CafeTable::where('status', 'available')
            ->whereNotIn('id', $bookedTableIds)
            ->get()
            ->map(fn ($t) => [
                'id'           => $t->id,
                'table_number' => $t->table_number,
                'slug'         => $t->slug,
            ]);

        return $this->successResponse(
            data: [
                'date'             => $request->date,
                'time'             => $request->time,
                'guest_count'      => $request->guest_count,
                'available_tables' => $availableTables,
                'is_available'     => $availableTables->isNotEmpty(),
            ],
            message: 'Ketersediaan meja berhasil dicek.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'table_id'         => ['nullable', 'exists:cafe_tables,id'],
            'customer_name'    => ['required', 'string', 'max:100'],
            'customer_phone'   => ['required', 'string', 'max:20'],
            'customer_email'   => ['nullable', 'email'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'reservation_time' => ['required', 'date_format:H:i'],
            'guest_count'      => ['required', 'integer', 'min:1', 'max:20'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        if ($request->table_id) {
            $conflict = Reservation::where('table_id', $request->table_id)
                ->where('reservation_date', $request->reservation_date)
                ->where('reservation_time', $request->reservation_time)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($conflict) {
                return $this->errorResponse('Meja sudah dipesan pada waktu tersebut.', 422);
            }
        }

        $reservation = Reservation::create($request->only([
            'table_id', 'customer_name', 'customer_phone', 'customer_email',
            'reservation_date', 'reservation_time', 'guest_count', 'notes',
        ]));

        return $this->successResponse(
            data: [
                'id'               => $reservation->id,
                'customer_name'    => $reservation->customer_name,
                'customer_phone'   => $reservation->customer_phone,
                'reservation_date' => $reservation->reservation_date->format('d M Y'),
                'reservation_time' => $reservation->reservation_time->format('H:i'),
                'guest_count'      => $reservation->guest_count,
                'status'           => $reservation->status,
                'table'            => $reservation->table?->table_number,
            ],
            message: 'Reservasi berhasil dibuat!'
        );
    }

    public function checkByPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $reservations = Reservation::where('customer_phone', $request->phone)
            ->with('table')
            ->orderByDesc('reservation_date')
            ->orderByDesc('reservation_time')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'id'               => $r->id,
                'reservation_date' => $r->reservation_date->format('d M Y'),
                'reservation_time' => $r->reservation_time->format('H:i'),
                'guest_count'      => $r->guest_count,
                'status'           => $r->status,
                'table'            => $r->table?->table_number ?? 'Belum ditentukan',
                'notes'            => $r->notes,
            ]);

        return $this->successResponse(
            data: ['reservations' => $reservations],
            message: 'Data reservasi berhasil diambil.'
        );
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'phone'               => ['required', 'string'],
            'cancellation_reason' => ['nullable', 'string', 'max:200'],
        ]);

        $reservation = Reservation::where('id', $id)
            ->where('customer_phone', $request->phone)
            ->first();

        if (!$reservation) {
            return $this->notFoundResponse('Reservasi tidak ditemukan.');
        }

        if ($reservation->isCancelled()) {
            return $this->errorResponse('Reservasi sudah dibatalkan.', 422);
        }

        if ($reservation->isCompleted()) {
            return $this->errorResponse('Reservasi sudah selesai, tidak bisa dibatalkan.', 422);
        }

        $reservation->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return $this->successResponse(
            data: ['id' => $reservation->id, 'status' => 'cancelled'],
            message: 'Reservasi berhasil dibatalkan.'
        );
    }
}