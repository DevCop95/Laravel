<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotDemoReadonly
{
    /**
     * Bloquea cualquier mutacion cuando la demo esta en modo solo-lectura.
     *
     * Solo afecta a peticiones que cambian datos (POST, PUT, PATCH, DELETE).
     * Las peticiones de lectura (GET, HEAD) siempre pasan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.demo_readonly') && ! $request->isMethodSafe()) {
            $message = 'Esta es una demostracion de solo lectura. Los cambios estan deshabilitados.';

            if ($request->expectsJson()) {
                abort(403, $message);
            }

            return back()->with('error', $message);
        }

        return $next($request);
    }
}
