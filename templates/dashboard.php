<?php
// templates/dashboard.php
$db = getUserDb();
$username = $_SESSION['user']['username'];

// 1) Statisztika: projektek és feladatok státusz szerint
$projStats = $db->prepare("
  SELECT status, COUNT(*) AS cnt 
  FROM projects 
  WHERE username = ?
  GROUP BY status
");
$projStats->execute([$username]);
$projStats = $projStats->fetchAll(PDO::FETCH_KEY_PAIR);

$taskStats = $db->prepare("
  SELECT status, COUNT(*) AS cnt 
  FROM tasks 
  WHERE username = ?
  GROUP BY status
");
$taskStats->execute([$username]);
$taskStats = $taskStats->fetchAll(PDO::FETCH_KEY_PAIR);

// 2) Összes projektek, összes feladatok
$totalProjects = array_sum($projStats);
$totalTasks    = array_sum($taskStats);

// 3) Pénzügyi mutatók a projektekhez
$totalBudget = $db->prepare("SELECT SUM(budget) FROM projects WHERE username = ?");
$totalBudget->execute([$username]);
$totalBudget = $totalBudget->fetchColumn();
$totalBudget = $totalBudget !== null ? (float)$totalBudget : 0.0;

$avgBudget   = $totalProjects ? $totalBudget / $totalProjects : 0;

$maxBudget = $db->prepare("SELECT MAX(budget) FROM projects WHERE username = ?");
$maxBudget->execute([$username]);
$maxBudget = $maxBudget->fetchColumn();

$minBudget = $db->prepare("SELECT MIN(budget) FROM projects WHERE username = ?");
$minBudget->execute([$username]);
$minBudget = $minBudget->fetchColumn();

// 4) Feladat‑mutatók: késedelmes, kész, függőben
$doneTasks    = $taskStats['done']    ?? 0;
$pendingTasks = $taskStats['pending'] ?? 0;
$overdueTasks = $db->prepare("
  SELECT COUNT(*) FROM tasks 
  WHERE username = :username AND due_date < :today 
    AND status != 'done'
");
$overdueTasks->execute([
  ':username' => $username,
  ':today'    => date('Y-m-d')
]);

$overdueTasks->execute([$username, ':today' => date('Y-m-d')]);
$overdueCount = $overdueTasks->fetchColumn();

// százalékok
$donePct    = $totalTasks ? round($doneTasks    / $totalTasks * 100, 1) : 0;
$pendingPct = $totalTasks ? round($pendingTasks / $totalTasks * 100, 1) : 0;
$overduePct = $totalTasks ? round($overdueCount  / $totalTasks * 100, 1) : 0;

// 5) Adatok grafikonhoz
$projLabels = array_keys($projStats);
$projCounts = array_values($projStats);
$taskLabels = array_keys($taskStats);
$taskCounts = array_values($taskStats);
?>

<div class="container">
  <h2>Áttekintés – Statisztikák</h2>

  <div style="width:45%; display:inline-block; vertical-align:top;">
    <h4>Projektek állapot szerinti megoszlása</h4>
    <canvas id="projChart"></canvas>
  </div>
  <div style="width:45%; display:inline-block; vertical-align:top;">
    <h4>Feladatok száma státuszonként</h4>
    <canvas id="taskChart"></canvas>
  </div>

  <!-- Táblázatos bontások -->
  <div class="mt-4">
    <h3>Projektek összesítve</h3>
    <p><strong>Összes projekt:</strong> <?= $totalProjects ?></p>
    <table class="table table-sm table-bordered">
      <thead><tr><th>Státusz</th><th>Darab</th></tr></thead>
      <tbody>
        <?php foreach ($projStats as $st => $cnt): ?>
        <tr>
          <td><?= htmlspecialchars(ucfirst($st)) ?></td>
          <td><?= $cnt ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    <h3>Feladatok összesítve</h3>
    <p><strong>Összes feladat:</strong> <?= $totalTasks ?></p>
    <table class="table table-sm table-bordered">
      <thead><tr><th>Státusz</th><th>Darab</th></tr></thead>
      <tbody>
        <?php foreach ($taskStats as $st => $cnt): ?>
        <tr>
          <td><?= htmlspecialchars(ucfirst($st)) ?></td>
          <td><?= $cnt ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Új mutatók -->
  <div class="mt-4">
    <h3>Pénzügyi mutatók projektekhez</h3>
    <table class="table table-sm table-bordered" style="width:50%;">
      <tbody>
        <tr>
          <th>Összes költség</th>
          <td><?= formatCurrency($totalBudget) ?></td>
        </tr>
        <tr>
          <th>Átlagos költség</th>
          <td><?= formatCurrency($avgBudget) ?></td>
        </tr>
        <tr>
          <th>Max. költség</th>
          <td><?= formatCurrency($maxBudget) ?></td>
        </tr>
        <tr>
          <th>Min. költség</th>
          <td><?= formatCurrency($minBudget) ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="mt-4 mb-5">
    <h3>Feladat‑mutatók</h3>
    <table class="table table-sm table-bordered" style="width:50%;">
      <tbody>
        <tr>
          <th>Kész feladatok</th>
          <td><?= $doneTasks ?> (<?= $donePct ?> %)</td>
        </tr>
        <tr>
          <th>Függőben lévő feladatok</th>
          <td><?= $pendingTasks ?> (<?= $pendingPct ?> %)</td>
        </tr>
        <tr>
          <th>Késedelmes feladatok</th>
          <td><?= $overdueCount ?> (<?= $overduePct ?> %)</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(
  document.getElementById('projChart').getContext('2d'),
  {
    type: 'pie',
    data: {
      labels: <?= json_encode($projLabels) ?>,
      datasets: [{ data: <?= json_encode($projCounts) ?>, backgroundColor: ['#FF6384','#36A2EB','#FFCE56'] }]
    }
  }
);
new Chart(
  document.getElementById('taskChart').getContext('2d'),
  {
    type: 'bar',
    data: {
      labels: <?= json_encode($taskLabels) ?>,
      datasets: [{
        label: 'Feladatok',
        data: <?= json_encode($taskCounts) ?>,
      }]
    },
    options: { scales: { y: { beginAtZero: true } } }
  }
);
</script>
