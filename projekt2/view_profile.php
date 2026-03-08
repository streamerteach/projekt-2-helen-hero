<?php
require_once __DIR__ . '/config.php';

$tid = (int)($_GET['id'] ?? 0);
if (!$tid) redirect('index.php');

$st = db()->prepare("SELECT * FROM users WHERE id=? AND role='user'");
$st->execute([$tid]);
$target = $st->fetch();
if (!$target) redirect('index.php');

// Like counts
$lkSt = db()->prepare("SELECT COALESCE(SUM(CASE WHEN value=1 THEN 1 ELSE 0 END),0) AS lk, COALESCE(SUM(CASE WHEN value=-1 THEN 1 ELSE 0 END),0) AS dk FROM likes WHERE target_id=?");
$lkSt->execute([$tid]);
$lk = $lkSt->fetch();

$myVote = 0;
if (loggedIn()) {
    $vst = db()->prepare("SELECT value FROM likes WHERE voter_id=? AND target_id=?");
    $vst->execute([$_SESSION['user_id'], $tid]);
    $row = $vst->fetch();
    if ($row) $myVote = (int)$row['value'];
}

// Post comment
$cmErr = ''; $cmOk = '';
if (loggedIn() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    $txt = trim($_POST['comment_text'] ?? '');
    if (strlen($txt) < 2)    $cmErr = 'För kort!';
    elseif (strlen($txt)>1000) $cmErr = 'Max 1000 tecken.';
    else {
        db()->prepare("INSERT INTO comments (user_id,target_user_id,comment_text) VALUES (?,?,?)")
           ->execute([$_SESSION['user_id'], $tid, $txt]);
        $cmOk = 'Kommentar skickad!';
    }
}

// Fetch comments
$cmSt = db()->prepare("SELECT c.*,u.username AS cu,u.real_name AS cn FROM comments c JOIN users u ON c.user_id=u.id WHERE c.target_user_id=? AND c.is_deleted=0 ORDER BY c.created_at DESC");
$cmSt->execute([$tid]);
$comments = $cmSt->fetchAll();

$safeU  = preg_replace('/[^a-zA-Z0-9]/', '', $target['username']);
$picAbs = UPLOAD_DIR . $safeU . '_current.jpg';
$picWeb = UPLOAD_WEB . $safeU . '_current.jpg';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($target['real_name']) ?> – LoveMatch</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="profile-container">
  <div class="profile-header">
    <?php if (file_exists($picAbs)): ?>
      <img src="<?= htmlspecialchars($picWeb) ?>" class="profile-avatar-large" alt="profil">
    <?php else: ?><div class="profile-avatar-placeholder-large">💕</div><?php endif; ?>
    <div class="profile-info">
      <h1><?= htmlspecialchars($target['real_name']) ?></h1>
      <p>@<?= htmlspecialchars($target['username']) ?></p>
      <p>📍 <?= htmlspecialchars($target['city'] ?: 'Finland') ?></p>
      <p>❤️ Söker: <?= htmlspecialchars($target['preference']) ?></p>
      <?php if (loggedIn()): ?>
        <p>📧 <?= htmlspecialchars($target['email']) ?></p>
        <p>💰 <?= number_format((int)$target['annual_salary']) ?> €/år</p>
      <?php else: ?>
        <p class="ad-locked">🔒 Logga in för att se e-post och lön</p>
      <?php endif; ?>
      <!-- Like buttons -->
      <div class="like-section" style="margin-top:.75rem">
        <span class="like-count">👍 <?= $lk['lk'] ?></span>
        <span class="like-count">👎 <?= $lk['dk'] ?></span>
        <?php if (loggedIn() && (int)$_SESSION['user_id'] !== $tid): ?>
          <form method="POST" action="like.php" style="display:inline">
            <input type="hidden" name="target_id" value="<?= $tid ?>">
            <input type="hidden" name="value" value="1">
            <input type="hidden" name="back" value="view_profile.php?id=<?= $tid ?>">
            <button class="like-btn <?= $myVote===1?'active':'' ?>">👍 Gilla</button>
          </form>
          <form method="POST" action="like.php" style="display:inline">
            <input type="hidden" name="target_id" value="<?= $tid ?>">
            <input type="hidden" name="value" value="-1">
            <input type="hidden" name="back" value="view_profile.php?id=<?= $tid ?>">
            <button class="like-btn <?= $myVote===-1?'active-dislike':'' ?>">👎 Ogilla</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- About -->
  <div class="profile-section" style="margin-bottom:1.5rem;background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,.1)">
    <h2>Om <?= htmlspecialchars($target['real_name']) ?></h2>
    <p><?= nl2br(htmlspecialchars($target['about_me'] ?: 'Ingen beskrivning.')) ?></p>
  </div>

  <?php if (isManager()): ?>
  <div class="profile-section" style="background:#fff3e0;border-radius:12px;padding:1.5rem;margin-bottom:1.5rem">
    <h2>🛠️ Admin</h2>
    <a href="admin.php?edit=<?= $tid ?>"   class="btn btn-secondary">Redigera</a>
    <a href="admin.php?del_user=<?= $tid ?>" class="btn btn-danger"
       onclick="return confirm('Radera?')">Radera</a>
  </div>
  <?php endif; ?>

  <!-- Comment form -->
  <div id="commentform" class="profile-section" style="background:#fff;border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,.1)">
    <h2>💬 Lämna kommentar</h2>
    <?php if (!loggedIn()): ?>
      <p><a href="login.php">Logga in</a> för att kommentera.</p>
    <?php elseif ((int)$_SESSION['user_id'] === $tid): ?>
      <p>Du kan inte kommentera din egen profil.</p>
    <?php else: ?>
      <?php if ($cmErr): ?><div class="alert alert-error"><?= htmlspecialchars($cmErr) ?></div><?php endif; ?>
      <?php if ($cmOk):  ?><div class="alert alert-success"><?= htmlspecialchars($cmOk) ?></div><?php endif; ?>
      <form method="POST">
        <div class="form-group">
          <textarea name="comment_text" rows="3" maxlength="1000" required placeholder="Skriv din kommentar..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Skicka</button>
      </form>
    <?php endif; ?>
  </div>

  <!-- Comments -->
  <div style="background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,.1)">
    <h2>💬 Kommentarer (<?= count($comments) ?>)</h2>
    <?php if (!$comments): ?>
      <p>Inga kommentarer ännu.</p>
    <?php else: ?>
      <?php foreach ($comments as $c): ?>
        <div class="comment-card">
          <div class="comment-header">
            <span class="comment-user">👤 <?= htmlspecialchars($c['cn']) ?></span>
            <div style="display:flex;gap:.5rem;align-items:center">
              <span class="comment-time"><?= htmlspecialchars($c['created_at']) ?></span>
              <?php if (isManager()): ?>
                <a href="admin.php?del_comment=<?= $c['id'] ?>&back=view_profile.php?id=<?= $tid ?>"
                   class="btn btn-small btn-danger" onclick="return confirm('Ta bort?')">🗑️</a>
              <?php endif; ?>
            </div>
          </div>
          <p class="comment-text"><?= nl2br(htmlspecialchars($c['comment_text'])) ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<footer class="footer"><p>&copy; <?= date('Y') ?> LoveMatch 💕</p></footer>
</body>
</html>
