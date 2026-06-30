<?php
/**
 * router.php — front controller for PHP's built-in server:
 *   php -S localhost:8000 -t public public/router.php
 *
 * Serves static assets directly and adds a few pretty routes.
 */

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rawurldecode($uri);
$file = __DIR__ . $uri;

/* Serve existing static files (css/js/img/manifest/etc.) as-is. */
if ($uri !== '/' && is_file($file) && !str_ends_with($uri, '.php')) {
    return false;
}

/* Pretty route: /activity/<slug> -> activity.php?slug=<slug> */
if (preg_match('#^/activity/([a-z0-9\-]+)/?$#i', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/activity.php';
    return true;
}

/* Direct .php request or directory -> index.php */
if (is_file($file) && str_ends_with($uri, '.php')) {
    require $file;
    return true;
}

if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    return true;
}

/* Directory index: /admin/ -> /admin/index.php */
$dirIndex = __DIR__ . rtrim($uri, '/') . '/index.php';
if (is_file($dirIndex)) {
    require $dirIndex;
    return true;
}

/* Try appending .php (clean URLs like /about) */
$asPhp = __DIR__ . rtrim($uri, '/') . '.php';
if (is_file($asPhp)) {
    require $asPhp;
    return true;
}

/* 404 */
http_response_code(404);
require __DIR__ . '/404.php';
return true;
