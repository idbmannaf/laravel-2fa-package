<?php

namespace mannaf\Laravel2FA\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip if 2FA is not enabled globally
        if (!config('twofactor.enabled', false)) {
            return $next($request);
        }

        // Skip if user is not authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $secretField = config('twofactor.user_model.secret_field', 'two_factor_secret');
        $enabledField = config('twofactor.user_model.enabled_field', 'two_factor_enabled');
        $sessionKey = config('twofactor.session.key', '2fa_verified');

        // Check if user has 2FA enabled
        if ($user->$secretField && $user->$enabledField) {
            // Check if 2FA has been verified in this session
            if (!Session::get($sessionKey)) {
                // Store user ID in session for verification
                Session::put('2fa:user:id', $user->id);
                Auth::logout();

                return redirect()->route('twofactor.verify')
                    ->with('message', 'Please complete two-factor authentication to continue.');
            }
        }

        return $next($request);
    }
}
