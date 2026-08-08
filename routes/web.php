<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\SsoController;

use App\Http\Controllers\PublikasiController;
use App\Http\Controllers\DashboardAnalitikController;
use App\Http\Controllers\MasterDataController;

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
|
| SSO Server Landing Page
|
*/

Route::redirect('/', '/login');



/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get(
        '/login',
        [AuthenticatedSessionController::class, 'create']
    )->name('login');

    Route::get('/auth/sso/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
    Route::get('/auth/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');

});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Dipakai untuk login manual maupun via SSO — kalau sesi berasal dari SSO
    // (ada sso_token di session), tokennya di-revoke dulu sebelum logout lokal.
    Route::post(
        '/logout',
        [SsoController::class, 'logout']
    )->name('logout');

});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Hanya Admin (role dari SSO) — semua route di bawah ini WAJIB login DAN
| WAJIB role admin. Sebelumnya route-route ini tidak dibungkus middleware
| sama sekali sehingga bisa diakses langsung tanpa login (mis. buka
| /dashboard langsung) — sekarang semuanya dipaksa lewat SSO dulu.
|
*/

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', [DashboardAnalitikController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-analitik', [DashboardAnalitikController::class, 'index'])->name('dashboard-analitik.index');

    /*
    |----------------------------------------------------------------------
    | Publikasi Karya Routes
    |----------------------------------------------------------------------
    */
    Route::resource('publikasi', PublikasiController::class);
    Route::delete('/publikasi-dokumen/{dokumen}', [PublikasiController::class, 'destroyDokumen'])->name('publikasi.dokumen.destroy');
    Route::get('/api/dosen/search', [PublikasiController::class, 'apiSearchDosen'])->name('api.dosen.search');
    Route::get('/api/mahasiswa/search', [PublikasiController::class, 'apiSearchMahasiswa'])->name('api.mahasiswa.search');
    Route::get('/api/mahasiswa/all', [PublikasiController::class, 'apiAllMahasiswa'])->name('api.mahasiswa.all');

    /*
    |----------------------------------------------------------------------
    | Master Data UKRI (mirror lokal dari api.ukri.web.id)
    |----------------------------------------------------------------------
    */
    Route::get('/api/master/fakultas', [MasterDataController::class, 'fakultas'])->name('api.master.fakultas');
    Route::get('/api/master/prodi', [MasterDataController::class, 'prodi'])->name('api.master.prodi');
    Route::get('/api/master/angkatan', [MasterDataController::class, 'angkatan'])->name('api.master.angkatan');
    Route::get('/api/master/peminatan', [MasterDataController::class, 'peminatan'])->name('api.master.peminatan');

});