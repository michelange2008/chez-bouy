<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('users:purge-expired')->dailyAt('03:00')->timezone('Europe/Paris')->withoutOverlapping();

Schedule::command('rentals:remind-codes')->dailyAt('09:00')->timezone('Europe/Paris')->withoutOverlapping();
