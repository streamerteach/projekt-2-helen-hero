<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Rapport – Projekt 2</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .rap{max-width:900px;margin:2rem auto;padding:0 1.5rem}
    .rap-s{background:#fff;border-radius:12px;padding:2rem;margin-bottom:2rem;box-shadow:0 2px 8px rgba(0,0,0,.1)}
    .rap-s h2{color:#e91e8c;border-bottom:2px solid #f8bbd0;padding-bottom:.5rem;margin-bottom:1rem}
    pre{background:#f5f5f5;border-radius:8px;padding:1.5rem;font-size:.8rem;line-height:1.8;overflow-x:auto}
    .tl{list-style:none;padding:0}.tl li{padding:.4rem 0;border-bottom:1px solid #f0f0f0}.tl li::before{content:"✅ "}
    .lg{display:flex;gap:.75rem;flex-wrap:wrap}
  </style>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="rap">
  <h1 style="text-align:center;color:#e91e8c">📋 Projekt 2 – Rapport</h1>

  <div class="rap-s">
    <h2>🔗 Testa alla delar</h2>
    <div class="lg">
      <a href="index.php"        class="btn btn-primary">🏠 Annonser</a>
      <a href="register.php"     class="btn btn-secondary">📝 Registrera</a>
      <a href="login.php"        class="btn btn-secondary">🔐 Login</a>
      <a href="profile.php"      class="btn btn-secondary">👤 Profil</a>
      <a href="admin.php"        class="btn btn-secondary">🛠️ Admin</a>
    </div>
    <h3 style="margin-top:1.5rem">Testanvändare:</h3>
    <table style="border-collapse:collapse;font-size:.9rem;margin-top:.5rem">
      <tr style="background:#f5f5f5"><th style="padding:.4rem 1rem">Användarnamn</th><th style="padding:.4rem 1rem">Lösenord</th><th style="padding:.4rem 1rem">Roll</th></tr>
      <tr><td style="padding:.4rem 1rem">admin</td><td style="padding:.4rem 1rem">password</td><td style="padding:.4rem 1rem">admin</td></tr>
      <tr><td style="padding:.4rem 1rem">anna_hki</td><td style="padding:.4rem 1rem">password</td><td style="padding:.4rem 1rem">user</td></tr>
      <tr><td style="padding:.4rem 1rem">mikko_tre</td><td style="padding:.4rem 1rem">password</td><td style="padding:.4rem 1rem">user</td></tr>
    </table>
    <p style="margin-top:.75rem;font-size:.85rem;color:#888">
      OBS: Om lösenordet inte fungerar, kör <code>make_hash.php</code> på servern och uppdatera databasen.
    </p>
  </div>

  <div class="rap-s">
    <h2>🗃️ Databasdiagram</h2>
<pre>
┌─────────────────────────────────────┐
│              users                  │
├─────────────────────────────────────┤
│ id            INT PK AUTO_INC       │
│ username      VARCHAR(30) UNIQUE    │
│ real_name     VARCHAR(100)          │
│ password      VARCHAR(255)  ← bcrypt│
│ email         VARCHAR(150) UNIQUE   │
│ city          VARCHAR(100)          │
│ about_me      TEXT                  │
│ annual_salary INT                   │
│ preference    ENUM(Man,Kvinna,...)  │
│ role          ENUM(user,manager,...) │
│ created_at    DATETIME              │
└──────────┬──────────────────────────┘
           │ 1:N (voter/target)
    ┌──────┴──────────┐    ┌─────────────────────┐
    │     likes       │    │      comments       │
    ├─────────────────┤    ├─────────────────────┤
    │ id  INT PK      │    │ id              INT │
    │ voter_id  → u   │    │ user_id         → u │
    │ target_id → u   │    │ target_user_id  → u │
    │ value TINYINT   │    │ comment_text   TEXT │
    │  (1 eller -1)   │    │ is_deleted  TINYINT │
    └─────────────────┘    │ created_at DATETIME │
                           └─────────────────────┘
</pre>
  </div>

  <div class="rap-s">
    <h2>✅ Implementerade funktioner</h2>
    <ul class="tl">
      <li>MySQL-databas med tabeller: users, likes, comments</li>
      <li>PDO med prepared statements (skyddar mot SQL-injection)</li>
      <li>Lösenord hashas med bcrypt (password_hash)</li>
      <li>Login med SQL + sessions</li>
      <li>Registrering med alla obligatoriska fält</li>
      <li>Anonyma kan bläddra men ser ej e-post/lön (visas bara för inloggade)</li>
      <li>Profilsida: redigera alla fält + byta lösenord</li>
      <li>Radera profil med lösenordsverifiering</li>
      <li>Bilduppladdning direkt från profilsidan</li>
      <li>Sortering: datum, lön, antal gillar</li>
      <li>Filtrering: preferens + minsta gillar</li>
      <li>Paginering – 5 annonser per sida</li>
      <li>Gilla/ogilla (toggle, sparas i DB)</li>
      <li>Kommentarer på annonser (chat)</li>
      <li>Svara på kommentarer via view_profile.php</li>
      <li>Rollhantering: user / manager / admin</li>
      <li>Manager kan redigera alla profiler och roller</li>
      <li>Manager kan ta bort enskilda kommentarer (soft delete)</li>
      <li>Auto-moderering: bot rensar fula ord från kommentarer</li>
    </ul>
  </div>

  <div class="rap-s">
    <h2>🚀 Installation</h2>
    <ol style="line-height:2.2;margin-left:1.5rem">
      <li>Skapa en databas i MySQL (t.ex. <code>lovematch</code>)</li>
      <li>Importera <code>database.sql</code></li>
      <li>Öppna <code>config.php</code> och ange DB_HOST, DB_NAME, DB_USER, DB_PASS</li>
      <li>Se till att <code>uploads/</code> är skrivbar: <code>chmod 755 uploads/</code></li>
      <li>Öppna <code>index.php</code> – klart!</li>
      <li>Om lösenorden i exempeldatat inte fungerar: öppna <code>make_hash.php</code> och uppdatera</li>
    </ol>
  </div>

  <div class="rap-s">
    <h2>💭 Reflektion</h2>
    <p><strong>Bra:</strong> PDO gör SQL-hanteringen säker och elegant. Rollhanteringen är enkel men kraftfull.</p>
    <p><strong>Svårt:</strong> Att kombinera paginering med filtrering och sortering utan att tappa URL-parametrar krävde noggrannhet.</p>
    <p><strong>Roligt:</strong> Admin-panelen med auto-moderering och content management var kul att bygga.</p>
    <p><strong>Förbättringar:</strong> Tidigare introduktion av PDO och säker SQL-hantering i kursen hade hjälpt mycket.</p>
  </div>
</div>
<footer class="footer"><p>&copy; <?= date('Y') ?> LoveMatch 💕</p></footer>
</body>
</html>
