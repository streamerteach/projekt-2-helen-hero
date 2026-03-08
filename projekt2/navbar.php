<?php require_once __DIR__ . '/config.php'; ?>
<nav class="navbar">
  <div class="nav-brand"><a href="index.php">💕 LoveMatch</a></div>
  <div class="nav-links">
    <a href="index.php">Annonser</a>
    <?php if (loggedIn()): ?>
      <?php
        $safeU  = preg_replace('/[^a-zA-Z0-9]/', '', $_SESSION['username']);
        $picAbs = UPLOAD_DIR . $safeU . '_current.jpg';
        $picWeb = UPLOAD_WEB . $safeU . '_current.jpg';
      ?>
      <a href="profile.php">
        <?php if (file_exists($picAbs)): ?>
          <img src="<?= htmlspecialchars($picWeb) ?>" class="nav-avatar" alt="profil">
        <?php else: ?>👤<?php endif; ?>
        <?= htmlspecialchars($_SESSION['username']) ?>
      </a>
      <?php if (isManager()): ?>
        <a href="admin.php" style="color:#e91e8c;font-weight:700">🛠️ Admin</a>
      <?php endif; ?>
      <a href="logout.php" class="btn btn-small">Logga ut</a>
    <?php else: ?>
      <a href="login.php"   class="btn btn-small btn-primary">Logga in</a>
      <a href="register.php" class="btn btn-small">Registrera</a>
    <?php endif; ?>
  </div>
</nav>
