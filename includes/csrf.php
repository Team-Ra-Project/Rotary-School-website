<?php
/**
 * Minimal CSRF protection for the admin forms.
 * Requires includes/auth.php to already have started the session.
 */

/** Returns the current CSRF token, generating one if it doesn't exist yet. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Echoes a hidden <input> field carrying the CSRF token, for use inside <form>. */
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/** Verifies a submitted token against the session token (constant-time compare). */
function csrf_verify(?string $submitted): bool
{
    if (empty($_SESSION['csrf_token']) || empty($submitted)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submitted);
}

/** Verifies $_POST['csrf_token'] and stops the request with a 403 if it's invalid. */
function csrf_require_valid(): void
{
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        die('Security check failed (invalid or expired form token). Please go back and try again.');
    }
}
