<?php

namespace Mannaf\Laravel2FA\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if (!config('twofactor.enabled')) {
            return $next($request);
        }

        if (auth()->check() && auth()->user()->google2fa_secret && !session('2fa_passed')) {
            return redirect()->route('twofactor.verify');
        }

        return $next($request);
    }
}
