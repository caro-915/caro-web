<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->is_banned ?? false)) {
            auth()->logout();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Votre compte a été bloqué. Contactez le support.']);
        }

        return $next($request);
    }
}
