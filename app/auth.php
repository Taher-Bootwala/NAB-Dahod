<?php
/**
 * auth.php — admin authentication.
 *
 * LIVE mode  : authenticates against Supabase Auth (GoTrue) and reads the
 *              user's role from the `users` table. JWT + refresh token are
 *              kept in the PHP session (server-side, not exposed to JS).
 * DEMO mode  : (no Supabase configured) allows a single bootstrap admin
 *              defined by ADMIN_EMAIL + ADMIN_DEMO_PASSWORD so the panel is
 *              explorable offline. CHANGE BEFORE PRODUCTION.
 */

declare(strict_types=1);

/** Demo credentials (offline mode only). */
function demo_admin_password(): string
{
    return (string) env('ADMIN_DEMO_PASSWORD', 'admin123');
}

function demo_admin_username(): string
{
    return (string) env('ADMIN_USERNAME', 'admin');
}

/** Attempt admin login. Returns [bool ok, string message]. */
function admin_login(Supabase $sb, string $username, string $password): array
{
    $username = trim(strtolower($username));

    // The local admin may sign in with either the configured username ("admin")
    // or the admin email — both map to the same bootstrap account.
    $localIdentifiers = [strtolower(demo_admin_username()), strtolower(ADMIN_EMAIL)];

    // Always check local admin credentials first (works with or without Supabase).
    if (in_array($username, $localIdentifiers, true) && hash_equals(demo_admin_password(), $password)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'email' => ADMIN_EMAIL,
            'role'  => 'super_admin',
            'name'  => 'Admin',
            'mode'  => 'local',
        ];
        return [true, 'Welcome back.'];
    }

    // If Supabase is configured, also try email-based login.
    if ($sb->enabled()) {
        $res = $sb->signInWithPassword($username, $password);
        if (!$res['ok'] || empty($res['data']['access_token'])) {
            $msg = $res['data']['error_description'] ?? $res['data']['msg'] ?? 'Invalid credentials.';
            return [false, $msg];
        }

        $jwt    = $res['data']['access_token'];
        $userId = $res['data']['user']['id'] ?? '';

        $rows = $sb->select('users', ['id' => 'eq.' . $userId, 'select' => 'role,email']);
        $role = $rows[0]['role'] ?? 'user';
        if (!in_array($role, ['admin', 'super_admin'], true)) {
            return [false, 'This account does not have admin access.'];
        }

        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id'      => $userId,
            'email'   => $username,
            'role'    => $role,
            'name'    => $res['data']['user']['user_metadata']['name'] ?? $username,
            'jwt'     => $jwt,
            'refresh' => $res['data']['refresh_token'] ?? '',
            'mode'    => 'live',
        ];
        return [true, 'Welcome back.'];
    }

    return [false, 'Invalid credentials.'];
}

function admin_user(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function is_admin(): bool
{
    return !empty($_SESSION['admin']);
}

function is_super_admin(): bool
{
    return ($_SESSION['admin']['role'] ?? '') === 'super_admin';
}

/** Guard: call at the top of every protected admin page. */
function require_admin(): void
{
    // Prevent browser from caching admin pages — back button will re-check auth.
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Expire session after 30 minutes of inactivity.
    if (!empty($_SESSION['admin'])) {
        if (isset($_SESSION['_last_active']) && (time() - $_SESSION['_last_active']) > 1800) {
            admin_logout();
        } else {
            $_SESSION['_last_active'] = time();
        }
    }

    if (!is_admin()) {
        redirect('/admin/login.php');
    }
}

function admin_logout(): void
{
    $_SESSION['admin'] = null;
    unset($_SESSION['admin']);
    session_regenerate_id(true);
}
