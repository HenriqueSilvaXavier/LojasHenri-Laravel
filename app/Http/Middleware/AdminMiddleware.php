<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            // Usuário não autenticado: redireciona para login
            return redirect('/login');
        }

        if (auth()->user()->role === 'admin') {
            // Usuário admin: deixa seguir
            return $next($request);
        }

        // Usuário não admin (cliente): redireciona para /
        return redirect('/');
    }
}
