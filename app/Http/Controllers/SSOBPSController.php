<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use JKD\SSO\Client\Provider\Keycloak;
use Throwable;

class SSOBPSController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SSOBPSController
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Route Redirect dari SSO BPS.
     * Pengendali route dari aplikasi
     */
    public function ssoBPSRedirect(Request $request): RedirectResponse|Response
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $code = $request->query('code');

        if (! is_string($code) || $code === '') {
            return redirect()->route('login');
        }

        $provider = $this->ssoInstance();

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
        } catch (Throwable $exception) {
            return response('Gagal mendapatkan akses token : ' . $exception->getMessage(), 500);
        }

        try {
            $dataSso = $provider->getResourceOwner($token);
            $kodeSatker = $dataSso->getKodeProvinsi() . $dataSso->getKodeKabupaten();

            if ($kodeSatker !== '5207') {
                return redirect('login')->with('error', 'Maaf, Aplikasi Ini Hanya Untuk Pegawai BPS Kabupaten Sumbawa Barat, Provinsi Nusa Tenggara Barat');
            }

            $user = User::query()->where('email_bps', $dataSso->getEmail())->first();

            if (! $user instanceof User) {
                $user = User::create([
                    'nip' => $dataSso->getNip(),
                    'nip_baru' => $dataSso->getNipBaru(),
                    'username' => $dataSso->getUsername(),
                    'nama' => $dataSso->getName(),
                    'email_bps' => $dataSso->getEmail(),
                    'golongan' => $dataSso->getGolongan(),
                    'jabatan' => $dataSso->getJabatan(),
                    'url_foto' => null,
                ]);
            } else {
                $user->update([
                    'nip' => $dataSso->getNip(),
                    'nip_baru' => $dataSso->getNipBaru(),
                    'username' => $dataSso->getUsername(),
                    'nama' => $dataSso->getName(),
                    'golongan' => $dataSso->getGolongan(),
                    'jabatan' => $dataSso->getJabatan(),
                ]);
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect('/');
        } catch (Throwable $exception) {
            return response('Gagal login: ' . $exception->getMessage(), 500);
        }
    }

    /**
     * Redirect ke SSO BPS saat menuju form login
     * Apabila sudah login SSO, akan terautentifikasi via email_bps
     */
    public function ssoBPSLogin(Request $request): RedirectResponse|Response
    {
        $provider = $this->ssoInstance();

        if (! $request->has('code')) {
            $authUrl = $provider->getAuthorizationUrl();
            $request->session()->put('oauth2state', $provider->getState());

            return redirect()->away($authUrl);
        }

        $expectedState = (string) $request->session()->pull('oauth2state');
        $currentState = $request->query('state');

        if (! is_string($currentState) || $currentState === '' || $currentState !== $expectedState) {
            return response('Invalid state', 419);
        }

        return redirect()->route('sso.redirect', $request->query());
    }

    /**
     * Bypass login, cuma buat di development local!
     * /bypass/?nama=
     */
    public function bypassLogin(Request $request): RedirectResponse|Response
    {
        try {
            $username = $request->query('nama');

            if (! is_string($username) || $username === '') {
                return response('Gagal bypass: username tidak ditemukan.', 400);
            }

            $user = User::query()->where('username', $username)->first();

            if (! $user instanceof User) {
                return response('Gagal bypass: cihuy', 404);
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect('/');
        } catch (Throwable $exception) {
            return response('Gagal bypass: ' . $exception->getMessage(), 500);
        }
    }

    /**
     * Buat logout ala-ala.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * @return Keycloak
     *
     * Function to access Keycloak - BPS SSO
     */
    private function ssoInstance(): Keycloak
    {
        return new Keycloak([
            'authServerUrl' => env('BPS_AUTH_URL'),
            'realm' => env('BPS_REALM'),
            'clientId' => env('BPS_CLIENT_ID'),
            'clientSecret' => env('BPS_CLIENT_SECRET'),
            'redirectUri' => env('BPS_REDIRECT'),
        ]);
    }
}
