<?php
// templates/gallery.php

$db = getUserDb();
$username = $_SESSION['user']['username'];

// Tábla létrehozása, ha még nincs
$db->exec("CREATE TABLE IF NOT EXISTS images (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    username       VARCHAR(50) NOT NULL,
    filename       VARCHAR(255) NOT NULL,
    title          VARCHAR(255),
    capture_date   DATE,
    uploaded_date  DATETIME,
    comment        TEXT
)");

$baseDir = __DIR__ . "/../uploads/images/{$username}/";
$baseUrl = "uploads/images/{$username}/";

// POST‑kezelés: megjegyzés frissítése, kép törlése
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1) Megjegyzés mentése
    if ($action === 'update_comment') {
        $id      = intval($_POST['id']);
        $comment = trim($_POST['comment']);
        $stmt = $db->prepare("UPDATE images SET comment = ? WHERE id = ? AND username = ?");
        $stmt->execute([$comment, $id, $username]);
    }

    // 2) Kép törlése
    if ($action === 'delete_image') {
        $id = intval($_POST['id']);
        // Fájlnév lekérése
        $stmt = $db->prepare("SELECT filename FROM images WHERE id = ? AND username = ?");
        $stmt->execute([$id, $username]);
        $filename = $stmt->fetchColumn();
        // Fájl törlése a lemezen
        $filePath = $baseDir . $filename;
        if ($filename && file_exists($filePath)) {
            unlink($filePath);
        }
        // Rekord törlése adatbázisból
        $stmt = $db->prepare("DELETE FROM images WHERE id = ? AND username = ?");
        $stmt->execute([$id, $username]);
    }

    // Vissza a galériához
    header('Location: index.php?page=gallery');
    exit;
}

// Képek lekérése
$stmt = $db->prepare("SELECT * FROM images WHERE username = ? ORDER BY uploaded_date DESC");
$stmt->execute([$username]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container mt-4">
  <h2>Galéria</h2>

  <?php if (empty($images)): ?>
    <p>Még nincs feltöltött kép.</p>
  <?php else: ?>
    <table class="table table-bordered align-middle">
      <thead>
        <tr>
          <th>Előnézet</th>
          <th>Cím</th>
          <th>Készítés dátuma</th>
          <th>Feltöltés dátuma</th>
          <th>Megjegyzés</th>
          <th>Művelet</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($images as $img): ?>
          <tr>
            <td style="width:120px">
              <a href="<?= htmlspecialchars($baseUrl . $img['filename']) ?>"
                 target="_blank"
                 download="<?= htmlspecialchars($img['filename']) ?>">
                <img src="<?= htmlspecialchars($baseUrl . $img['filename']) ?>"
                     style="max-width:100px; border:1px solid #ccc; padding:2px;">
              </a>
            </td>
            <td><?= htmlspecialchars($img['title']) ?></td>
            <td><?= htmlspecialchars($img['capture_date']) ?></td>
            <td><?= htmlspecialchars($img['uploaded_date']) ?></td>
            <td style="width:250px">
              <form method="post" class="mb-0">
                <input type="hidden" name="action" value="update_comment">
                <input type="hidden" name="id"     value="<?= $img['id'] ?>">
                <textarea name="comment"
                          class="form-control form-control-sm"
                          rows="2"
                ><?= htmlspecialchars($img['comment']) ?></textarea>
                <button type="submit" class="btn btn-sm btn-success mt-1">Mentés</button>
              </form>
            </td>
            <td style="width:100px">
              <form method="post" onsubmit="return confirm('Tényleg törli a képet?');">
                <input type="hidden" name="action" value="delete_image">
                <input type="hidden" name="id"     value="<?= $img['id'] ?>">
                <button class="btn btn-sm btn-danger">Törlés</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
