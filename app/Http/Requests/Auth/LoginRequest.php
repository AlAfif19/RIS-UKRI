<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [

            /**
             * Login pakai username
             */
            'username' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
            ],

        ];
    }

    /**
     * Authenticate user
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        /**
         * Attempt login. Form field-nya masih bernama "username" (label di
         * tampilan login), tapi tabel `users` hanya punya kolom `email`
         * (tidak ada kolom `username`) — jadi nilainya dicocokkan ke `email`.
         * Sebelum perbaikan ini, Auth::attempt(['username' => ...]) selalu
         * gagal dengan SQL error "Unknown column 'username'" untuk SIAPA PUN
         * yang login manual, terlepas dari kredensialnya benar atau salah.
         */
        if (! Auth::attempt(
            [
                'email' => $this->input('username'),
                'password' => $this->input('password'),
            ],
            $this->boolean('remember')
        )) {

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([

                'username' => trans('auth.failed'),

            ]);
        }

        /**
         * Get authenticated user
         */
        $user = Auth::user();

        /**
         * Clear limiter
         */
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure not rate limited
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts(
            $this->throttleKey(),
            5
        )) {

            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn(
            $this->throttleKey()
        );

        throw ValidationException::withMessages([

            'username' => trans('auth.throttle', [

                'seconds' => $seconds,

                'minutes' => ceil($seconds / 60),

            ]),

        ]);
    }

    /**
     * Throttle key
     */
    public function throttleKey(): string
    {
        return Str::transliterate(

            Str::lower(
                $this->string('username')
            ) . '|' . $this->ip()

        );
    }
}