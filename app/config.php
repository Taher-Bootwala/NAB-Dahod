<?php
/**
 * config.php — loads environment + defines app constants.
 * Lives OUTSIDE the web root (public/), so secrets are never served.
 */

declare(strict_types=1);

/* ---- Minimal .env loader (no Composer dependency) ---- */
function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // strip surrounding quotes
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'")) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, $default = null)
{
    $v = getenv($key);
    if ($v === false || $v === '') {
        return $default;
    }
    return $v;
}

$root = dirname(__DIR__);
load_env($root . '/.env');

/* ---- Paths ---- */
define('APP_ROOT', $root);
define('APP_DIR', __DIR__);
define('PUBLIC_DIR', $root . '/public');

/* ---- Site ---- */
define('SITE_NAME', env('SITE_NAME', 'National Association for the Blind'));
define('SITE_URL', rtrim((string) env('SITE_URL', 'http://localhost:8000'), '/'));
define('SITE_ENV', env('SITE_ENV', 'development'));
define('SITE_TAGLINE', 'Empowering Visually Impaired Students to Build Their Future');

/* ---- Org ---- */
define('ORG_EMAIL', env('ORG_EMAIL', 'info@blindschooldahod.org'));
define('ORG_PHONE', env('ORG_PHONE', '+91 99999 99999'));
define('ORG_WHATSAPP', env('ORG_WHATSAPP', '919999999999'));
define('ORG_ADDRESS', env('ORG_ADDRESS', 'Shrimati M B Jain Blind School, Dahod, Gujarat 389151, India'));

/* ---- Donation ---- */
define('UPI_ID', env('UPI_ID', 'merchant331865.augp@aubank'));
define('UPI_PAYEE_NAME', env('UPI_PAYEE_NAME', 'National Association for the Blind Dahod'));
define('DONATION_GOAL', (int) env('DONATION_GOAL', 2500000));

/* ---- Supabase ---- */
define('SUPABASE_URL', rtrim((string) env('SUPABASE_URL', ''), '/'));
define('SUPABASE_ANON_KEY', env('SUPABASE_ANON_KEY', ''));
define('SUPABASE_SERVICE_KEY', env('SUPABASE_SERVICE_KEY', ''));
define('SUPABASE_BUCKET', env('SUPABASE_BUCKET', 'media'));
define('SUPABASE_ENABLED', SUPABASE_URL !== '' && SUPABASE_SERVICE_KEY !== '');
// Total Storage quota for this Supabase project (bytes). Free tier is 1 GB.
// Used by the admin Storage page to show how much space remains.
define('SUPABASE_STORAGE_LIMIT', (int) env('SUPABASE_STORAGE_LIMIT', 1024 * 1024 * 1024));

/* ---- Storage buckets (one per image category) ---- */
define('BUCKET_ACTIVITIES', env('BUCKET_ACTIVITIES', 'activities'));
define('BUCKET_GALLERY',    env('BUCKET_GALLERY',    'gallery'));
define('BUCKET_TRUSTEES',   env('BUCKET_TRUSTEES',   'trustees'));
define('BUCKET_HOME',       env('BUCKET_HOME',       'home'));
define('BUCKET_PROGRESS',   env('BUCKET_PROGRESS',   'progress'));

/* ---- Admin ---- */
define('ADMIN_EMAIL', env('ADMIN_EMAIL', 'admin@blindschooldahod.org'));

/* ---- Security ---- */
define('APP_SECRET', env('APP_SECRET', 'dev-insecure-secret-change-me'));

/* ---- Uploads ---- */
// Maximum accepted image upload size (bytes). Default 5 MB.
define('MAX_UPLOAD_BYTES', (int) env('MAX_UPLOAD_BYTES', 5 * 1024 * 1024));
// Maximum accepted PDF upload size (bytes). Default 20 MB (progress reports).
define('MAX_PDF_BYTES', (int) env('MAX_PDF_BYTES', 20 * 1024 * 1024));

/* ---- Errors ---- */
if (SITE_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}
