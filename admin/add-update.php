<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/upload.php';
require __DIR__ . '/../config/database.php';

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

csrf_require_valid();

$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$updateDate  = trim($_POST['update_date'] ?? '');
$status      = $_POST['status'] ?? 'published';

$errors = validate_update_input($title, $description, $updateDate, $status);

if ($errors) {
    flash_set('error', implode(' ', $errors));
    header('Location: dashboard.php');
    exit;
}

try {
    $imagePath = handle_news_image_upload('image');
} catch (RuntimeException $e) {
    flash_set('error', $e->getMessage());
    header('Location: dashboard.php');
    exit;
}

$stmt = get_db()->prepare(
    'INSERT INTO recent_updates (title, description, image, update_date, status) VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$title, $description, $imagePath, $updateDate, $status]);

flash_set('success', 'Update added.');
header('Location: dashboard.php');
exit;
