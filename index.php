<?php
// index.php – Front Controller + Output Buffering
ob_start();
session_start();

// Adatbázis‑sémák létrehozása TESSZT környezetben, de élesben MySQL‑re váltunk, ezért kommentben hagyva:
// require __DIR__ . '/data/init_db.php';

require_once __DIR__ . '/config/menu.php';
require_once __DIR__ . '/includes/functions.php';

$page = $_GET['page'] ?? 'home';

// Vendégként elérhető oldalak
$public_pages  = ['home','login','register','contact'];
// Bejelentkezetteknek elérhető oldalak
$protected_pages = [
  'dashboard','projects','add_project',
  'tasks','add_task',
  'documents','gallery','upload',
  'messages','logout'
];
// Vendég‑only oldalak (bejelentkezett ne férjen hozzá)
$guest_only    = ['login','register','contact'];

// 1) Vendég, de nem publikus oldal? → login
if (empty($_SESSION['user']) && !in_array($page, $public_pages, true)) {
    header('Location: index.php?page=login');
    exit;
}
// 2) Bejelentkezett, de vendég‑only oldal? → home
if (!empty($_SESSION['user']) && in_array($page, $guest_only, true)) {
    header('Location: index.php?page=home');
    exit;
}

// 3) Létező oldal-e?
$allowed = array_merge($public_pages, $protected_pages);
if (!in_array($page, $allowed, true)) {
    http_response_code(404);
    echo "<h2>404 – Az oldal nem található.</h2>";
    ob_end_flush();
    exit;
}

// 4) Templétek betöltése
include __DIR__ . '/includes/header.php';
include __DIR__ . '/templates/' . $page . '.php';
include __DIR__ . '/includes/footer.php';

ob_end_flush();
