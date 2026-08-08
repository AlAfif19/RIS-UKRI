<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Login via SSO UKRI (OAuth2 Authorization Code Grant).
 *
 * Alurnya sama dengan SIKEMAH-UKRI: redirect() -> user login di halaman SSO
 * -> SSO redirect balik ke callback() dengan `code` -> code ditukar ke
 * access_token (/oauth/token) -> access_token dipakai ambil profil
 * (/api/user) -> user lokal RIS dicari/dibuat -> login.
 *
 * BEDA dengan SIKEMAH: RIS hanya menerima role "admin" dari SSO. Role
 * "dosen" dan "mahasiswa" ditolak (403) karena RIS belum punya dashboard
 * untuk peran tersebut.
 *
 * STATUS: aktif hanya kalau SSO_ENABLED=true DAN semua kredensial di .env
 * terisi (lihat ssoAktif()). Selama itu belum terpenuhi, kedua route di sini
 * mengembalikan 404 dan login manual (AuthenticatedSessionController) tetap
 * berjalan seperti biasa.
 */
class SsoController extends Controller
{
    /**
     * Role SSO yang boleh login ke RIS. Tambah di sini kalau nanti RIS
     * punya dashboard untuk role lain (mis. "dosen").
     */
    private const ALLOWED_ROLES = ['admin'];

    /**
     * Redirect ke halaman login SSO UKRI.
     */
    public function redirect(): RedirectResponse
    {
        if (! $this->ssoAktif()) {
            abort(404);
        }

        // State dipakai sebagai proteksi CSRF antara redirect() <-> callback().
        $state = Str::random(40);
        session(['sso_state' => $state]);

        $query = http_build_query([
            'client_id' => config('services.sso.client_id'),
            'redirect_uri' => config('services.sso.redirect'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return redirect($this->url('/oauth/authorize').'?'.$query);
    }

    /**
     * Callback dari SSO UKRI setelah user login di sana.
     */
    public function callback(Request $request): RedirectResponse
    {
        if (! $this->ssoAktif()) {
            abort(404);
        }

        if ($request->has('error')) {
            return redirect()->route('login')
                ->with('error', 'Login SSO dibatalkan.');
        }

        $stateTersimpan = session()->pull('sso_state');

        if (
            $request->filled('state')
            && $stateTersimpan
            && ! hash_equals($stateTersimpan, (string) $request->string('state'))
        ) {
            return redirect()->route('login')
                ->with('error', 'Sesi SSO tidak valid, silakan coba login ulang.');
        }

        if (! $request->filled('code')) {
            return redirect()->route('login')
                ->with('error', 'Kode otorisasi SSO tidak ditemukan.');
        }

        $token = $this->tukarKodeDenganToken((string) $request->string('code'));

        if (! $token) {
            return redirect()->route('login')
                ->with('error', 'Login SSO gagal, silakan coba lagi.');
        }

        $ssoUser = $this->ambilProfilSso($token['access_token'] ?? '');

        if (! $ssoUser) {
            return redirect()->route('login')
                ->with('error', 'Gagal mengambil data akun dari SSO.');
        }

        $roles = $ssoUser['roles'] ?? [];

        if (! array_intersect(self::ALLOWED_ROLES, $roles)) {
            // Termasuk role "dosen" / "mahasiswa" — RIS baru mendukung admin.
            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak memiliki akses admin di RIS.');
        }

        $user = $this->carikanAtauBuatPengguna($ssoUser);

        session(['sso_token' => $token]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        if ($request->session()->has('url.intended')) {
            return redirect()->intended();
        }

        return redirect()->route('dashboard');
    }

    /**
     * Tukar authorization code -> access token lewat /oauth/token.
     * Return null kalau gagal (sudah di-log, pemanggil tinggal redirect balik).
     */
    private function tukarKodeDenganToken(string $code): ?array
    {
        try {
            $response = Http::timeout($this->timeout())->asForm()->post(
                $this->url('/oauth/token'),
                [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('services.sso.client_id'),
                    'client_secret' => config('services.sso.client_secret'),
                    'redirect_uri' => config('services.sso.redirect'),
                    'code' => $code,
                ]
            );

            if ($response->failed()) {
                Log::warning('SSO: gagal menukar authorization code menjadi token', [
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Ambil profil user SSO lewat /api/user pakai access token.
     * Return null kalau gagal (sudah di-log, pemanggil tinggal redirect balik).
     */
    private function ambilProfilSso(string $accessToken): ?array
    {
        if (blank($accessToken)) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout())
                ->withToken($accessToken)
                ->get($this->url('/api/user'));

            if ($response->failed()) {
                Log::warning('SSO: gagal mengambil profil user', [
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Cocokkan ke akun lokal yang sudah ada, atau buat akun baru.
     *
     * Urutan pencocokan: sso_id dulu (akun yang sebelumnya sudah pernah login
     * via SSO), baru email (akun lama yang dibuat sebelum SSO aktif — asal
     * emailnya sama dengan email di SSO, akan otomatis tertaut & tidak
     * dibuatkan akun duplikat).
     *
     * Role "admin" (Spatie) selalu di-sync di sini karena hanya role itu yang
     * lolos pengecekan ALLOWED_ROLES di callback() — jadi aman untuk RIS.
     */
    private function carikanAtauBuatPengguna(array $ssoUser): User
    {
        $ssoId = (string) ($ssoUser['username'] ?? $ssoUser['id'] ?? '');
        $email = (string) ($ssoUser['email'] ?? '');

        $user = User::where('sso_id', $ssoId)->first()
            ?? User::where('email', $email)->first();

        if (! $user) {
            // Endpoint /api/user SSO tidak mengirim nama lengkap (hanya
            // username, email, roles) — nama sementara diisi username.
            $user = User::create([
                'name' => (string) ($ssoUser['name'] ?? $ssoUser['username'] ?? $email),
                'email' => $email,
                'sso_id' => $ssoId,
                'password' => bcrypt(Str::random(40)), // Tidak pernah dipakai untuk login manual
            ]);
        } elseif (blank($user->sso_id)) {
            $user->forceFill(['sso_id' => $ssoId])->save();
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user->syncRoles(['admin']);

        return $user;
    }

    /**
     * Revoke token SSO (kalau ada) lalu logout lokal.
     */
    public function logout(Request $request): RedirectResponse
    {
        $token = session('sso_token.access_token');

        if ($token) {
            try {
                Http::timeout($this->timeout())->withToken($token)->post($this->url('/api/logout'));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * SSO dianggap aktif hanya kalau saklarnya true DAN semua kredensial terisi.
     */
    private function ssoAktif(): bool
    {
        return (bool) config('services.sso.enabled')
            && filled(config('services.sso.base_url'))
            && filled(config('services.sso.client_id'))
            && filled(config('services.sso.client_secret'))
            && filled(config('services.sso.redirect'));
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.sso.base_url'), '/').$path;
    }

    private function timeout(): int
    {
        return (int) config('services.sso.timeout', 10);
    }
}
