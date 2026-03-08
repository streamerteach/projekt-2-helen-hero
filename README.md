# 💕 LoveMatch – Projekt 1

En filbaserad dejtingsajt byggd med PHP för kursen Webbprogrammering vid Arcada yrkeshögskola.

## 🌐 Live-länk
[https://cgi.arcada.fi/~herohele/projekt1/](https://cgi.arcada.fi/~herohele/projekt1/)

## 📋 Beskrivning
LoveMatch är en dejtingsajt där användare kan registrera sig, logga in, ladda upp profilbilder och lämna kommentarer i gästboken. All data lagras i textfiler på servern – ingen databas används.

## ✅ Funktioner
- 🍪 Cookie-banner (godkänn / avvisa)
- 👋 Hälsning för återkommande besökare (cookie-baserad)
- 🖥️ Serverinformation (PHP-version, webbserver)
- 📅 Datum på finländskt format + veckonummer
- ⏳ Datumformulär med nedräkning i realtid (JavaScript)
- 📝 Registrering med slumpmässigt genererat lösenord
- 🔐 Login med PHP-sessioner
- 👤 Profilsida med möjlighet att uppdatera e-post
- 📷 Bilduppladdning (JPG/PNG, max 5 MB, MIME-kontroll)
- 💬 Gästbok (nyaste kommentaren visas överst)
- 📊 Besöksräknare med unika IP-adresser

## 🗂️ Filstruktur
```
projekt1/
├── index.php          # Startsida
├── register.php       # Registrering
├── login.php          # Inloggning
├── logout.php         # Utloggning
├── profile.php        # Profilsida
├── upload.php         # Bilduppladdning
├── guestbook.php      # Gästbok
├── navbar.php         # Navigation
├── datetime.php       # Datum + nedräkning
├── visit_counter.php  # Besöksräknare
├── rapport.php        # Reflektiv rapport
├── init.php           # Gemensam bootstrap
├── style.css          # CSS
├── data/              # Textfiler (users, gästbok, besök)
└── uploads/           # Uppladdade bilder
```

## 🚀 Installation på Arcada
1. Ladda upp alla filer till `~/html/projekt1/` via FileZilla
2. Sätt behörigheter: `chmod 777 data/ uploads/`
3. Öppna `https://cgi.arcada.fi/~herohele/projekt1/`

## 🧪 Testa
1. Gå till `register.php` – registrera ett konto, lösenordet visas på skärmen
2. Logga in med användarnamn och lösenord
3. Ladda upp en profilbild
4. Skriv i gästboken

## 🛠️ Tekniker
- PHP 8
- HTML5 / CSS3
- JavaScript
- Filbaserad datalagring (textfiler)

## 👩‍💻 Utvecklare
Helen Hero – Arcada yrkeshögskola 2026
