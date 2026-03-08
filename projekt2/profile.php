<?php
require_once __DIR__ . '/config.php';
if (!loggedIn()) redirect('login.php');

$uid  = (int)$_SESSION['user_id'];
$prefs= ['Man','Kvinna','Båda','Annat','Alla'];
$msg  = '';

// Load user
$user = db()->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]);
$user = $user->fetch();

// DELETE profile
if (isset($_POST['delete_profile'])) {
    if (password_verify($_POST['confirm_pass'] ?? '', $user['password'])) {
        db()->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        session_destroy();
        redirect('index.php');
    } else {
        $msg = '<div class="alert alert-error">Fel lösenord – profilen raderades inte.</div>';
    }
}

// UPDATE profile
if (isset($_POST['save'])) {
    $rn   = trim($_POST['real_name']     ?? '');
    $em   = trim($_POST['email']         ?? '');
    $city = trim($_POST['city']          ?? '');
    $ab   = trim($_POST['about_me']      ?? '');
    $sal  = (int)($_POST['annual_salary']?? 0);
    $pref = $_POST['preference']         ?? 'Alla';
    $np   = $_POST['new_password']       ?? '';

    if (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
        $msg = '<div class="alert alert-error">Ogiltig e-postadress.</div>';
    } elseif (!in_array($pref, $prefs)) {
        $msg = '<div class="alert alert-error">Ogiltig preferens.</div>';
    } else {
        if ($np && strlen($np) >= 8) {
            $hash = password_hash($np, PASSWORD_DEFAULT);
            db()->prepare("UPDATE users SET real_name=?,email=?,city=?,about_me=?,annual_salary=?,preference=?,password=? WHERE id=?")
               ->execute([$rn,$em,$city,$ab,$sal,$pref,$hash,$uid]);
        } else {
            db()->prepare("UPDATE users SET real_name=?,email=?,city=?,about_me=?,annual_salary=?,preference=? WHERE id=?")
               ->execute([$rn,$em,$city,$ab,$sal,$pref,$uid]);
        }
        $_SESSION['email'] = $em;
        $msg = '<div class="alert alert-success">Profil uppdaterad!</div>';
        $st2 = db()->prepare("SELECT * FROM users WHERE id=?"); $st2->execute([$uid]); $user = $st2->fetch();
    }
}

// Upload profile pic
if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
    $f    = $_FILES['pic'];
    $fi   = new finfo(FILEINFO_MIME_TYPE);
    $mime = $fi->file($f['tmp_name']);
    $ext  = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $ok   = ['image/jpeg'=>'jpg','image/png'=>'png'];
    if (!isset($ok[$mime]) || !in_array($ext,['jpg','jpeg','png'])) {
        $msg = '<div class="alert alert-error">Endast JPG/PNG tillåtna.</div>';
    } elseif ($f['size'] > 5*1024*1024) {
        $msg = '<div class="alert alert-error">Max 5 MB.</div>';
    } else {
        $safeU = preg_replace('/[^a-zA-Z0-9]/','',$_SESSION['username']);
        $dest  = UPLOAD_DIR . $safeU . '_' . date('Ymd_His') . '.' . $ok[$mime];
        $cur   = UPLOAD_DIR . $safeU . '_current.' . $ok[$mime];
        if (move_uploaded_file($f['tmp_name'], $dest)) {
            foreach (glob(UPLOAD_DIR.$safeU.'_current.*') as $old) unlink($old);
            copy($dest, $cur);
            $msg = '<div class="alert alert-success">Bild uppladdad!</div>';
        }
    }
}

$safeU  = preg_replace('/[^a-zA-Z0-9]/', '', $_SESSION['username']);
$picAbs = UPLOAD_DIR . $safeU . '_current.jpg';
$picAbsP= UPLOAD_DIR . $safeU . '_current.png';
$picWeb = file_exists($picAbs) ? UPLOAD_WEB.$safeU.'_current.jpg'
        : (file_exists($picAbsP) ? UPLOAD_WEB.$safeU.'_current.png' : '');

// Likes received
$likeCount = (int)db()->prepare("SELECT COUNT(*) FROM likes WHERE target_id=? AND value=1")->execute([$uid]) ? db()->query("SELECT COUNT(*) FROM likes WHERE target_id=$uid AND value=1")->fetchColumn() : 0;
$lkSt = db()->prepare("SELECT COUNT(*) FROM likes WHERE target_id=? AND value=1"); $lkSt->execute([$uid]); $likeCount=(int)$lkSt->fetchColumn();

// Comments on my profile
$cmSt = db()->prepare("SELECT c.*,u.username AS cu,u.real_name AS cn FROM comments c JOIN users u ON c.user_id=u.id WHERE c.target_user_id=? AND c.is_deleted=0 ORDER BY c.created_at DESC");
$cmSt->execute([$uid]);
$comments = $cmSt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Min profil – LoveMatch</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="profile-container">
  <div class="profile-header">
    <?php if ($picWeb): ?>
      <img src="<?= htmlspecialchars($picWeb) ?>" class="profile-avatar-large" alt="profil">
    <?php else: ?><div class="profile-avatar-placeholder-large">💕</div><?php endif; ?>
    <div class="profile-info">
      <h1><?= htmlspecialchars($user['real_name']) ?></h1>
      <p>@<?= htmlspecialchars($user['username']) ?></p>
      <p>📧 <?= htmlspecialchars($user['email']) ?></p>
      <p>📍 <?= htmlspecialchars($user['city'] ?: '–') ?></p>
      <p>💰 <?= number_format((int)$user['annual_salary']) ?> €/år</p>
      <p>👍 <?= $likeCount ?> gillar</p>
      <?php if (!empty($_SESSION['last_login'])): ?>
        <p class="last-login">Senast inloggad: <?= htmlspecialchars($_SESSION['last_login']) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <?= $msg ?>

  <div class="profile-sections">
    <!-- Upload pic -->
    <div class="profile-section">
      <h2>📷 Ladda upp profilbild</h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="file" name="pic" accept=".jpg,.jpeg,.png" required style="margin-bottom:.75rem;display:block">
        <button type="submit" class="btn btn-secondary">Ladda upp</button>
      </form>
    </div>

    <!-- Edit profile -->
    <div class="profile-section" style="grid-column:1/-1">
      <h2>✏️ Redigera profil</h2>
      <form method="POST" class="profile-form">
        <div class="form-row">
          <div class="form-group">
            <label>Riktigt namn</label>
            <input type="text" name="real_name" value="<?= htmlspecialchars($user['real_name']) ?>" required>
          </div>
          <div class="form-group">
            <label>E-post</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Stad</label>
            <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>">
          </div>
          <div class="form-group">
            <label>Årslön (€)</label>
            <input type="number" name="annual_salary" min="0" value="<?= (int)$user['annual_salary'] ?>">
          </div>
          <div class="form-group">
            <label>Jag söker:</label>
            <select name="preference">
              <?php foreach ($prefs as $p): ?>
                <option <?= $user['preference']===$p?'selected':'' ?>><?= $p ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Om mig</label>
          <textarea name="about_me" rows="3"><?= htmlspecialchars($user['about_me'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Nytt lösenord (lämna tomt = behåll)</label>
          <input type="password" name="new_password" minlength="8" placeholder="Minst 8 tecken">
        </div>
        <button type="submit" name="save" class="btn btn-primary">Spara ändringar</button>
      </form>
    </div>

    <!-- Comments on my profile -->
    <div class="profile-section" style="grid-column:1/-1">
      <h2>💬 Meddelanden på min profil (<?= count($comments) ?>)</h2>
      <?php if (!$comments): ?>
        <p>Inga meddelanden ännu.</p>
      <?php else: ?>
        <?php foreach ($comments as $c): ?>
          <div class="comment-card">
            <div class="comment-header">
              <span class="comment-user">👤 <?= htmlspecialchars($c['cn']) ?> (@<?= htmlspecialchars($c['cu']) ?>)</span>
              <div style="display:flex;gap:.5rem;align-items:center">
                <span class="comment-time"><?= htmlspecialchars($c['created_at']) ?></span>
                <a href="view_profile.php?id=<?= $c['user_id'] ?>#commentform" class="btn btn-small btn-primary">Svara</a>
              </div>
            </div>
            <p class="comment-text"><?= nl2br(htmlspecialchars($c['comment_text'])) ?></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Delete profile -->
    <div class="profile-section danger-zone">
      <h2>⚠️ Ta bort profil</h2>
      <p>Raderar din profil permanent. Kan inte ångras!</p>
      <form method="POST" onsubmit="return confirm('Säker? Profilen raderas permanent!')">
        <div class="form-group">
          <label>Bekräfta med lösenord:</label>
          <input type="password" name="confirm_pass" required placeholder="Ditt lösenord">
        </div>
        <button type="submit" name="delete_profile" class="btn btn-danger">Radera min profil</button>
      </form>
    </div>
  </div>
</div>
<footer class="footer"><p>&copy; <?= date('Y') ?> LoveMatch 💕</p></footer>
</body>
</html>
