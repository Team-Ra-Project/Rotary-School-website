<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/functions.php';
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
    $stmt = $db->prepare('SELECT status FROM recent_updates WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row) {
        $newStatus = $row['status'] === 'published' ? 'hidden' : 'published';
        $upd = $db->prepare('UPDATE recent_updates SET status = ? WHERE id = ?');
        $upd->execute([$newStatus, $id]);
        flash_set('success', 'Visibility updated.');
    } else {
        flash_set('error', 'That update could not be found.');
    }
}

header('Location: dashboard.php');
exit;
