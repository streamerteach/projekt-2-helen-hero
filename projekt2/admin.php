<?php
require_once __DIR__ . '/config.php';
if (!loggedIn() || !isManager()) redirect('index.php');

$msg = '';

// Delete user
if (isset($_GET['del_user'])) {
    $uid = (int)$_GET['del_user'];
    if ($uid !== (int)$_SESSION['user_id']) {
        db()->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        $msg = '<div class="alert alert-success">Användare raderad.</div>';
    }
}

// Soft-delete comment
if (isset($_GET['del_comment'])) {
    db()->prepare("UPDATE comments SET is_deleted=1 WHERE id=?")->execute([(int)$_GET['del_comment']]);
    $back = $_GET['back'] ?? 'admin.php';
    if (!preg_match('/^[a-zA-Z0-9_.?=&%\-\/]+$/', $back)) $back = 'admin.php';
    redirect($back);
}

// Save user edit
if (isset($_POST['save_edit'])) {
    $uid  = (int)$_POST['uid'];
    $rn   = trim($_POST['real_name']??'');
    $em   = trim($_POST['email']??'');
    $city = trim($_POST['city']??'');
    $ab   = trim($_POST['about_me']??'');
    $sal  = (int)($_POST['annual_salary']??0);
    $role = in_array($_POST['role']??'',['user','manager','admin']) ? $_POST['role'] : 'user';
    db()->prepare("UPDATE users SET real_name=?,email=?,city=?,about_me=?,annual_salary=?,role=? WHERE id=?")
       ->execute([$rn,$em,$city,$ab,$sal,$role,$uid]);
    $msg = '<div class="alert alert-success">Sparad.</div>';
}

// Auto-moderate comments
if (isset($_POST['auto_mod'])) {
    $bad = ['spam','troll','idiot','hata','lögn'];
    $rows = db()->query("SELECT id,comment_text FROM comments WHERE is_deleted=0")->fetchAll();
    $n = 0;
    foreach ($rows as $r) {
        $clean = preg_replace('/\b(' . implode('|', array_map('preg_quote', $bad)) . ')\b/iu', '***', $r['comment_text']);
        if ($clean !== $r['comment_text']) {
            db()->prepare("UPDATE comments SET comment_text=? WHERE id=?")->execute([$clean,$r['id']]);
            $n++;
        }
    }
    $msg = "<div class='alert alert-success'>Auto-mod klar: $n kommentarer rensade.</div>";
}

// Edit form
$editUser = null;
if (isset($_GET['edit'])) {
    $st = db()->prepare("SELECT * FROM users WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $editUser = $st->fetch();
}

// Load all users
$users = db()->query("SELECT u.*,
  (SELECT COUNT(*) FROM likes l WHERE l.target_id=u.id AND l.value=1) AS likes,
  (SELECT COUNT(*) FROM comments c WHERE c.target_user_id=u.id AND c.is_deleted=0) AS ccount
  FROM users u ORDER BY u.role DESC,u.created_at DESC")->fetchAll();

// Latest comments
$comments = db()->query("SELECT c.*,u.username AS cu,t.username AS tu FROM comments c JOIN users u ON c.user_id=u.id JOIN users t ON c.target_user_id=t.id WHERE c.is_deleted=0 ORDER BY c.created_at DESC LIMIT 20")->fetchAll();
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin – LoveMatch</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="page-container">
  <h1 style="color:#e91e8c;margin-bottom:1.5rem">🛠️ Content Management</h1>
  <?= $msg ?>

  <?php if ($editUser): ?>
  <div style="background:#fff;border-radius:12px;padding:2rem;box-shadow:0 4px 16px rgba(0,0,0,.1);margin-bottom:2rem">
    <h2>✏️ Redigera <?= htmlspecialchars($editUser['username']) ?></h2>
    <form method="POST">
      <input type="hidden" name="uid" value="<?= $editUser['id'] ?>">
      <div class="form-row">
        <div class="form-group"><label>Namn</label><input type="text" name="real_name" value="<?= htmlspecialchars($editUser['real_name']) ?>" required></div>
        <div class="form-group"><label>E-post</label><input type="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" required></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Stad</label><input type="text" name="city" value="<?= htmlspecialchars($editUser['city']) ?>"></div>
        <div class="form-group"><label>Lön</label><input type="number" name="annual_salary" value="<?= (int)$editUser['annual_salary'] ?>"></div>
        <div class="form-group"><label>Roll</label>
          <select name="role">
            <?php foreach (['user','manager','admin'] as $r): ?>
              <option <?= $editUser['role']===$r?'selected':'' ?>><?= $r ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Om</label><textarea name="about_me" rows="2"><?= htmlspecialchars($editUser['about_me'] ?? '') ?></textarea></div>
      <button type="submit" name="save_edit" class="btn btn-primary">Spara</button>
      <a href="admin.php" class="btn btn-secondary">Avbryt</a>
    </form>
  </div>
  <?php endif; ?>

  <!-- Auto-mod -->
  <div style="background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,.1);margin-bottom:2rem">
    <h2>🤖 Auto-moderering</h2>
    <p>Rensar fula ord (spam, troll, idiot, hata, lögn) från alla kommentarer.</p>
    <form method="POST"><button type="submit" name="auto_mod" class="btn btn-secondary">Kör nu</button></form>
  </div>

  <!-- Users table -->
  <div style="background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,.1);margin-bottom:2rem;overflow-x:auto">
    <h2>👥 Användare (<?= count($users) ?>)</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.9rem">
      <tr style="background:#f5f5f5">
        <th style="padding:.5rem;text-align:left">ID</th><th style="padding:.5rem;text-align:left">Namn</th>
        <th style="padding:.5rem;text-align:left">E-post</th><th style="padding:.5rem;text-align:left">Roll</th>
        <th style="padding:.5rem;text-align:left">👍</th><th style="padding:.5rem;text-align:left">💬</th>
        <th style="padding:.5rem;text-align:left">Åtgärder</th>
      </tr>
      <?php foreach ($users as $u): ?>
      <tr style="border-bottom:1px solid #eee">
        <td style="padding:.5rem"><?= $u['id'] ?></td>
        <td style="padding:.5rem"><strong><?= htmlspecialchars($u['username']) ?></strong><br><small><?= htmlspecialchars($u['real_name']) ?></small></td>
        <td style="padding:.5rem"><?= htmlspecialchars($u['email']) ?></td>
        <td style="padding:.5rem"><span class="badge"><?= $u['role'] ?></span></td>
        <td style="padding:.5rem"><?= $u['likes'] ?></td>
        <td style="padding:.5rem"><?= $u['ccount'] ?></td>
        <td style="padding:.5rem;display:flex;gap:.3rem;flex-wrap:wrap">
          <a href="view_profile.php?id=<?= $u['id'] ?>" class="btn btn-small btn-secondary">Visa</a>
          <a href="admin.php?edit=<?= $u['id'] ?>" class="btn btn-small btn-primary">✏️</a>
          <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
            <a href="admin.php?del_user=<?= $u['id'] ?>" class="btn btn-small btn-danger"
               onclick="return confirm('Radera <?= htmlspecialchars($u['username']) ?>?')">🗑️</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <!-- Comments -->
  <div style="background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,.1)">
    <h2>💬 Senaste kommentarer</h2>
    <?php foreach ($comments as $c): ?>
      <div class="comment-card">
        <div class="comment-header">
          <span><strong><?= htmlspecialchars($c['cu']) ?></strong> → @<?= htmlspecialchars($c['tu']) ?></span>
          <div style="display:flex;gap:.5rem;align-items:center">
            <span class="comment-time"><?= htmlspecialchars($c['created_at']) ?></span>
            <a href="admin.php?del_comment=<?= $c['id'] ?>&back=admin.php"
               class="btn btn-small btn-danger" onclick="return confirm('Ta bort?')">🗑️</a>
          </div>
        </div>
        <p class="comment-text"><?= nl2br(htmlspecialchars($c['comment_text'])) ?></p>
      </div>
    <?php endforeach; ?>
    <?php if (!$comments): ?><p>Inga kommentarer.</p><?php endif; ?>
  </div>
</div>
<footer class="footer"><p>&copy; <?= date('Y') ?> LoveMatch 💕</p></footer>
</body>
</html>
