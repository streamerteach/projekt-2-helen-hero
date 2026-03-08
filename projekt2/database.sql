-- LoveMatch Projekt 2 – Databas
-- Importera denna fil till din MySQL-databas
-- mysql -u root -p lovematch < database.sql

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(30)  NOT NULL UNIQUE,
    real_name     VARCHAR(100) NOT NULL,
    password      VARCHAR(255) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    city          VARCHAR(100) DEFAULT '',
    about_me      TEXT,
    annual_salary INT          DEFAULT 0,
    preference    ENUM('Man','Kvinna','Båda','Annat','Alla') DEFAULT 'Alla',
    role          ENUM('user','manager','admin')             DEFAULT 'user',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS likes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    voter_id   INT NOT NULL,
    target_id  INT NOT NULL,
    value      TINYINT NOT NULL,   -- 1=gilla, -1=ogilla
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_vote (voter_id, target_id),
    FOREIGN KEY (voter_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comments (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    target_user_id INT NOT NULL,
    comment_text   TEXT NOT NULL,
    is_deleted     TINYINT DEFAULT 0,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)        REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Testanvändare, lösenord = "password" för alla
-- Genererat med: password_hash('password', PASSWORD_DEFAULT)
INSERT IGNORE INTO users (username, real_name, password, email, city, about_me, annual_salary, preference, role) VALUES
('admin',    'Admin Adminsson',  '$2y$10$TKh8H1.PfQ1q7PbAFnm53uOB5Jf1Wgp1JzM3qFOHe6N0Ax3P2Xu2', 'admin@lovematch.fi',  'Helsingfors', 'Jag är administratören för LoveMatch.',  80000, 'Alla',   'admin'),
('anna_hki', 'Anna Lindqvist',   '$2y$10$TKh8H1.PfQ1q7PbAFnm53uOB5Jf1Wgp1JzM3qFOHe6N0Ax3P2Xu2', 'anna@example.fi',    'Helsingfors', 'Glad 28-åring som älskar kafé, löpning och resor. Söker en snäll man!', 42000, 'Man',    'user'),
('mikko_tre','Mikko Virtanen',   '$2y$10$TKh8H1.PfQ1q7PbAFnm53uOB5Jf1Wgp1JzM3qFOHe6N0Ax3P2Xu2', 'mikko@example.fi',   'Tammerfors', 'Teknolog och naturälskare. Gillar vandring och matlagning.',            55000, 'Kvinna', 'user'),
('sara_abo',  'Sara Mäkinen',    '$2y$10$TKh8H1.PfQ1q7PbAFnm53uOB5Jf1Wgp1JzM3qFOHe6N0Ax3P2Xu2', 'sara@example.fi',    'Åbo',        'Grafisk designer och konstnär. Söker kreativt sällskap!',              38000, 'Båda',   'user');

-- OBS: Om lösenordet "password" inte fungerar med ovan hash,
-- logga in i PHP och kör: echo password_hash('password', PASSWORD_DEFAULT);
-- och uppdatera kolumnen manuellt i databasen.
