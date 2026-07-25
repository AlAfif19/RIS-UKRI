<x-guest-layout>

<div class="card mb-3 border-0 shadow-sm">

    <div class="card-body p-4">

        <div class="pt-2 pb-3">

            <div class="text-center mb-3">

                <img src="{{ asset('assets/img/Logo UKRI.png') }}"
                     style="height:80px">

            </div>

            <h5 class="card-title text-center pb-0 fs-4 fw-bold">

                UKRI SSO

            </h5>

            <p class="text-center small text-muted">

                Centralized Authentication System

            </p>

        </div>

        <form class="row g-3"
              action="{{ route('login') }}"
              method="POST">

            @csrf

            {{-- Username --}}
            <div class="col-12">

                <label class="form-label fw-semibold">

                    Username

                </label>

                <input type="text"
                       name="username"
                       class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}"
                       required
                       autofocus>

                @error('username')

                    <div class="invalid-feedback">

                        {{ $message }}

                    </div>

                @enderror

            </div>

            {{-- Password --}}
            <div class="col-12">

                <label class="form-label fw-semibold">

                    Password

                </label>

                <div class="input-group">

                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required>

                    <span class="input-group-text bg-white"
                          id="togglePassword"
                          style="cursor:pointer;">

                        <i class="bi bi-eye"
                           id="eyeIcon"></i>

                    </span>

                </div>

                @error('password')

                    <div class="invalid-feedback d-block">

                        {{ $message }}

                    </div>

                @enderror

            </div>

            {{-- Remember --}}
            <div class="col-12">

                <div class="form-check">

                    <input type="checkbox"
                           name="remember"
                           class="form-check-input">

                    <label class="form-check-label">

                        Remember Me

                    </label>

                </div>

            </div>

            {{-- Submit --}}
            <div class="col-12">

                <button class="btn btn-primary w-100 fw-bold"
                        type="submit">

                    Login

                </button>

            </div>

        </form>

    </div>

</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const togglePassword = document.getElementById('togglePassword');

    const passwordInput = document.getElementById('password');

    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function () {

        const type = passwordInput.getAttribute('type') === 'password'
            ? 'text'
            : 'password';

        passwordInput.setAttribute('type', type);

        eyeIcon.classList.toggle('bi-eye');

        eyeIcon.classList.toggle('bi-eye-slash');

    });

});

</script>

</x-guest-layout>