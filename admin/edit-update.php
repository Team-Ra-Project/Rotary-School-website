<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/upload.php';
require __DIR__ . '/../config/database.php';

require_admin_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

$db   = get_db();
$stmt = $db->prepare('SELECT * FROM recent_updates WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$update = $stmt->fetch();

if (!$update) {
    flash_set('error', 'That update could not be found.');
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require_valid();

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $updateDate  = trim($_POST['update_date'] ?? '');
    $status      = $_POST['status'] ?? 'published';
    $removeImage = !empty($_POST['remove_image']);

    $errors = validate_update_input($title, $description, $updateDate, $status);

    $newImagePath = $update['image'];

    if (!$errors) {
        try {
            $uploaded = handle_news_image_upload('image');
            if ($uploaded !== null) {
                delete_news_image($update['image']);
                $newImagePath = $uploaded;
            } elseif ($removeImage) {
                delete_news_image($update['image']);
                $newImagePath = null;
            }
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (!$errors) {
        $stmt = $db->prepare(
            'UPDATE recent_updates SET title = ?, description = ?, image = ?, update_date = ?, status = ? WHERE id = ?'
        );
        $stmt->execute([$title, $description, $newImagePath, $updateDate, $status, $id]);

        flash_set('success', 'Update saved.');
        header('Location: dashboard.php');
        exit;
    }

    // Re-populate the form with the submitted (invalid) values for correction.
    $update = array_merge($update, [
        'title'       => $title,
        'description' => $description,
        'update_date' => $updateDate,
        'status'      => $status,
        'image'       => $newImagePath,
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Update | Rotary School Uran Admin</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="../assets/images/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <img src="../assets/images/logo.jpg" alt="Rotary School Uran">
      <div><strong>Rotary School Uran</strong><span>Admin Panel</span></div>
    </div>
    <nav class="admin-nav">
      <a href="dashboard.php" class="active"><span class="ic">&#128240;</span> Recent Updates</a>
    </nav>
    <div class="admin-sidebar-foot">
      <a href="../index.html"><span class="ic">&#8617;</span> Back to website</a>
      <a href="logout.php"><span class="ic">&#9099;</span> Log out</a>
    </div>
  </aside>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Edit update</h1>
        <p>Editing &ldquo;<?= h($update['title']) ?>&rdquo;.</p>
      </div>
      <a href="dashboard.php" class="btn btn-ghost btn-sm">&larr; Back to Recent Updates</a>
    </div>

    <?php if ($errors): ?>
      <div class="flash error" style="padding:14px 18px;border-radius:12px;font-weight:600;font-size:13.5px;margin-bottom:20px;background:#fbe4e1;color:#c0392b">
        <?= h(implode(' ', $errors)) ?>
      </div>
    <?php endif; ?>

    <section class="form-card">
      <form method="post" action="edit-update.php?id=<?= (int) $update['id'] ?>" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        <input type="hidden" name="id" value="<?= (int) $update['id'] ?>">
        <div class="form-row full">
          <div class="field">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required maxlength="255" value="<?= h($update['title']) ?>">
          </div>
        </div>
        <div class="form-row full">
          <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" required maxlength="5000"><?= h($update['description']) ?></textarea>
          </div>
        </div>
        <div class="form-row">
          <div class="field">
            <label for="update_date">Date</label>
            <input type="date" id="update_date" name="update_date" required value="<?= h($update['update_date']) ?>">
          </div>
          <div class="field">
            <label for="status">Visibility</label>
            <select id="status" name="status">
              <option value="published" <?= $update['status'] === 'published' ? 'selected' : '' ?>>Published (visible on the site)</option>
              <option value="hidden" <?= $update['status'] === 'hidden' ? 'selected' : '' ?>>Hidden (draft)</option>
            </select>
          </div>
        </div>
        <div class="form-row full">
          <div class="field">
            <label for="image">Featured image (JPG, JPEG, PNG, or WebP)</label>
            <?php if ($update['image']): ?>
              <div class="update-thumb" style="width:96px;height:96px;margin-bottom:8px">
                <img src="../<?= h($update['image']) ?>" alt="">
              </div>
              <label style="font-weight:500;font-size:13px;display:flex;align-items:center;gap:6px">
                <input type="checkbox" name="remove_image" value="1" style="width:auto"> Remove current image
              </label>
            <?php endif; ?>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
          </div>
        </div>
        <button class="btn btn-primary" type="submit">Save changes</button>
      </form>
    </section>
  </main>
</div>

</body>
</html>
