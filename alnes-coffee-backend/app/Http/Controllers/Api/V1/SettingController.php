<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $setting = Setting::instance();

        return $this->successResponse(
            data: [
                'cafe_name'         => $setting->cafe_name,
                'logo'              => $setting->logo_url,
                'address'           => $setting->address,
                'phone'             => $setting->phone,
                'email'             => $setting->email,
                'instagram'         => $setting->instagram,
                'facebook'          => $setting->facebook,
                'tiktok'            => $setting->tiktok,
                'maps_url'          => $setting->maps_url,
                'open_time'         => $setting->open_time,
                'close_time'        => $setting->close_time,
                'tax_percentage'    => $setting->tax_percentage,
                'service_fee'       => $setting->service_fee,
                'is_open'           => $setting->is_open,
                'is_currently_open' => $setting->isCurrentlyOpen(),
            ],
            message: 'Pengaturan café berhasil diambil.'
        );
    }
}