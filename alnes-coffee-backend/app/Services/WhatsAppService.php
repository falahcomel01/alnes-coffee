<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $url;

    public function __construct()
    {
        $this->token = config('services.fonnte.token');
        $this->url   = config('services.fonnte.url', 'https://api.fonnte.com/send');
    }

    public function send(string $phone, string $message): bool
    {
        // Normalize nomor HP
        $phone = $this->normalizePhone($phone);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->url, [
                'target'  => $phone,
                'message' => $message,
            ]);

            if (!$response->successful()) {
                Log::warning('WhatsApp send failed', [
                    'phone'    => $phone,
                    'response' => $response->body(),
                ]);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('WhatsApp error: ' . $e->getMessage());
            return false;
        }
    }

    private function normalizePhone(string $phone): string
    {
        // Hapus karakter non-angka
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ganti awalan 0 dengan 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    // ── Template pesan ───────────────────────────────────────────

    public function sendOrderConfirmation(string $phone, string $name, string $invoice, float $total): bool
    {
        $message = "✅ *Pesanan Dikonfirmasi!*\n\n"
            . "Halo *{$name}*,\n"
            . "Pesanan kamu sudah kami terima dan sedang diproses.\n\n"
            . "📋 Invoice: *{$invoice}*\n"
            . "💰 Total: *Rp " . number_format($total, 0, ',', '.') . "*\n\n"
            . "Pantau status pesananmu di aplikasi ya! ☕\n\n"
            . "_Alnes Coffee and Venue Batu_";

        return $this->send($phone, $message);
    }

    public function sendPaymentSuccess(string $phone, string $name, string $invoice, float $total): bool
    {
        $message = "💳 *Pembayaran Berhasil!*\n\n"
            . "Halo *{$name}*,\n"
            . "Pembayaran kamu sudah kami terima.\n\n"
            . "📋 Invoice: *{$invoice}*\n"
            . "💰 Total: *Rp " . number_format($total, 0, ',', '.') . "*\n\n"
            . "Pesananmu sedang kami siapkan, ditunggu ya! ☕\n\n"
            . "_Alnes Coffee and Venue Batu_";

        return $this->send($phone, $message);
    }

    public function sendOrderReady(string $phone, string $name, string $invoice): bool
    {
        $message = "🔔 *Pesanan Siap!*\n\n"
            . "Halo *{$name}*,\n"
            . "Pesananmu sudah siap untuk diambil!\n\n"
            . "📋 Invoice: *{$invoice}*\n\n"
            . "Silakan ambil pesananmu di kasir. Selamat menikmati! ☕\n\n"
            . "_Alnes Coffee and Venue Batu_";

        return $this->send($phone, $message);
    }

    public function sendReservationConfirmed(string $phone, string $name, string $code, string $date, string $time, int $guests): bool
    {
        $message = "📅 *Reservasi Dikonfirmasi!*\n\n"
            . "Halo *{$name}*,\n"
            . "Reservasi meja kamu sudah kami konfirmasi.\n\n"
            . "🎫 Kode: *{$code}*\n"
            . "📆 Tanggal: *{$date}*\n"
            . "⏰ Waktu: *{$time}*\n"
            . "👥 Tamu: *{$guests} orang*\n\n"
            . "Tunjukkan kode ini saat tiba. Sampai jumpa! ☕\n\n"
            . "_Alnes Coffee and Venue Batu_";

        return $this->send($phone, $message);
    }

    public function sendLoyaltyPoints(string $phone, string $name, int $points, int $balance): bool
    {
        $message = "⭐ *Poin Loyalty Masuk!*\n\n"
            . "Halo *{$name}*,\n"
            . "Kamu mendapatkan poin dari transaksi terakhir!\n\n"
            . "✨ Poin masuk: *+{$points} poin*\n"
            . "💎 Total saldo: *{$balance} poin*\n\n"
            . "Tukarkan poinmu dengan reward menarik di aplikasi! ☕\n\n"
            . "_Alnes Coffee and Venue Batu_";

        return $this->send($phone, $message);
    }

    public function sendReservationReminder(string $phone, string $name, string $code, string $time): bool
    {
        $message = "⏰ *Reminder Reservasi!*\n\n"
            . "Halo *{$name}*,\n"
            . "Reservasi kamu di Alnes Coffee akan dimulai dalam *2 jam lagi*.\n\n"
            . "🎫 Kode: *{$code}*\n"
            . "⏰ Waktu: *{$time}*\n\n"
            . "Kami tunggu kedatanganmu! ☕\n\n"
            . "_Alnes Coffee and Venue Batu_";

        return $this->send($phone, $message);
    }
}