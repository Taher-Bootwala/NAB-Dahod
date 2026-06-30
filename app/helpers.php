<?php
/**
 * helpers.php — small utility functions used across the app.
 */

declare(strict_types=1);

/** Escape for HTML output. */
function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Escape for use inside HTML attributes (alias of e for clarity). */
function attr(?string $s): string
{
    return e($s);
}

/** Build an asset URL with a cache-busting version stamp. */
function asset(string $path): string
{
    $path = '/' . ltrim($path, '/');
    $file = PUBLIC_DIR . $path;
    $v = is_file($file) ? (string) filemtime($file) : '1';
    return $path . '?v=' . $v;
}

/** Site-absolute URL. */
function url(string $path = ''): string
{
    return SITE_URL . '/' . ltrim($path, '/');
}

/** Send a JSON response and exit. */
function json_out($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Read JSON or form body into an array. */
function request_body(): array
{
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($ct, 'application/json')) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    return $_POST;
}

/** Redirect helper. */
function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

/* -------------------- CSRF -------------------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}

/**
 * Verify the CSRF token on a POST request, or stop the request with HTTP 419.
 * No-op for GET/HEAD so it is safe to call unconditionally at the top of a page.
 */
function require_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    if (!csrf_check($_POST['csrf'] ?? null)) {
        http_response_code(419);
        exit('Your session expired or the request token was invalid. Please go back, refresh the page, and try again.');
    }
}

/* -------------------- Rate limiting (file-based) -------------------- */
/**
 * Returns true if the action is allowed, false if rate limit exceeded.
 * Window-based limiter keyed by action + client IP.
 */
function rate_limit(string $action, int $max = 5, int $windowSec = 600): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $dir = sys_get_temp_dir() . '/bsd_rate';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    $file = $dir . '/' . md5($action . '|' . $ip) . '.json';
    $now = time();
    $hits = [];
    if (is_file($file)) {
        $hits = json_decode((string) file_get_contents($file), true) ?: [];
    }
    $hits = array_values(array_filter($hits, fn($t) => $t > $now - $windowSec));
    if (count($hits) >= $max) {
        return false;
    }
    $hits[] = $now;
    @file_put_contents($file, json_encode($hits), LOCK_EX);
    return true;
}

/* -------------------- Formatting -------------------- */
function inr(float $amount): string
{
    // Indian number formatting (lakh/crore grouping).
    $num = number_format($amount, 0, '.', '');
    $neg = str_starts_with($num, '-');
    $num = ltrim($num, '-');
    if (strlen($num) <= 3) {
        $out = $num;
    } else {
        $last3 = substr($num, -3);
        $rest = substr($num, 0, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        $out = $rest . ',' . $last3;
    }
    return ($neg ? '-' : '') . '₹' . $out;
}

function inr_short(float $amount): string
{
    if ($amount >= 10000000) {
        return '₹' . rtrim(rtrim(number_format($amount / 10000000, 2, '.', ''), '0'), '.') . 'Cr';
    }
    if ($amount >= 100000) {
        return '₹' . rtrim(rtrim(number_format($amount / 100000, 2, '.', ''), '0'), '.') . 'L';
    }
    if ($amount >= 1000) {
        return '₹' . rtrim(rtrim(number_format($amount / 1000, 1, '.', ''), '0'), '.') . 'K';
    }
    return '₹' . number_format($amount, 0);
}

function fmt_date(?string $iso, string $format = 'j M Y'): string
{
    if (!$iso) {
        return '';
    }
    try {
        return (new DateTime($iso))->format($format);
    } catch (Throwable) {
        return $iso;
    }
}

function excerpt(string $text, int $len = 140): string
{
    $text = trim(strip_tags($text));
    if (mb_strlen($text) <= $len) {
        return $text;
    }
    return mb_substr($text, 0, $len) . '…';
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/** Generate a human-friendly receipt / transaction id. */
function gen_id(string $prefix): string
{
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('y');
}

/**
 * Store an uploaded image and return a URL usable directly in <img src>.
 *
 * When Supabase is configured the bytes are uploaded to the given Storage
 * bucket and its public URL is returned. Otherwise (offline/demo) the file
 * is saved under public/uploads/<bucket>/ and a relative URL is returned.
 *
 * @param string $tmpPath  $_FILES[..]['tmp_name']
 * @param int    $errCode  $_FILES[..]['error']
 * @param string $bucket   target bucket name (also the local sub-folder)
 * @param string $prefix   filename prefix, e.g. "act", "gallery", "trustee"
 * @return string|null     image URL, or null on validation/upload failure
 */
function store_image(string $tmpPath, int $errCode, string $bucket, string $prefix): ?string
{
    global $supabase;

    if ($errCode !== UPLOAD_ERR_OK || $tmpPath === '' || !is_uploaded_file($tmpPath)) {
        return null;
    }
    // Reject oversized files before reading them into memory.
    $size = filesize($tmpPath);
    if ($size === false || $size > MAX_UPLOAD_BYTES) {
        return null;
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = mime_content_type($tmpPath);
    if (!isset($allowed[$mime])) {
        return null;
    }
    // Confirm the bytes are actually a decodable image, not just an image MIME.
    if (@getimagesize($tmpPath) === false) {
        return null;
    }
    $name = $prefix . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];

    // Preferred path: Supabase Storage bucket.
    if (defined('SUPABASE_ENABLED') && SUPABASE_ENABLED && isset($supabase)) {
        $bytes = file_get_contents($tmpPath);
        if ($bytes !== false) {
            $res = $supabase->uploadBinary($bucket, $name, $bytes, $mime);
            if (!empty($res['ok'])) {
                return $res['public_url'];
            }
        }
        // fall through to local storage if the upload failed
    }

    // Offline fallback: public/uploads/<bucket>/
    $dir = PUBLIC_DIR . '/uploads/' . $bucket;
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return move_uploaded_file($tmpPath, $dir . '/' . $name)
        ? '/uploads/' . $bucket . '/' . $name
        : null;
}

/**
 * Store an uploaded PDF and return a URL usable directly in <a href>.
 *
 * Mirrors store_image() but accepts only application/pdf. Uploads to the
 * given Supabase Storage bucket when configured, otherwise saves under
 * public/uploads/<bucket>/ for offline/demo use.
 *
 * @return string|null  PDF URL, or null on validation/upload failure
 */
function store_pdf(string $tmpPath, int $errCode, string $bucket, string $prefix): ?string
{
    global $supabase;

    if ($errCode !== UPLOAD_ERR_OK || $tmpPath === '' || !is_uploaded_file($tmpPath)) {
        return null;
    }
    $size = filesize($tmpPath);
    if ($size === false || $size > MAX_PDF_BYTES) {
        return null;
    }
    if (mime_content_type($tmpPath) !== 'application/pdf') {
        return null;
    }
    $name = $prefix . '_' . bin2hex(random_bytes(6)) . '.pdf';

    // Preferred path: Supabase Storage bucket.
    if (defined('SUPABASE_ENABLED') && SUPABASE_ENABLED && isset($supabase)) {
        $bytes = file_get_contents($tmpPath);
        if ($bytes !== false) {
            $res = $supabase->uploadBinary($bucket, $name, $bytes, 'application/pdf');
            if (!empty($res['ok'])) {
                return $res['public_url'];
            }
        }
        // fall through to local storage if the upload failed
    }

    // Offline fallback: public/uploads/<bucket>/
    $dir = PUBLIC_DIR . '/uploads/' . $bucket;
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return move_uploaded_file($tmpPath, $dir . '/' . $name)
        ? '/uploads/' . $bucket . '/' . $name
        : null;
}

/**
 * Public URL for the fallback trustee avatar, used when no photo is uploaded.
 *
 * When Supabase is configured the bundled user_default.png is uploaded
 * (idempotently, via upsert) to the trustees bucket and its public URL is
 * returned, so trustee records always point at a real stored object.
 * Falls back to the local asset path offline or if the upload fails.
 */
function default_trustee_photo(): string
{
    global $supabase;
    $local = '/assets/img/user_default.png';

    if (defined('SUPABASE_ENABLED') && SUPABASE_ENABLED && isset($supabase)) {
        $file = PUBLIC_DIR . $local;
        if (is_file($file)) {
            $bytes = file_get_contents($file);
            if ($bytes !== false) {
                $res = $supabase->uploadBinary(BUCKET_TRUSTEES, 'user_default.png', $bytes, 'image/png');
                if (!empty($res['ok'])) {
                    return $res['public_url'];
                }
            }
        }
    }
    return $local;
}

/** Human-readable byte size, e.g. 1536 -> "1.5 KB". */
function format_bytes(float $bytes, int $decimals = 1): string
{
    if ($bytes <= 0) {
        return '0 B';
    }
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = (int) floor(log($bytes, 1024));
    $i = max(0, min($i, count($units) - 1));
    return round($bytes / pow(1024, $i), $decimals) . ' ' . $units[$i];
}

/**
 * Scannable QR image URL. Uses admin-uploaded QR if set, else falls back
 * to the static file shipped with the site.
 */
function upi_qr_url(?float $amount = null, int $size = 320): string
{
    global $repo;
    if (isset($repo)) {
        $custom = $repo->setting('donation_qr', '');
        if ($custom !== '') return $custom . '?v=' . filemtime(PUBLIC_DIR . ltrim($custom, '/'));
    }
    return asset('assets/img/payment_qr.png');
}

/** Build a UPI deep-link string, using saved UPI ID from settings if available. */
function upi_link(?float $amount = null): string
{
    global $repo;
    $upiId  = isset($repo) ? $repo->setting('upi_id', UPI_ID) : UPI_ID;
    $payee  = isset($repo) ? $repo->setting('upi_payee_name', UPI_PAYEE_NAME) : UPI_PAYEE_NAME;
    $params = [
        'pa' => $upiId,
        'pn' => $payee,
        'cu' => 'INR',
        'tn' => 'Donation to ' . SITE_NAME,
    ];
    if ($amount && $amount > 0) {
        $params['am'] = number_format($amount, 2, '.', '');
    }
    return 'upi://pay?' . http_build_query($params);
}
