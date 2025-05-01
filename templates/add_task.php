<?php
// templates/add_task.php
$db = getUserDb();
$username = $_SESSION['user']['username'];

$projects = $db->prepare("SELECT id, name FROM projects WHERE username = ?");
$projects->execute([$username]);
$projects = $projects->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = intval($_POST['project_id']);
    $title      = trim($_POST['title']);
    $due_date   = $_POST['due_date'];
    $status     = $_POST['status'];

    if (!$project_id || $title === '' || !$due_date || !in_array($status, ['pending','done'], true)) {
        $error = 'Kérlek, tölts ki minden mezőt helyesen!';
    } else {
        $stmt = $db->prepare("
          INSERT INTO tasks(username, project_id, title, due_date, status) 
          VALUES(?,?,?,?,?)
        ");
        $stmt->execute([$username, $project_id, $title, $due_date, $status]);
        redirect('tasks');
    }
}
?>
<div class="container">
  <h2>Új feladat hozzáadása</h2>
  <?php if ($error): ?>
    <p class="alert alert-danger"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Projekt</label>
      <select name="project_id" class="form-select" required>
        <option value="">– Válassz projektet –</option>
        <?php foreach ($projects as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Feladat címe</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Határidő</label>
      <input type="date" name="due_date" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Státusz</label>
      <select name="status" class="form-select" required>
        <option value="pending">Függőben</option>
        <option value="done">Kész</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Hozzáadás</button>
  </form>
</div>
