<?php
// templates/add_project.php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name']);
    $budget = floatval($_POST['budget']);
    $status = $_POST['status'];
    $username = $_SESSION['user']['username'];

    if ($name === '' || !in_array($status, ['planned','active','completed'], true)) {
        $error = 'Kérlek, tölts ki minden mezőt helyesen!';
    } else {
        $db = getUserDb();
        $stmt = $db->prepare("INSERT INTO projects(username, name, budget, status) VALUES(?,?,?,?)");
        $stmt->execute([$username, $name, $budget, $status]);
        redirect('projects');
    }
}
?>
<div class="container">
  <h2>Új projekt hozzáadása</h2>
  <?php if ($error): ?>
    <p class="alert alert-danger"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Projekt neve</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Költség (Ft)</label>
      <input type="number" name="budget" class="form-control" step="0.01" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Státusz</label>
      <select name="status" class="form-select" required>
        <option value="planned">Tervezett</option>
        <option value="active">Futó</option>
        <option value="completed">Befejezett</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Hozzáadás</button>
  </form>
</div>
