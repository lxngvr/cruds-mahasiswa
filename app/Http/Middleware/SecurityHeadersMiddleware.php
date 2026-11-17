<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Menambahkan header keamanan pada setiap response aplikasi.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mencegah halaman dirender di dalam iframe (clickjacking).
        $response->headers->set('X-Frame-Options', 'DENY');

        // Mencegah browser menebak tipe konten yang berbeda dari header.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Membatasi informasi referer yang dikirim ke situs lain.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Menonaktifkan akses fitur browser yang tidak dipakai aplikasi.
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // CSP: hanya mengizinkan aset lokal dan CDN Tailwind yang memang dipakai.
        // 'unsafe-inline' dan 'unsafe-eval' diperlukan oleh Tailwind CDN dan Alpine.js.
        $csp = "default-src 'self'; "
            ."script-src 'self' https://cdn.tailwindcss.com 'unsafe-inline' 'unsafe-eval'; "
            ."style-src 'self' 'unsafe-inline' https://fonts.bunny.net; "
            ."font-src 'self' data: https://fonts.bunny.net; "
            ."img-src 'self' data:; "
            ."connect-src 'self'; "
            ."base-uri 'self'; "
            ."form-action 'self'; "
            ."frame-ancestors 'none';";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
