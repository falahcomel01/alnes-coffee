<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\LoyaltyService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('loyalty:expire-points', function () {
    $count = app(LoyaltyService::class)->expirePoints();
    $this->info("✅ {$count} poin berhasil di-expire.");
})->purpose('Expire loyalty points that have passed their expiration date');

Schedule::command('loyalty:expire-points')->dailyAt('00:00');