<?php
require_once __DIR__ . '/config.php';

$error   = '';
$success = '';
$prefs   = ['Man','Kvinna','Båda','Annat','Alla'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username']      ?? '');
    $realName  = trim($_POST['real_name']     ?? '');
    $password  = $_POST['password']           ?? '';
    $password2 = $_POST['password2']          ?? '';
    $email     = trim($_POST['email']         ?? '');
    $city      = trim($_POST['city']          ?? '');
    $about     = trim($_POST['about_me']      ?? '');
    $salary    = (int)($_POST['annual_salary']?? 0);
    $pref      = $_POST['preference']         ?? 'Alla';

    if (strlen($username) < 3 || strlen($username) > 30)    $error = 'Användarnamn: 3–30 tecken.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))       $error = 'Ogiltig e-postadress.';
    elseif (strlen($password) < 8)                            $error = 'Lösenordet måste vara minst 8 tecken.';
    elseif ($password !== $password2)                         $error = 'Lösenorden matchar inte.';
    elseif (!in_array($pref, $prefs))                         $error = 'Ogiltig preferens.';
    else {
        $st = db()->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $st->execute([$username, $email]);
        if ($st->fetch()) {
            $error = 'Användarnamnet eller e-posten är redan registrerad.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            db()->prepare("INSERT INTO users (username,real_name,password,email,city,about_me,annual_salary,preference) VALUES (?,?,?,?,?,?,?,?)")
               ->execute([$username,$realName,$hash,$email,$city,$about,$salary,$pref]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Registrera – LoveMatch</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="auth-container" style="max-width:620px">
  <div class="auth-card">
    <h1>💕 Registrera dig</h1>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success">✅ Registrering lyckades! Nu kan du logga in.</div>
      <a href="login.php" class="btn btn-primary btn-full" style="margin-top:1rem">Logga in →</a>
    <?php else: ?>
    <form method="POST" class="auth-form">
      <div class="form-row">
        <div class="form-group">
          <label>Användarnamn <span class="required">*</span></label>
          <input type="text" name="username" minlength="3" maxlength="30" required
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Riktigt namn <span class="required">*</span></label>
          <input type="text" name="real_name" required
                 value="<?= htmlspecialchars($_POST['real_name'] ?? '') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Lösenord <span class="required">*</span> (min 8 tecken)</label>
          <input type="password" name="password" minlength="8" required>
        </div>
        <div class="form-group">
          <label>Upprepa lösenord <span class="required">*</span></label>
          <input type="password" name="password2" required>
        </div>
      </div>
      <div class="form-group">
        <label>E-postadress <span class="required">*</span></label>
        <input type="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Stad</label>
          <input type="text" name="city" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Årslön (€)</label>
          <input type="number" name="annual_salary" min="0"
                 value="<?= htmlspecialchars($_POST['annual_salary'] ?? '0') ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Jag söker:</label>
        <select name="preference">
          <?php foreach ($prefs as $p): ?>
            <option value="<?= $p ?>" <?= ($_POST['preference'] ?? 'Alla') === $p ? 'selected' : '' ?>><?= $p ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Om mig (annonstext)</label>
        <textarea name="about_me" rows="3" placeholder="Skriv om dig själv..."><?= htmlspecialchars($_POST['about_me'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-full">Registrera</button>
    </form>
    <?php endif; ?>
    <p class="auth-link">Redan konto? <a href="login.php">Logga in</a></p>
  </div>
</div>
<footer class="footer"><p>&copy; <?= date('Y') ?> LoveMatch 💕</p></footer>
</body>
</html>
