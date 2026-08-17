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
| Sengaja TIDAK pakai Route::redirect('/', '/login') lagi - itu menambah
| satu hop redirect ekstra ('/' -> '/login') yang lalu diteruskan LAGI oleh
| middleware `guest` ke halaman dashboard kalau user ternyata sudah login.
| Kalau suatu saat route bernama "dashboard" tidak ada/berubah, fallback
| bawaan middleware `guest` (Illuminate\Auth\Middleware\RedirectIfAuthenticated)
| akan balik lagi ke '/' - dan berulang terus (ERR_TOO_MANY_REDIRECTS).
| Di bawah ini langsung menentukan tujuannya dalam SATU kali redirect saja,
| tidak bergantung pada fallback middleware `guest` sama sekali.
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard-analitik.index')
        : redirect()->route('login');
});



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
| Guest Login (LOCAL DEV ONLY)
|--------------------------------------------------------------------------
|
| Shortcut login (tanpa SSO) untuk testing role admin/dosen di mesin
| lokal. Logikanya sengaja ditaruh di routes/dev-login.php, sebuah file
| yang di-gitignore (lihat .gitignore) supaya tidak pernah ter-commit
| atau ter-push. File itu tidak ada di repo — kalau tidak ada, blok ini
| tidak melakukan apa-apa, jadi aman di production maupun setelah pull.
*/

if (app()->environment('local')) {
    $devLoginRoutes = base_path('routes/dev-login.php');

    if (is_file($devLoginRoutes)) {
        require $devLoginRoutes;
    }
}

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
| Publikasi Karya Routes (Admin & Dosen)
|--------------------------------------------------------------------------
|
| Bisa diakses Admin maupun Dosen (role dari SSO) — WAJIB login DAN WAJIB
| salah satu dari role tersebut. Pembatasan datanya sendiri (dosen hanya
| lihat publikasi yang nama dia tercantum sebagai penulis, admin lihat
| semuanya) ditangani di dalam PublikasiController, bukan di sini — lihat
| PublikasiController::ownedAuthorIds().
|
*/

Route::middleware(['auth', 'role:admin|dosen'])->group(function () {

    Route::resource('publikasi', PublikasiController::class);
    Route::delete('/publikasi-dokumen/{dokumen}', [PublikasiController::class, 'destroyDokumen'])->name('publikasi.dokumen.destroy');
    Route::get('/api/dosen/search', [PublikasiController::class, 'apiSearchDosen'])->name('api.dosen.search');
    Route::get('/api/dosen/all', [PublikasiController::class, 'apiAllDosen'])->name('api.dosen.all');
    Route::get('/api/mahasiswa/search', [PublikasiController::class, 'apiSearchMahasiswa'])->name('api.mahasiswa.search');
    Route::get('/api/mahasiswa/all', [PublikasiController::class, 'apiAllMahasiswa'])->name('api.mahasiswa.all');
    Route::get('/api/jurnal/all', [PublikasiController::class, 'apiAllJurnal'])->name('api.jurnal.all');
    Route::get('/api/penerbit/all', [PublikasiController::class, 'apiAllPenerbit'])->name('api.penerbit.all');
    Route::get('/api/penulis-lain/all', [PublikasiController::class, 'apiAllPenulisLain'])->name('api.penulis-lain.all');
    Route::get('/api/publikasi/cek-judul', [PublikasiController::class, 'apiSearchJudul'])->name('api.publikasi.cek-judul');

    // Dashboard Analitik — bisa diakses admin maupun dosen. Datanya sendiri
    // dibatasi di dalam DashboardAnalitikController: admin lihat semua,
    // dosen hanya lihat statistik dari publikasi yang dia tercantum sebagai
    // penulis (lihat DashboardAnalitikController::currentDosenId()).
    //
    // Sebelumnya ada JUGA route '/dashboard' (name: 'dashboard') yang
    // mengarah ke controller & view yang SAMA PERSIS - jadi menu sidebar
    // punya 2 item ("Dashboard Analitik" & "Dashboard") yang isinya
    // identik. Route duplikatnya sudah dihapus, cukup satu ini saja.
    Route::get('/dashboard-analitik', [DashboardAnalitikController::class, 'index'])->name('dashboard-analitik.index');

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