<?php
// templates/contact.php

if (!empty($_SESSION['user'])) {
    redirect('dashboard'); // Csak vendégeknek
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getAuthDb();

    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $message = trim($_POST['message'] ?? '');

    $username = $_POST['username'] ?? '';
    if ($username === '') {
        $error = 'Hiányzó címzett felhasználónév!';
    } elseif ($message === '' || $email === '') {
        $error = 'Minden mező kitöltése kötelező!';
    } elseif (!preg_match('/^[^@]+@[^@]+\.[^@]+$/', $email)) {
        $error = 'Hibás email cím!';
    } else {
        try {
            $stmt = $db->prepare("
              INSERT INTO messages (username, name, email, message, read_flag, created_at)
              VALUES (?, ?, ?, ?, 0, ?)
            ");
            $stmt->execute([
              $username,
              $name !== '' ? $name : 'Vendég',
              $email,
              $message,
              date('Y-m-d H:i:s')
            ]);
            $success = 'Üzenet elküldve!';
        } catch (PDOException $e) {
            $error = 'Hiba az adatbázis művelet során.';
        }
    }
}
?>

<div class="container">
  <h2>Kapcsolat – Üzenetküldés</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php elseif ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST" id="contactForm" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Neved (nem kötelező)</label>
      <input name="name" class="form-control">
    </div>
    <div class="col-md-6">
      <label class="form-label">E-mail címed</label>
      <input name="email" class="form-control">
    </div>
    <div class="col-md-6">
      <label class="form-label">Címzett felhasználónév</label>
      <input name="username" class="form-control">
    </div>
    <div class="col-12">
      <label class="form-label">Üzeneted</label>
      <textarea name="message" rows="5" class="form-control"></textarea>
    </div>
    <div class="col-12">
      <button class="btn btn-primary">Küldés</button>
    </div>
  </form>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
  let error = '';

  const email = this.email.value.trim();
  const message = this.message.value.trim();
  const username = this.username.value.trim();

  if (!email || !message || !username) {
    error = 'Minden kötelező mezőt ki kell tölteni!';
  } else if (!/^[^@]+@[^@]+\.[^@]+$/.test(email)) {
    error = 'Hibás email formátum!';
  }

  if (error) {
    e.preventDefault();
    alert(error);
  }
});
</script>
