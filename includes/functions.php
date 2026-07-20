<?php
/**
 * Small shared helpers used by the admin CRUD pages.
 */

/** HTML-escapes a value for safe output in templates. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Stores a one-time flash message in the session (shown after the next redirect). */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Retrieves and clears the pending flash message, or null if there isn't one. */
function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Validates the "Recent Update" form fields.
 * Returns an array of error messages (empty array = valid).
 */
function validate_update_input(string $title, string $description, string $date, string $status): array
{
    $errors = [];

    if ($title === '' || mb_strlen($title) > 255) {
        $errors[] = 'Title is required and must be 255 characters or fewer.';
    }

    if ($description === '' || mb_strlen($description) > 5000) {
        $errors[] = 'Description is required and must be 5000 characters or fewer.';
    }

    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        $errors[] = 'Please provide a valid date.';
    }

    if (!in_array($status, ['published', 'hidden'], true)) {
        $errors[] = 'Invalid status value.';
    }

    return $errors;
}
