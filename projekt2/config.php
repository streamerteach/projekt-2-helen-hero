<?php
// =============================================
// config.php – Projekt 2 bootstrap
// Include this at TOP of every PHP file
// =============================================

if (session_status() === PHP_SESSION_NONE) session_start();

// ── Database settings – change these! ─────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'herohele');
define('DB_USER', 'herohele');
define('DB_PASS', 'ZnNXBTC8Va');
// ──────────────────────────────────────────────────────────

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_WEB', 'uploads/');

if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

// PDO singleton
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        die('<div style="font-family:sans-serif;padding:2rem;background:#ffebee;color:#c62828;max-width:700px;margin:2rem auto;border-radius:8px">
            <h2>⚠️ Databasfel</h2>
            <p>Kunde inte ansluta till databasen. Kontrollera <code>config.php</code>.</p>
            <p>Fel: ' . htmlspecialchars($e->getMessage()) . '</p>
            <p>Kom ihåg att importera <code>database.sql</code> och ange rätt DB_HOST/DB_NAME/DB_USER/DB_PASS.</p>
        </div>');
    }
    return $pdo;
}

function loggedIn(): bool { return isset($_SESSION['user_id']); }
function isManager(): bool { return in_array($_SESSION['role'] ?? '', ['manager','admin']); }
function redirect(string $url): void { header('Location: ' . $url); exit; }
