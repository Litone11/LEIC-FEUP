<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RejectBlockedUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->isBlocked()) {
            $reason = $user->activeBlockReason();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => $reason
                    ? 'A sua conta está bloqueada: ' . $reason
                    : 'A sua conta está bloqueada.',
            ]);
        }

        return $next($request);
    }
}
