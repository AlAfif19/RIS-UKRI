<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthenticatedSessionController;

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

    Route::post(
        '/login',
        [AuthenticatedSessionController::class, 'store']
    );

});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::post(
        '/logout',
        [AuthenticatedSessionController::class, 'destroy']
    )->name('logout');

});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Hanya Admin / Super Admin
|
*/

Route::get('/dashboard', [DashboardAnalitikController::class, 'index'])->name('dashboard');
Route::get('/dashboard-analitik', [DashboardAnalitikController::class, 'index'])->name('dashboard-analitik.index');


/*
|--------------------------------------------------------------------------
| Debug Routes
|--------------------------------------------------------------------------
*/

Route::get('/test-auth', function () {

    return response()->json([
        'authenticated' => Auth::check(),
        'user' => Auth::user(),
    ]);

});

/*
|--------------------------------------------------------------------------
| Publikasi Karya Routes
|--------------------------------------------------------------------------
*/
Route::resource('publikasi', PublikasiController::class);
Route::delete('/publikasi-dokumen/{dokumen}', [PublikasiController::class, 'destroyDokumen'])->name('publikasi.dokumen.destroy');
Route::get('/api/dosen/search', [PublikasiController::class, 'apiSearchDosen'])->name('api.dosen.search');
Route::get('/api/mahasiswa/search', [PublikasiController::class, 'apiSearchMahasiswa'])->name('api.mahasiswa.search');
Route::get('/api/mahasiswa/all', [PublikasiController::class, 'apiAllMahasiswa'])->name('api.mahasiswa.all');

/*
|--------------------------------------------------------------------------
| Master Data UKRI (mirror lokal dari api.ukri.web.id)
|--------------------------------------------------------------------------
*/
Route::get('/api/master/fakultas', [MasterDataController::class, 'fakultas'])->name('api.master.fakultas');
Route::get('/api/master/prodi', [MasterDataController::class, 'prodi'])->name('api.master.prodi');
Route::get('/api/master/angkatan', [MasterDataController::class, 'angkatan'])->name('api.master.angkatan');
Route::get('/api/master/peminatan', [MasterDataController::class, 'peminatan'])->name('api.master.peminatan');