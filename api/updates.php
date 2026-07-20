<?php
/**
 * Public read-only endpoint used by assets/js/public-updates.js on the
 * Home and News pages.
 *
 *   GET /api/updates.php?limit=8
 *
 * Returns a JSON array of published updates, newest first:
 *   [{ id, type, title, description, thumb, timestamp, link }, ...]
 *
 * No authentication required — only "published" rows are ever returned.
 */

require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 8;
if ($limit < 1) {
    $limit = 8;
}
if ($limit > 50) {
    $limit = 50;
}

try {
    $stmt = get_db()->prepare(
        'SELECT id, title, description, image, update_date
         FROM recent_updates
         WHERE status = "published"
         ORDER BY update_date DESC, created_at DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not load updates.']);
    exit;
}

$out = array_map(function (array $row): array {
    return [
        'id'          => (int) $row['id'],
        'type'        => 'news',
        'title'       => $row['title'],
        'description' => $row['description'],
        'thumb'       => $row['image'] ?: null,
        'timestamp'   => (new DateTime($row['update_date']))->format(DateTime::ATOM),
        'link'        => 'news.html',
    ];
}, $rows);

echo json_encode($out, JSON_UNESCAPED_SLASHES);
