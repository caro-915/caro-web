<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ✅ Scheduled tasks pour le système PRO
Schedule::command('subscriptions:expire')->daily()->at('03:00');
Schedule::command('boosts:expire')->daily()->at('03:00');
