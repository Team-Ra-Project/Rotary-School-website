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

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $db   = get_db();
    $stmt = $db->prepare('SELECT image FROM recent_updates WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row) {
        $del = $db->prepare('DELETE FROM recent_updates WHERE id = ?');
        $del->execute([$id]);
        delete_news_image($row['image']);
        flash_set('success', 'Update deleted.');
    } else {
        flash_set('error', 'That update could not be found.');
    }
}

header('Location: dashboard.php');
exit;
