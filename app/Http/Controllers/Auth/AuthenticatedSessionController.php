<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Login RIS sekarang HANYA lewat SSO UKRI — tidak ada lagi form login
 * mandiri (username/password). Halaman /login praktis hanya jadi "pintu
 * masuk" yang langsung meneruskan ke SsoController::redirect(). RIS hanya
 * menerima role "admin" dari SSO (lihat SsoController::ALLOWED_ROLES).
 *
 * Halaman auth.login tetap dirender (bukan langsung redirect dari sini)
 * hanya kalau ada pesan error yang perlu ditampilkan ke user (mis. login
 * SSO gagal, atau akun bukan admin) — supaya pesannya sempat terlihat
 * sebelum user klik ulang "Login dengan SSO UKRI".
 *
 * Logout ditangani oleh SsoController::logout() (lihat routes/web.php),
 * bukan controller ini.
 */
class AuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (session()->has('error')) {
            return view('auth.login');
        }

        return redirect()->route('sso.redirect');
    }
}
