<?php
require __DIR__ . '/../includes/auth.php';

log_admin_out();
header('Location: login.php');
exit;
