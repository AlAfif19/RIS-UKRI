<x-guest-layout>

<div class="card mb-3 border-0 shadow-sm">

    <div class="card-body p-4">

        <div class="pt-2 pb-3">

            <div class="text-center mb-3">

                <img src="{{ asset('assets/img/Logo UKRI.png') }}"
                     style="height:80px">

            </div>

            <h5 class="card-title text-center pb-0 fs-4 fw-bold">

                RIS UKRI

            </h5>

            <p class="text-center small text-muted">

                Research Information System

            </p>

        </div>

        @if (session('error'))
            <div class="alert alert-danger py-2 small mb-3">
                {{ session('error') }}
            </div>
        @endif

        {{-- Satu-satunya cara login: SSO UKRI. Tidak ada lagi form
             username/password mandiri. RIS hanya menerima role "admin"
             dari SSO. --}}
        @if (config('services.sso.enabled'))
            <a href="{{ route('sso.redirect') }}" class="btn btn-primary w-100 fw-bold">
                Login dengan SSO UKRI
            </a>
        @else
            <div class="alert alert-warning py-2 small mb-0">
                Login SSO belum dikonfigurasi. Hubungi administrator sistem.
            </div>
        @endif

        {{-- Guest login lokal — hanya muncul kalau routes/dev-login.php ada
             (file itu di-gitignore, tidak pernah ikut ter-push). Aman
             ditinggal di view ini karena di production route-nya tidak
             pernah terdaftar. --}}
        @if (Route::has('dev-login'))
            <hr class="my-3">
            <p class="text-center small text-muted mb-2">Guest Login (khusus lokal)</p>
            <div class="d-grid gap-2">
                <a href="{{ route('dev-login', 'admin') }}" class="btn btn-outline-secondary btn-sm">
                    Masuk sebagai Admin
                </a>
                <a href="{{ route('dev-login', 'dosen') }}" class="btn btn-outline-secondary btn-sm">
                    Masuk sebagai Dosen
                </a>
            </div>
        @endif

    </div>

</div>

</x-guest-layout>
