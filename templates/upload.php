<?php
// templates/upload.php

$db = getUserDb();
$username = $_SESSION['user']['username'];

// 1) Tábla létrehozása, ha még nincs
$db->exec("CREATE TABLE IF NOT EXISTS images (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    username       VARCHAR(50) NOT NULL,
    filename       VARCHAR(255) NOT NULL,
    title          VARCHAR(255),
    capture_date   DATE,
    uploaded_date  DATETIME,
    comment        TEXT
)");

// 2) Feltöltés kezelése
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['image']['tmp_name'])) {
    $title    = trim($_POST['title']);
    $comment  = trim($_POST['comment']);

    // Érvényes kiterjesztés?
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif'], true)) {
        $error = 'Csak JPG, PNG vagy GIF képet tölthetsz fel!';
    } else {
        // 3) EXIF dátum kinyerése (csak JPG‑hez)
        $capture_date = null;
        if (in_array($ext, ['jpg','jpeg'], true) && function_exists('exif_read_data')) {
            $exif = @exif_read_data($_FILES['image']['tmp_name']);
            if (!empty($exif['DateTimeOriginal'])) {
                $capture_date = date('Y-m-d', strtotime($exif['DateTimeOriginal']));
            } elseif (!empty($exif['DateTime'])) {
                $capture_date = date('Y-m-d', strtotime($exif['DateTime']));
            }
        }

        // 4) Fájl mentése
        $userDir  = getUserImageDir();
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/','_', $_FILES['image']['name']);
        $dest     = $userDir . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            // 5) Adatok mentése az adatbázisba
            $stmt = $db->prepare("
              INSERT INTO images (username, filename, title, capture_date, uploaded_date, comment)
              VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
              $username,
              $filename,
              $title,
              $capture_date,
              date('Y-m-d H:i:s'),
              $comment
            ]);
            // 6) Visszairányítás a galériára
            header('Location: index.php?page=gallery');
            exit;
        } else {
            $error = 'Hiba a fájl feltöltésekor.';
        }
    }
}
?>

<div class="container">
  <h2>Kép feltöltése</h2>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" id="uploadForm">
    <div class="mb-3">
      <label class="form-label">Kép címe</label>
      <input type="text" name="title" class="form-control" placeholder="Adj címet a képnek">
    </div>

    <div class="mb-3">
      <label class="form-label">Kép kiválasztása</label>
      <input type="file" name="image" accept="image/*" class="form-control" id="imageInput" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Előnézet</label><br>
      <img id="preview" style="max-width:200px; display:none; border:1px solid #ccc; padding:5px;">
    </div>

    <div class="mb-3">
      <label class="form-label">Megjegyzés</label>
      <textarea name="comment" class="form-control" rows="3" placeholder="Írj megjegyzést"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Feltöltés</button>
  </form>
</div>

<script>
// Client-side kép előnézet
document.getElementById('imageInput').addEventListener('change', function(){
  const file = this.files[0];
  if (!file) return;
  const preview = document.getElementById('preview');
  const reader = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(file);
});
</script>
