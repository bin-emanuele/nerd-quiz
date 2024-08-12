<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('AuthenticateAs middleware', [
            'user' => $request->user(),
            'partecipant' => $request->user('partecipant'),
        ]);

        if (Auth::guard('participant')->check()) {
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        return $next($request);
    }
}
