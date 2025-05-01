<?php
// templates/messages.php

if (empty($_SESSION['user'])) {
    redirect('login');
}

$db = getUserDb();
$username = $_SESSION['user']['username'];

$stmt = $db->prepare("
  SELECT name, email, message, created_at 
  FROM messages 
  WHERE username = ? 
  ORDER BY created_at DESC
");
$stmt->execute([$username]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
  <h2>Bejövő üzenetek</h2>

  <?php if (empty($messages)): ?>
    <p>Nincs bejövő üzeneted.</p>
  <?php else: ?>
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>Küldő</th>
          <th>E-mail</th>
          <th>Üzenet</th>
          <th>Érkezett</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($messages as $m): ?>
        <tr>
          <td><?= htmlspecialchars($m['name'] ?: 'Vendég') ?></td>
          <td><?= htmlspecialchars($m['email']) ?></td>
          <td><?= nl2br(htmlspecialchars($m['message'])) ?></td>
          <td><?= htmlspecialchars($m['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
