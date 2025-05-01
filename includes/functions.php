<?php
// includes/functions.php
// (itt már NINCS session_start(), azt csak az index.php indítja)

/**
 * MySQL adatbázis kapcsolat
 */
function getDbConnection() {
    //$host = 'localhost';
    //$db = 'adatb01';
    //$user = 'root';
    //$pass = '';
    $host = 'mysql.omega';
    $db = 'adatbzs01';
    $user = 'adatbzs01';  // <-- ide jön a tárhely MySQL felhasználónév
    $pass = '22WWvsi46csu53aac5usv54s';          // <-- ide a MySQL jelszó
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, $user, $pass, $options);
}

/**
 * Master adatbázis (hitelesítéshez)
 */
function getAuthDb() {
    return getDbConnection();
}

/**
 * Bejelentkezett felhasználó saját adatbázisa (projektek, feladatok, üzenetek)
 */
function getUserDb() {
    if (empty($_SESSION['user']['username'])) {
        throw new Exception("Nincs bejelentkezett felhasználó.");
    }
    return getDbConnection();
}

/**
 * Saját képek mappája
 */
function getUserImageDir() {
    $u = preg_replace('/[^a-zA-Z0-9_]/', '', $_SESSION['user']['username']);
    $dir = __DIR__ . "/../uploads/images/{$u}";
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir;
}

/**
 * Saját dokumentumok mappája
 */
function getUserDocDir() {
    $u = preg_replace('/[^a-zA-Z0-9_]/', '', $_SESSION['user']['username']);
    $dir = __DIR__ . "/../uploads/documents/{$u}";
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir;
}

/**
 * Projektstátusz: futó (active) projektek száma
 */
function countActiveProjects() {
    $db = getUserDb();
    $stmt = $db->query("SELECT COUNT(*) FROM projects WHERE status = 'active'");
    return (int)$stmt->fetchColumn();
}

/**
 * Összköltség (budget összege)
 */
function totalBudget() {
    $db = getUserDb();
    $stmt = $db->query("SELECT SUM(budget) FROM projects");
    $sum = $stmt->fetchColumn();
    return $sum !== null ? (float)$sum : 0.0;
}

/**
 * Lejáró (due_date <= ma és status != done) feladatok száma
 */
function countDueTasks() {
    $db = getUserDb();
    $today = date('Y-m-d');
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM tasks 
        WHERE due_date <= :today 
          AND status != 'done'
    ");
    $stmt->execute([':today' => $today]);
    return (int)$stmt->fetchColumn();
}

/**
 * Olvasatlan üzenetek száma
 */
function countUnreadMessages() {
    $db = getUserDb();
    $stmt = $db->query("SELECT COUNT(*) FROM messages WHERE read_flag = 0");
    return (int)$stmt->fetchColumn();
}

/**
 * Valuta formázás (Forint)
 */
function formatCurrency($value) {
    return number_format((float)$value, 0, ',', ' ') . "\xa0Ft";
}

/**
 * Egyszerű átirányítás
 */
function redirect($page) {
    header("Location: index.php?page=" . urlencode($page));
    exit;
}

/**
 * Másik felhasználó adatbázisának megnyitása
 * @param string $username – a felhasználó felhasználóneve
 * @return PDO
 * @throws Exception, ha nincs adatbázis
 */
function getOtherUserDb(string $username) {
    return getDbConnection();
}
