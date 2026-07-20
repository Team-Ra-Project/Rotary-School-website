<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../config/database.php';

if (is_admin_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require_valid();

    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = get_db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            log_admin_in($admin);
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Incorrect username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Rotary School Uran</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="../assets/images/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container nav-inner">
    <a href="../index.html" class="brand">
      <img src="../assets/images/logo.jpg" alt="Rotary School Uran logo">
      <span class="brand-text"><strong>Rotary School Uran</strong><span>Learn &middot; Serve &middot; Succeed</span></span>
    </a>
  </div>
</header>

<main>
<section class="page-hero">
  <div class="container">
    <div>
      <div class="eyebrow">Admin</div>
      <h1>Admin login.</h1>
      <p>Manage the Recent Updates section for the Home and News pages.</p>
      <div class="breadcrumb"><a href="../index.html">Home</a> / <span>Admin Login</span></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="auth-wrap">
      <div class="form-card">
        <h2 style="margin-top:0;font-size:1.2rem">Admin sign in</h2>
        <?php if ($error): ?>
          <p class="form-note" style="display:block;margin:0 0 16px;color:#c0392b;font-weight:600"><?= h($error) ?></p>
        <?php endif; ?>
        <form method="post" action="login.php" novalidate>
          <?php csrf_field(); ?>
          <div class="form-row full">
            <div class="field">
              <label for="username">Username</label>
              <input type="text" id="username" name="username" required autofocus autocomplete="username" placeholder="admin">
            </div>
          </div>
          <div class="form-row full">
            <div class="field">
              <label for="password">Password</label>
              <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
            </div>
          </div>
          <button class="btn btn-primary btn-block" type="submit">Log in</button>
        </form>
      </div>
      <p class="text-center" style="margin-top:18px;color:var(--ink-500);font-size:13px">This area is restricted to the school&rsquo;s website administrator.</p>
    </div>
  </div>
</section>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <span>&copy; Rotary Education Society. All rights reserved.</span>
    </div>
  </div>
</footer>
</body>
</html>