<?php
// templates/documents.php
$db = getUserDb();
$username = $_SESSION['user']['username'];

// 1) Tábla létrehozása
$db->exec("CREATE TABLE IF NOT EXISTS documents (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50) NOT NULL,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    uploaded_at   DATETIME,
    comment       TEXT
)");

// 2) Felhasználói mappa biztosítása
$userDir = getUserDocDir();
if (!is_dir($userDir)) {
    if (!mkdir($userDir, 0755, true)) {
        die("<div class='alert alert-danger'>Nem sikerült létrehozni a felhasználói mappát: {$userDir}</div>");
    }
}

$error = '';
$success = '';

// 3) POST: feltöltés, komment, törlés
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Feltöltés
    if (isset($_FILES['doc'])) {
        $f = $_FILES['doc'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $error = "Feltöltési hiba: " . $f['error'];
        } else {
            $comment = trim($_POST['comment'] ?? '');
            $ext     = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf','doc','docx','xls','xlsx','txt'];
            if (!in_array($ext, $allowed, true)) {
                $error = 'Csak PDF/DOC/DOCX/XLS/XLSX/TXT fájlokat tölthetsz fel!';
            } else {
                $orig  = basename($f['name']);
                $fname = time().'_'.preg_replace('/[^a-zA-Z0-9_\.-]/','_',$orig);
                $dest  = $userDir . DIRECTORY_SEPARATOR . $fname;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $stmt = $db->prepare("
                      INSERT INTO documents (username, filename, original_name, uploaded_at, comment)
                      VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                      $username,
                      $fname,
                      $orig,
                      date('Y-m-d H:i:s'),
                      $comment
                    ]);
                    $success = 'Sikeresen feltöltve!';
                } else {
                    $error = 'Nem sikerült a fájl mentése a szerverre.';
                }
            }
        }
    }

    // Megjegyzés mentése
    if (($_POST['action'] ?? '') === 'update_comment') {
        $id      = intval($_POST['id']);
        $comment = trim($_POST['comment'] ?? '');
        $stmt = $db->prepare("UPDATE documents SET comment = ? WHERE id = ? AND username = ?");
        $stmt->execute([$comment, $id, $username]);
        header('Location: index.php?page=documents');
        exit;
    }

    // Törlés
    if (($_POST['action'] ?? '') === 'delete_doc') {
        $id = intval($_POST['id']);
        $row = $db->prepare("SELECT filename FROM documents WHERE id = ? AND username = ?");
        $row->execute([$id, $username]);
        $d = $row->fetch(PDO::FETCH_ASSOC);
        if ($d) {
            @unlink($userDir . DIRECTORY_SEPARATOR . $d['filename']);
        }
        $stmt = $db->prepare("DELETE FROM documents WHERE id = ? AND username = ?");
        $stmt->execute([$id, $username]);
        header('Location: index.php?page=documents');
        exit;
    }
}

// 4) Dokumentumok lekérése
$stmt = $db->prepare("SELECT * FROM documents WHERE username = ? ORDER BY uploaded_at DESC");
$stmt->execute([$username]);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$baseUrl = "uploads/documents/{$username}/";
?>

<div class="container">
  <h2>Dokumentumok</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php elseif ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- Feltöltő űrlap -->
  <form method="POST" enctype="multipart/form-data" class="mb-4 row g-3">
    <div class="col-md-5">
      <input type="file" name="doc" class="form-control" required>
    </div>
    <div class="col-md-5">
      <input type="text" name="comment" class="form-control" placeholder="Megjegyzés">
    </div>
    <div class="col-md-2">
      <button class="btn btn-success w-100">Feltöltés</button>
    </div>
  </form>

  <?php if (empty($docs)): ?>
    <p>Még nincs feltöltött dokumentum.</p>
  <?php else: ?>
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Fájl</th>
          <th>Feltöltés dátuma</th>
          <th>Megjegyzés</th>
          <th>Művelet</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($docs as $d): ?>
        <tr>
          <td>
            <a href="<?= htmlspecialchars($baseUrl . $d['filename']) ?>" target="_blank">
              <?= htmlspecialchars($d['original_name']) ?>
            </a>
          </td>
          <td><?= htmlspecialchars($d['uploaded_at']) ?></td>
          <td style="width:35%;">
            <form method="post" class="d-flex">
              <input type="hidden" name="action" value="update_comment">
              <input type="hidden" name="id"     value="<?= $d['id'] ?>">
              <input type="text" name="comment" class="form-control form-control-sm"
                     value="<?= htmlspecialchars($d['comment']) ?>">
              <button class="btn btn-sm btn-primary ms-2">Mentés</button>
            </form>
          </td>
          <td>
            <form method="post"
                  onsubmit="return confirm('Biztos törlöd a dokumentumot?');">
              <input type="hidden" name="action" value="delete_doc">
              <input type="hidden" name="id"     value="<?= $d['id'] ?>">
              <button class="btn btn-sm btn-danger">Törlés</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
