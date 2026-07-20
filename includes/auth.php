<?php
/**
 * Admin session handling.
 * Single-admin auth only — there is no visitor/user login on this site.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Harden the session cookie before starting the session.
    $params = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** True if an admin is currently logged in. */
function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

/** Call at the top of every protected admin page. Redirects to login if needed. */
function require_admin_login(): void
{
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/** Logs the given admin row into the session, regenerating the session id. */
function log_admin_in(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
}

/** Clears the admin session (used by logout). */
function log_admin_out(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
