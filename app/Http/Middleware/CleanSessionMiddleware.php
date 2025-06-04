<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CleanSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Para cada uma das chaves, limpa caso a variável esteja na session.
        foreach (['tipo', 'restricoes', 'variaveis', 'z'] as $key) {
            $request->session()->forget($key);
        }

        // Retorna a execução normal do fluxo.
        return $next($request);
    }
}
