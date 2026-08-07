<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Data master (fakultas, prodi, angkatan, peminatan, dosen, mahasiswa) jarang
// berubah, jadi cukup ditarik dari Master Data API UKRI sekali sehari.
// Jalankan manual sekali setelah migrate: php artisan ukri:sync
Schedule::command('ukri:sync')->dailyAt('02:00')->withoutOverlapping();
