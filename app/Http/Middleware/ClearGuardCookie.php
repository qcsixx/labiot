<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClearGuardCookie
{
    /**
     * Middleware untuk menghapus cookie guard yang spesifik.
     * Ini bermanfaat untuk mengontrol logout yang lebih granular.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $cookieName): Response
    {
        $response = $next($request);
        
        // Jika request URL mengandung '/logout', tambahkan cookie expire
        if (str_contains($request->url(), '/logout')) {
            $cookie = cookie()->forget($cookieName);
            $response = $response->withCookie($cookie);
        }
        
        return $response;
    }
}
