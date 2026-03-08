# 💕 LoveMatch – Projekt 2

En databasdriven dejtingsajt byggd med PHP och MySQL för kursen Webbprogrammering vid Arcada yrkeshögskola.

## 🌐 Live-länk
[https://cgi.arcada.fi/~herohele/projekt2/](https://cgi.arcada.fi/~herohele/projekt2/)

## 📋 Beskrivning
LoveMatch är en fullständig dejtingsajt med MySQL-databas, rollhantering och innehållsmoderering. Användare kan bläddra bland annonser, gilla/ogilla profiler och lämna kommentarer. Managers och admins har tillgång till ett adminpanel.

## ✅ Funktioner
- 📝 Registrering med fullständigt formulär
- 🔐 Login med PHP-sessioner och bcrypt-lösenord
- 👤 Profilsida – redigera uppgifter, byta lösenord, radera profil
- 📷 Bilduppladdning direkt från profilsidan
- 📋 Annonslistning med sortering och filtrering
- 🔍 Sortering: datum, årslön, antal gillar
- 🎯 Filtrering: preferens, minsta antal gillar
- 📄 Paginering – 5 annonser per sida
- 👍 Gilla/ogilla (toggle – klicka igen för att ta bort)
- 💬 Kommentarer på profiler
- 🔒 Anonym användare ser ej e-post/lön
- 👥 Rollhantering: user / manager / admin
- 🛠️ Adminpanel – redigera/radera användare och kommentarer
- 🤖 Auto-moderering – rensar fula ord automatiskt

## 🗃️ Databasstruktur
```
users       – id, username, real_name, password, email, city,
              about_me, annual_salary, preference, role, created_at

likes       – id, voter_id, target_id, value (1/-1), created_at

comments    – id, user_id, target_user_id, comment_text,
              is_deleted, created_at
```

## 🗂️ Filstruktur
```
projekt2/
├── index.php          # Annonslistning
├── login.php          # Inloggning
├── logout.php         # Utloggning
├── register.php       # Registrering
├── profile.php        # Min profil
├── view_profile.php   # Visa annan profil
├── like.php           # Gilla/ogilla hantering
├── admin.php          # Adminpanel
├── navbar.php         # Navigation
├── rapport.php        # Reflektiv rapport
├── config.php         # Databaskonfiguration
├── database.sql       # Databasschema
├── style.css          # CSS
└── uploads/           # Uppladdade bilder
```

## 🚀 Installation på Arcada
1. Ladda upp alla filer till `~/html/projekt2/` via FileZilla
2. Sätt behörigheter: `chmod 777 uploads/`
3. Ändra databasuppgifter i `config.php`
4. Öppna `install.php` i webbläsaren för att skapa databasen
5. Radera `install.php` från servern efteråt

## 🧪 Testanvändare
| Användarnamn | Lösenord | Roll  |
|-------------|----------|-------|
| admin       | password | admin |
| anna_hki    | password | user  |
| mikko_tre   | password | user  |
| sara_abo    | password | user  |

## 🛠️ Tekniker
- PHP 8
- MySQL med PDO (prepared statements)
- HTML5 / CSS3
- JavaScript

## 👩‍💻 Utvecklare
Helen Hero – Arcada yrkeshögskola 2026

