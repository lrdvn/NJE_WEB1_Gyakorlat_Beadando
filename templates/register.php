<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $fullname = trim($_POST['fullname']);
    $username = preg_replace('/[^a-zA-Z0-9_]/', '', trim($_POST['username']));
    $email    = trim($_POST['email']);
    $pass     = $_POST['password'];

    // === VALIDÁLÁS ===
    if (empty($fullname)) {
        $errors[] = "A teljes név megadása kötelező!";
    } elseif (strlen($fullname) > 50) {
        $errors[] = "A teljes név legfeljebb 50 karakter lehet!";
    } elseif (strlen($fullname) < 5) {
        $errors[] = "A teljes név minimum 5 karakter legyen!";
    }
    if (empty($username)) {
        $errors[] = "A felhasználónév megadása kötelező!";
    } elseif (strlen($username) > 50) {
        $errors[] = "A felhasználónév legfeljebb 50 karakter lehet!";
    } elseif (strlen($username) < 5) {
        $errors[] = "A felhasználónév minimum 5 karakter legyen!";
    }
    if (empty($email)) {
        $errors[] = "Az e-mail cím megadása kötelező!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Érvénytelen e-mail cím formátum!";
    }
    if (empty($pass)) {
        $errors[] = "A jelszó megadása kötelező!";
    } else {
        if (strlen($pass) < 8) {
            $errors[] = "A jelszónak legalább 8 karakter hosszúnak kell lennie!";
        }
        if (!preg_match('/[A-Z]/', $pass)) {
            $errors[] = "A jelszónak tartalmaznia kell legalább egy nagybetűt!";
        }
        if (!preg_match('/[a-z]/', $pass)) {
            $errors[] = "A jelszónak tartalmaznia kell legalább egy kisbetűt!";
        }
        if (!preg_match('/[0-9]/', $pass)) {
            $errors[] = "A jelszónak tartalmaznia kell legalább egy számot!";
        }
    }

    // === Ha NINCS hiba, mehet a TE eredeti kódod ===
    if (empty($errors)) {
        $db = getAuthDb();
        try {
            $stmt = $db->prepare("INSERT INTO users(fullname,username,email,password) VALUES(?,?,?,?)");
            $stmt->execute([$fullname, $username, $email, $pass]);
        } catch (PDOException $e) {
            echo "<p class='alert alert-danger'>Felhasználónév vagy email már létezik.</p>";
            return;
        }

        mkdir(__DIR__ . "/../uploads/images/{$username}", 0755, true);
        mkdir(__DIR__ . "/../uploads/documents/{$username}", 0755, true);

        echo "<p class='alert alert-success'>Sikeres regisztráció! Jelentkezz be.</p>";
    } else {
        // Hibák kiírása
        echo '<div class="alert alert-danger"><ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul></div>';
    }
}
?>

<div class="container">
    <h2>Regisztráció</h2>
    <form method="POST" class="row g-3" novalidate>
        <div class="col-12">
            <label for="fullname" class="form-label">Teljes név</label>
            <input type="text" id="fullname" name="fullname" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label for="username" class="form-label">Felhasználónév</label>
            <input type="text" id="username" name="username" class="form-control" autocomplete="off" required>
        </div>
        <div class="col-md-4">
            <label for="email" class="form-label">Email cím</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label for="password" class="form-label">Jelszó</label>
            
            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required>
        </div>
	<div style="color: red; font-size: 0.9em;">
                A jelszónak minimum 8 karakter hosszúnak kell lennie, és tartalmaznia kell legalább egy nagybetűt, egy kisbetűt és egy számot!
            </div>
        <div class="col-12">
            <button type="submit" class="btn btn-success">Regisztrálok</button>
        </div>
    </form>
</div>
