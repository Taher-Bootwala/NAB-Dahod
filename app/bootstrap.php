<?php
/**
 * bootstrap.php — single entry include for every page/endpoint.
 * Loads config, helpers, classes, starts the session and exposes $repo.
 */

declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/Supabase.php';
require __DIR__ . '/data/seed.php';
require __DIR__ . '/Repo.php';
require __DIR__ . '/auth.php';

/* ---- Session (secure cookie params) ---- */
if (session_status() !== PHP_SESSION_ACTIVE) {
    // App-specific cookie name so we never collide with a stale `PHPSESSID`
    // left by another PHP project on localhost (cookies ignore the port). A
    // leftover Secure `PHPSESSID` cannot be overwritten over plain HTTP, which
    // silently breaks login in a browser that holds one. See README.
    session_name('NABSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (($_SERVER['HTTPS'] ?? '') === 'on'),
    ]);
    session_start();
}

/* ---- Security headers (defence in depth) ---- */
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    // CSP allows Tailwind CDN + fonts + Supabase + Unsplash demo images + maps.
    $csp = "default-src 'self'; "
        . "img-src 'self' data: blob: https:; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; "
        . "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; "
        . "font-src 'self' https://fonts.gstatic.com data:; "
        . "connect-src 'self' https://*.supabase.co; "
        . "frame-src https://www.google.com https://maps.google.com; "
        . "object-src 'none'; base-uri 'self';";
    header("Content-Security-Policy: $csp");
}

/* ---- Globals ---- */
$supabase = new Supabase();
$repo = new Repo($supabase);
