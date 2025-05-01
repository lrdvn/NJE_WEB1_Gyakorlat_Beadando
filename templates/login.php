<?php
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $db   = getAuthDb();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // if ($user && password_verify($_POST['password'], $user['password'])) 
    if ($user && $_POST['password'] === $user['password']) {
        $_SESSION['user'] = $user;
        redirect('dashboard');
    } else {
        echo "<p class='alert alert-danger'>Hibás felhasználónév vagy jelszó.</p>";
    }
}
?>

<div class="container">
  <h2>Bejelentkezés</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-6">
      <label for="username" class="form-label">Felhasználónév</label>
      <input type="text" class="form-control" id="username" name="username" autocomplete="off" required>
    </div>
    <div class="col-md-6">
      <label for="password" class="form-label">Jelszó</label>
      <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" required>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Belépek</button>
      <p class="mt-4"> <a href="index.php?page=register">Még nincs fiókod? Regisztrálj!</a> </p>
    </div>
  </form>
</div>
