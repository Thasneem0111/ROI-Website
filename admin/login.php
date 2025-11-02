<?php
session_start();
// Simple PHP login for admin panel. Replace with DB check in future.
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    // TODO: Replace these with secure DB-driven auth
    $ADMIN_USER = 'admin';
    $ADMIN_PASS = 'admin123';
    if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        // redirect to dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="../styles.css">
  <style>
    .login-body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f5f7fb }
    .login-container { background:#fff; padding:28px; border-radius:10px; box-shadow:0 4px 24px rgba(0,0,0,0.06); width:360px }
    .login-container h2 { margin-bottom:18px }
    .login-container input { width:100%; padding:10px 12px; margin-bottom:12px; border-radius:6px; border:1px solid #e3e6ea }
    .login-container button { width:100%; padding:10px; background:#2ca6a4; border:none; color:#fff; border-radius:6px }
    .login-error { color:#d9534f; margin-top:8px; text-align:center }
  </style>
</head>
<body class="login-body">
  <div class="login-container">
    <h2>Admin Login</h2>
    <form method="post" action="login.php">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <?php if ($error): ?>
      <p class="login-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
  </div>
</body>
</html>
