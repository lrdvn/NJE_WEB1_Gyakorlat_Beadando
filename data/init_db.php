<?php
// data/init_db.php

require_once __DIR__ . '/../includes/functions.php';

$db = getAuthDb();

// 1) users tábla
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    fullname   VARCHAR(255) NOT NULL,
    username   VARCHAR(50) UNIQUE NOT NULL,
    email      VARCHAR(255) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL
)");

// 2) projects tábla (minden felhasználó közös táblája)
$db->exec("CREATE TABLE IF NOT EXISTS projects (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    name     VARCHAR(255) NOT NULL,
    budget   FLOAT DEFAULT 0,
    status   VARCHAR(50) DEFAULT 'planned'
)");

// 3) tasks tábla
$db->exec("CREATE TABLE IF NOT EXISTS tasks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL,
    project_id  INT NOT NULL,
    title       VARCHAR(255) NOT NULL,
    due_date    DATE,
    status      VARCHAR(50) DEFAULT 'pending'
)");

// 4) messages tábla
$db->exec("CREATE TABLE IF NOT EXISTS messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50) NOT NULL,
    name       VARCHAR(255) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    message    TEXT NOT NULL,
    read_flag  TINYINT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 5) Tesztfelhasználók
$plain = '1234';
$hash  = password_hash($plain, PASSWORD_DEFAULT);

try {
    $stmt = $db->prepare("INSERT IGNORE INTO users (fullname, username, email, password) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Próba Felhasználó', 'proba', 'proba@example.com', $hash]);
    $stmt->execute(['Második Felhasználó', 'proba2', 'proba2@example.com', $hash]);
} catch (PDOException $e) {
    echo "Hiba a felhasználók beszúrásánál: " . $e->getMessage();
    exit;
}

// 6) Mintaprojektek létrehozása
foreach (['proba', 'proba2'] as $username) {
    for ($i = 1; $i <= 11; $i++) {
        $name = "Projekt $i";
        $budget = rand(10e6, 100e6);
        $status = ['planned', 'active', 'completed'][array_rand(['planned', 'active', 'completed'])];

        $stmt = $db->prepare("INSERT INTO projects (username, name, budget, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $name, $budget, $status]);
    }

    // uploads mappák létrehozása
    @mkdir(__DIR__ . "/../uploads/images/$username", 0755, true);
    @mkdir(__DIR__ . "/../uploads/documents/$username", 0755, true);
}

echo "✅ MySQL adatbázis inicializálva, tesztfelhasználók és projektek létrehozva.";
