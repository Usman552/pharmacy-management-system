<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Minimal "Continue with Google" (OAuth 2.0 authorization-code flow).
 * Only customers can use it: staff accounts always sign in with a password.
 */
class GoogleAuthController extends Controller
{
    private const NEXT = ['checkout' => 'checkout.index', 'account' => 'account.index'];

    public static function enabled(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    private function redirectUri(): string
    {
        return config('services.google.redirect') ?: route('google.callback');
    }

    public function redirect(Request $request)
    {
        abort_unless(self::enabled(), 404);

        $state = Str::random(40);
        session(['google_state' => $state, 'google_next' => array_key_exists((string) $request->query('next'), self::NEXT) ? $request->query('next') : null]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id'     => config('services.google.client_id'),
            'redirect_uri'  => $this->redirectUri(),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'prompt'        => 'select_account',
        ]));
    }

    public function callback(Request $request)
    {
        abort_unless(self::enabled(), 404);

        $expected = session()->pull('google_state');
        $next = session()->pull('google_next');

        if ($request->filled('error') || ! $request->filled('code') || ! $expected || ! hash_equals($expected, (string) $request->state)) {
            return redirect()->route('login')->with('error', 'Google sign-in was cancelled or could not be verified. Please try again.');
        }

        $token = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
            'code'          => $request->code,
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri'  => $this->redirectUri(),
            'grant_type'    => 'authorization_code',
        ]);

        if (! $token->ok() || ! $token->json('access_token')) {
            return redirect()->route('login')->with('error', 'Google sign-in failed. Please try again.');
        }

        $profile = Http::withToken($token->json('access_token'))->timeout(15)->get('https://openidconnect.googleapis.com/v1/userinfo');
        $sub = (string) $profile->json('sub');
        $email = strtolower((string) $profile->json('email'));

        // Only accept an email address Google itself has verified.
        if (! $profile->ok() || $sub === '' || $email === '' || ! filter_var($profile->json('email_verified'), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('login')->with('error', 'Your Google account does not have a verified email address.');
        }

        $user = User::where('google_id', $sub)->first() ?? User::where('email', $email)->first();

        if ($user) {
            if ($user->role !== 'customer') {
                return redirect()->route('login')->with('error', 'Staff accounts must sign in with email and password.');
            }
            if (! $user->is_active) {
                return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact support.');
            }
            if (! $user->google_id) {
                $user->forceFill(['google_id' => $sub, 'email_verified_at' => $user->email_verified_at ?? now()])->save();
            }
        } else {
            $user = new User([
                'name'              => $profile->json('name') ?: Str::before($email, '@'),
                'email'             => $email,
                'password'          => Hash::make(Str::random(40)),   // unusable until they reset it
                'role'              => 'customer',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);
            $user->google_id = $sub;
            $user->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $target = $next && isset(self::NEXT[$next]) ? route(self::NEXT[$next]) : route('account.index');

        return redirect()->to($target)->with('success', 'Signed in as ' . $user->email . '.');
    }
}
