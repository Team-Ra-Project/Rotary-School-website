<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../config/database.php';

require_admin_login();

$stmt    = get_db()->query('SELECT * FROM recent_updates ORDER BY update_date DESC, created_at DESC');
$updates = $stmt->fetchAll();

$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recent Updates | Rotary School Uran Admin</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="../assets/images/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
<style>
  .flash{padding:14px 18px;border-radius:12px;font-weight:600;font-size:13.5px;margin-bottom:20px;}
  .flash.success{background:#dcf5ee;color:var(--teal-700);}
  .flash.error{background:#fbe4e1;color:#c0392b;}
  .admin-updates-list-item-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
  .admin-updates-list-item-actions form{display:inline;margin:0;}
  .btn-xs{padding:7px 13px;font-size:12.5px;border-radius:8px;}
</style>
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
        <h1>Recent Updates</h1>
        <p>Add, edit, publish, or hide the News / Recent Updates shown on the Home and News pages.</p>
      </div>
      <div class="admin-profile">
        <div><b><?= h($_SESSION['admin_username']) ?></b><span>Administrator</span></div>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="flash <?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>

    <section class="form-card" style="margin-bottom:28px">
      <h2 style="margin-top:0;font-size:1.1rem">Add a new update</h2>
      <form method="post" action="add-update.php" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        <div class="form-row full">
          <div class="field">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required maxlength="255" placeholder="e.g. SSC board result 2026 announced">
          </div>
        </div>
        <div class="form-row full">
          <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" required maxlength="5000" placeholder="Short description shown on the Home and News pages"></textarea>
          </div>
        </div>
        <div class="form-row">
          <div class="field">
            <label for="update_date">Date</label>
            <input type="date" id="update_date" name="update_date" required value="<?= date('Y-m-d') ?>">
          </div>
          <div class="field">
            <label for="status">Visibility</label>
            <select id="status" name="status">
              <option value="published">Published (visible on the site)</option>
              <option value="hidden">Hidden (draft)</option>
            </select>
          </div>
        </div>
        <div class="form-row full">
          <div class="field">
            <label for="image">Featured image (JPG, JPEG, PNG, or WebP — optional)</label>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
          </div>
        </div>
        <button class="btn btn-primary" type="submit">Add update</button>
      </form>
    </section>

    <section class="updates-card">
      <div class="updates-card-head">
        <div>
          <h2><span class="live-dot"></span> All updates</h2>
          <div class="sub"><?= count($updates) ?> total &mdash; newest first</div>
        </div>
      </div>

      <div class="update-list">
        <?php foreach ($updates as $u): ?>
          <div class="update-item">
            <div class="update-ic type-news">&#128240;</div>
            <div class="update-body">
              <div class="update-body-top"><span class="update-type-label">News</span></div>
              <div class="update-title"><?= h($u['title']) ?></div>
              <p class="update-desc"><?= h($u['description']) ?></p>
              <div class="update-meta">
                <span>&#128337; <?= h((new DateTime($u['update_date']))->format('d M Y')) ?></span>
              </div>
              <div class="admin-updates-list-item-actions" style="margin-top:10px">
                <a class="btn btn-ghost btn-xs" href="edit-update.php?id=<?= (int) $u['id'] ?>">Edit</a>

                <form method="post" action="toggle-status.php">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                  <button class="btn btn-ghost btn-xs" type="submit">
                    <?= $u['status'] === 'published' ? 'Hide' : 'Publish' ?>
                  </button>
                </form>

                <form method="post" action="delete-update.php" onsubmit="return confirm('Delete this update? This cannot be undone.');">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                  <button class="btn btn-ghost btn-xs" type="submit" style="color:#c0392b">Delete</button>
                </form>
              </div>
            </div>
            <div class="update-right">
              <span class="status-badge <?= $u['status'] === 'published' ? 'published' : 'draft' ?>"><?= h($u['status']) ?></span>
              <?php if ($u['image']): ?>
                <div class="update-thumb"><img src="../<?= h($u['image']) ?>" alt=""></div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (empty($updates)): ?>
        <div class="updates-empty">
          <div class="ic">&#128203;</div>
          <p>No updates yet. Add your first one above.</p>
        </div>
      <?php endif; ?>
    </section>
  </main>
</div>

</body>
</html>
