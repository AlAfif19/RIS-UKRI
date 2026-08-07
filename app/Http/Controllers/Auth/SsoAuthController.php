<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Str;

class SsoAuthController extends Controller
{
    /**
     * Redirect To OAuth Server
     */
    public function redirect()
    {
        $query = http_build_query([

            'client_id' => config('services.sso.client_id'),

            'redirect_uri' => config('services.sso.redirect'),

            'response_type' => 'code',

            'scope' => '',

        ]);

        return redirect(

            config('services.sso.base_url')
            . '/oauth/authorize?'
            . $query

        );
    }

    /**
     * OAuth Callback
     */
    public function callback(Request $request)
    {

        if ($request->has('error')) {

            return redirect('/login')
                ->with(
                    'error',
                    'Login SSO dibatalkan oleh pengguna.'
                );
        }
        /**
         * Exchange Authorization Code -> Token
         */
        $response = Http::asForm()->post(

            config('services.sso.base_url')

            . '/oauth/token',

            [

                'grant_type' => 'authorization_code',

                'client_id' => config('services.sso.client_id'),

                'client_secret' => config('services.sso.client_secret'),

                'redirect_uri' => config('services.sso.redirect'),

                'code' => $request->code,

            ]

        );

        /**
         * Failed Token Exchange
         */
        if (! $response->successful()) {

            dd($response->json());

        }

        /**
         * Token Data
         */
        $token = $response->json();

        /**
         * Get Authenticated User
         */
        $userResponse = Http::withToken(

            $token['access_token']

        )->get(

            config('services.sso.base_url')

            . '/api/user'

        );

        /**
         * Failed User Request
         */
        if (! $userResponse->successful()) {

            abort(
                403,
                'Failed get user profile.'
            );
        }

        /**
         * User Data
         */
        $ssoUser = $userResponse->json();
        $roles = $ssoUser['roles'] ?? [];

        if (

            ! in_array('admin', $roles)
            &&
            ! in_array('dosen', $roles)
            &&
            ! in_array('mahasiswa', $roles)

        ) {

            abort(
                403,
                'Anda tidak memiliki akses.'
            );

        }

        /**
         * Create / Update Local User
         */
        $user = User::updateOrCreate(

            [
                'email' => $ssoUser['email'],
            ],

            [
                'name' => $ssoUser['username']
                    ?? $ssoUser['name'],

                'password' => bcrypt(
                    Str::random(20)
                ),
            ]

        );

        /**
         * Save OAuth Session
         */
        session([

            'token' => $token,

            'sso_user' => $ssoUser,

        ]);

        /**
         * Login Local User
         */
        Auth::login($user);

        /**
         * Regenerate Session
         */
        $request->session()->regenerate();

        /**
         * Redirect Dashboard
         */
        return redirect()->route('dashboard');
    }
}
