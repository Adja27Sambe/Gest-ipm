<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('conventions:alerter-expiration')->dailyAt('08:00');

// Gestion des cotisations
Schedule::command('cotisations:generer-mensuelles')->monthlyOn(1, '01:00');
Schedule::job(new \App\Jobs\RelancerCotisationsImpayees)->dailyAt('02:00');
