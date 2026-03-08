<?php
require_once __DIR__ . '/config.php';

$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;
$offset  = ($page - 1) * $perPage;

$pref    = $_GET['preference'] ?? '';
$minLikes= max(0, (int)($_GET['min_likes'] ?? 0));
$sort    = in_array($_GET['sort'] ?? '', ['annual_salary','like_count','created_at'])
           ? $_GET['sort'] : 'created_at';

$where  = ["u.role = 'user'"];
$params = [];
if ($pref && in_array($pref, ['Man','Kvinna','Båda','Annat','Alla'])) {
    $where[] = 'u.preference = ?';
    $params[] = $pref;
}
$whereSQL  = 'WHERE ' . implode(' AND ', $where);
$havingSQL = $minLikes > 0 ? 'HAVING like_count >= ?' : '';

// Count
$cntSQL = "SELECT COUNT(*) FROM (
  SELECT u.id, COALESCE(SUM(CASE WHEN l.value=1 THEN 1 ELSE 0 END),0) AS like_count
  FROM users u LEFT JOIN likes l ON l.target_id=u.id
  $whereSQL GROUP BY u.id $havingSQL
) sub";
$cntSt = db()->prepare($cntSQL);
$cntParams = $params;
if ($minLikes > 0) $cntParams[] = $minLikes;
$cntSt->execute($cntParams);
$total = (int)$cntSt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Main query
$sql = "SELECT u.id, u.username, u.real_name, u.city, u.about_me, u.preference,
               u.annual_salary, u.email, u.created_at,
               COALESCE(SUM(CASE WHEN l.value=1  THEN 1 ELSE 0 END),0) AS like_count,
               COALESCE(SUM(CASE WHEN l.value=-1 THEN 1 ELSE 0 END),0) AS dislike_count
        FROM users u LEFT JOIN likes l ON l.target_id=u.id
        $whereSQL GROUP BY u.id $havingSQL
        ORDER BY $sort DESC LIMIT ? OFFSET ?";

$stParams = $params;
if ($minLikes > 0) $stParams[] = $minLikes;
$stParams[] = $perPage;
$stParams[] = $offset;

$st = db()->prepare($sql);
$st->execute($stParams);
$rows = $st->fetchAll();

// My votes
$myVotes = [];
if (loggedIn()) {
    $vst = db()->prepare("SELECT target_id, value FROM likes WHERE voter_id=?");
    $vst->execute([$_SESSION['user_id']]);
    foreach ($vst->fetchAll() as $v) $myVotes[$v['target_id']] = (int)$v['value'];
}

function qstr(array $extra = []): string {
    $p = array_merge($_GET, $extra);
    unset($p['page']);
    return http_build_query($p);
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>LoveMatch – Annonser</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>

<div class="page-container">
  <section class="hero">
    <h1>💕 LoveMatch</h1>
    <p>Hitta kärleken online!</p>
    <?php if (!loggedIn()): ?>
      <div class="hero-buttons">
        <a href="register.php" class="btn btn-large btn-primary">Registrera gratis</a>
        <a href="login.php"    class="btn btn-large btn-outline">Logga in</a>
      </div>
      <p class="hint-text">💡 Logga in för att se e-post/lön och gilla annonser!</p>
    <?php endif; ?>
  </section>

  <!-- Filters -->
  <section class="filters-section">
    <form method="GET" class="filters-form">
      <div class="filter-group">
        <label>Preferens:</label>
        <select name="preference" onchange="this.form.submit()">
          <option value="">Alla</option>
          <?php foreach (['Man','Kvinna','Båda','Annat','Alla'] as $p): ?>
            <option value="<?= $p ?>" <?= $pref===$p ? 'selected' : '' ?>><?= $p ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="filter-group">
        <label>Min. gillar:</label>
        <input type="number" name="min_likes" min="0" value="<?= $minLikes ?>" style="width:70px">
      </div>
      <div class="filter-group">
        <label>Sortera:</label>
        <select name="sort">
          <option value="created_at"    <?= $sort==='created_at'    ? 'selected':'' ?>>Nyaste</option>
          <option value="annual_salary" <?= $sort==='annual_salary' ? 'selected':'' ?>>Högst lön</option>
          <option value="like_count"    <?= $sort==='like_count'    ? 'selected':'' ?>>Mest gillad</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Filtrera</button>
      <a href="index.php" class="btn btn-secondary">Återställ</a>
    </form>
  </section>

  <section class="ads-section">
    <h2>Annonser – <?= $total ?> totalt (sida <?= $page ?>/<?= $totalPages ?>)</h2>

    <?php if (!$rows): ?>
      <div class="empty-state"><p>😔 Inga annonser. <a href="index.php">Visa alla</a></p></div>
    <?php else: ?>
      <div class="ads-grid">
      <?php foreach ($rows as $u):
        if (loggedIn() && (int)$u['id'] === (int)$_SESSION['user_id']) continue;
        $safeU  = preg_replace('/[^a-zA-Z0-9]/', '', $u['username']);
        $picAbs = UPLOAD_DIR . $safeU . '_current.jpg';
        $picWeb = UPLOAD_WEB . $safeU . '_current.jpg';
        $myVote = $myVotes[$u['id']] ?? 0;
      ?>
        <div class="ad-card">
          <div class="ad-header">
            <div class="ad-avatar">
              <?php if (file_exists($picAbs)): ?>
                <img src="<?= htmlspecialchars($picWeb) ?>" alt="foto">
              <?php else: ?><div class="avatar-placeholder">💕</div><?php endif; ?>
            </div>
            <div class="ad-meta">
              <h3><?= htmlspecialchars($u['real_name']) ?></h3>
              <span class="ad-username">@<?= htmlspecialchars($u['username']) ?></span>
              <span class="ad-city">📍 <?= htmlspecialchars($u['city'] ?: 'Finland') ?></span>
              <span class="badge">❤️ Söker: <?= htmlspecialchars($u['preference']) ?></span>
            </div>
          </div>

          <p class="ad-text"><?= htmlspecialchars(mb_substr($u['about_me'] ?? '', 0, 180)) ?><?= mb_strlen($u['about_me'] ?? '') > 180 ? '…' : '' ?></p>

          <?php if (loggedIn()): ?>
            <div class="ad-private">
              <span>📧 <?= htmlspecialchars($u['email']) ?></span>
              <span>💰 <?= number_format((int)$u['annual_salary']) ?> €/år</span>
            </div>
          <?php else: ?>
            <div class="ad-locked">🔒 Logga in för att se e-post och lön</div>
          <?php endif; ?>

          <div class="ad-footer">
            <div class="like-section">
              <span class="like-count">👍 <?= $u['like_count'] ?></span>
              <span class="like-count">👎 <?= $u['dislike_count'] ?></span>
              <?php if (loggedIn()): ?>
                <form method="POST" action="like.php" style="display:inline">
                  <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="value" value="1">
                  <input type="hidden" name="back" value="index.php?<?= htmlspecialchars(qstr(['page'=>$page])) ?>&page=<?= $page ?>">
                  <button class="like-btn <?= $myVote===1?'active':'' ?>">👍</button>
                </form>
                <form method="POST" action="like.php" style="display:inline">
                  <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="value" value="-1">
                  <input type="hidden" name="back" value="index.php?<?= htmlspecialchars(qstr(['page'=>$page])) ?>&page=<?= $page ?>">
                  <button class="like-btn <?= $myVote===-1?'active-dislike':'' ?>">👎</button>
                </form>
              <?php endif; ?>
            </div>
            <a href="view_profile.php?id=<?= $u['id'] ?>" class="btn btn-small btn-primary">Visa profil</a>
          </div>
        </div>
      <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?<?= htmlspecialchars(qstr()) ?>&page=<?= $page-1 ?>" class="btn btn-secondary">← Föregående</a>
        <?php endif; ?>
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
          <a href="?<?= htmlspecialchars(qstr()) ?>&page=<?= $i ?>"
             class="btn btn-small <?= $i===$page?'btn-primary':'btn-secondary' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="?<?= htmlspecialchars(qstr()) ?>&page=<?= $page+1 ?>" class="btn btn-secondary">Nästa →</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
</div>
<footer class="footer"><p>&copy; <?= date('Y') ?> LoveMatch 💕</p></footer>
</body>
</html>
