<?php
require_once __DIR__ . '/config.php';
if (loggedIn()) redirect('index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Fyll i alla fält.';
    } else {
        $st = db()->prepare("SELECT id, username, password, role, email FROM users WHERE username = ?");
        $st->execute([$username]);
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['email']    = $user['email'];
            $ck = 'last_' . preg_replace('/[^a-zA-Z0-9]/', '', $username);
            $_SESSION['last_login'] = $_COOKIE[$ck] ?? 'Aldrig';
            setcookie($ck, date('Y-m-d H:i'), time()+86400*365, '/');
            redirect('index.php');
        } else {
            $error = 'Felaktigt användarnamn eller lösenord.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Logga in – LoveMatch</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="auth-container">
  <div class="auth-card">
    <h1>💕 Logga in</h1>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" class="auth-form">
      <div class="form-group">
        <label>Användarnamn</label>
        <input type="text" name="username" required autocomplete="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Lösenord</label>
        <input type="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary btn-full">Logga in</button>
    </form>
    <p style="margin-top:.75rem;font-size:.85rem;color:#888;text-align:center">
      Testa: <strong>anna_hki</strong> / <strong>password</strong>
    </p>
    <p class="auth-link">Inget konto? <a href="register.php">Registrera dig</a></p>
  </div>
</div>
<footer class="footer"><p>&copy; <?= date('Y') ?> LoveMatch 💕</p></footer>
</body>
</html>
