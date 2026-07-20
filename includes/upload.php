<?php
/**
 * Secure handling for the "Recent Update" featured image upload.
 */

const NEWS_UPLOAD_DIR      = __DIR__ . '/../uploads/news/';
const NEWS_UPLOAD_REL_PATH = 'uploads/news/';
const NEWS_UPLOAD_MAX_SIZE = 5 * 1024 * 1024; // 5 MB

const NEWS_UPLOAD_ALLOWED_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

/**
 * Validates and stores an uploaded image from $_FILES[$field].
 *
 * Returns:
 *   - a relative path string (e.g. "uploads/news/abc123.jpg") on success
 *   - null if no file was submitted for this field (not an error — image is optional)
 *   - throws RuntimeException with a user-facing message on validation failure
 */
function handle_news_image_upload(string $field): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed (error code ' . $file['error'] . '). Please try again.');
    }

    if ($file['size'] <= 0 || $file['size'] > NEWS_UPLOAD_MAX_SIZE) {
        throw new RuntimeException('Image must be between 1 byte and 5 MB.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid file upload.');
    }

    // Verify the real content type server-side — never trust the client-sent MIME type.
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset(NEWS_UPLOAD_ALLOWED_MIME[$realMime])) {
        throw new RuntimeException('Only JPG, JPEG, PNG, and WebP images are allowed.');
    }

    // Confirm it's actually a readable image (blocks disguised non-image files).
    if (@getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('The uploaded file is not a valid image.');
    }

    if (!is_dir(NEWS_UPLOAD_DIR)) {
        mkdir(NEWS_UPLOAD_DIR, 0755, true);
    }

    $extension = NEWS_UPLOAD_ALLOWED_MIME[$realMime];
    $filename  = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = NEWS_UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded image. Check folder permissions on uploads/news/.');
    }

    chmod($destination, 0644);

    return NEWS_UPLOAD_REL_PATH . $filename;
}

/** Deletes a previously stored news image (best-effort, ignores missing files). */
function delete_news_image(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }
    $full = __DIR__ . '/../' . $relativePath;
    if (is_file($full)) {
        @unlink($full);
    }
}
