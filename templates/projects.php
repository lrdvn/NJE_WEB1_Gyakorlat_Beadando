<?php
// templates/projects.php

// 1) Helper: accents‑insensitive összehasonlítás
function removeAccents(string $s): string {
    static $map = [
        'á'=>'a','Á'=>'A','é'=>'e','É'=>'E','í'=>'i','Í'=>'I',
        'ó'=>'o','Ó'=>'O','ö'=>'o','Ö'=>'O','ő'=>'o','Ő'=>'O',
        'ú'=>'u','Ú'=>'U','ü'=>'u','Ü'=>'U','ű'=>'u','Ű'=>'U'
    ];
    return strtr($s, $map);
}

$db = getUserDb();
$username = $_SESSION['user']['username'];

// 2) POST műveletek: státuszfrissítés és törlés
if ($_SERVER['REQUEST_METHOD']==='POST') {
    // Státusz frissítése
    if (($_POST['action'] ?? '')==='update_status') {
        $id     = intval($_POST['id']);
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['planned','active','completed'], true)) {
            $db->prepare("UPDATE projects SET status = ? WHERE id = ? AND username = ?")
               ->execute([$status, $id, $username]);
        }
    }
    // Törlés
    if (($_POST['action'] ?? '')==='delete_project') {
        $id = intval($_POST['id']);
        // Előbb a kapcsolódó feladatok törlése
        $db->prepare("DELETE FROM tasks WHERE project_id = ? AND username = ?")
           ->execute([$id, $username]);
        // Majd a projekt
        $db->prepare("DELETE FROM projects WHERE id = ? AND username = ?")
           ->execute([$id, $username]);
    }
    // Ugrás vissza kereséssel, rendezéssel
    $params = [
      'page=projects',
      'sort=' . urlencode($_GET['sort']  ?? 'id'),
      'order='. urlencode($_GET['order'] ?? 'asc'),
    ];
    if (!empty($_GET['q'])) $params[] = 'q='.urlencode($_GET['q']);
    header('Location: index.php?' . implode('&',$params));
    exit;
}

// 3) GET paraméterek
$validSorts  = ['id','name','budget','status'];
$validOrders = ['asc','desc'];
$sort  = (isset($_GET['sort'])  && in_array($_GET['sort'],$validSorts,true))  ? $_GET['sort']  : 'id';
$order = (isset($_GET['order']) && in_array($_GET['order'],$validOrders,true)) ? $_GET['order'] : 'asc';
$search= trim($_GET['q'] ?? '');

// 4) Adatok lekérése és feldolgozása
$stmt = $db->prepare("SELECT id,name,budget,status FROM projects WHERE username = ?");
$stmt->execute([$username]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Rendezés
usort($rows, function($a,$b) use($sort,$order){
    if($sort==='name'){
        $cmp = strcasecmp(removeAccents($a['name']), removeAccents($b['name']));
    } elseif($sort==='budget'){
        $cmp = $a['budget'] <=> $b['budget'];
    } elseif($sort==='status'){
        $cmp = strcasecmp(removeAccents($a['status']), removeAccents($b['status']));
    } else {
        $cmp = $a['id'] <=> $b['id'];
    }
    return $order==='asc' ? $cmp : -$cmp;
});

// Szűrés keresővel
if($search!==''){
    $rows = array_filter($rows, function($r) use($search){
        return mb_stripos(removeAccents($r['name']), removeAccents($search))!==false;
    });
}
?>
<div class="container">
  <h2>Projektek listája</h2>

  <!-- Keresés -->
  <form method="get" class="mb-3">
    <input type="hidden" name="page" value="projects">
    <input type="hidden" name="sort"  value="<?=htmlspecialchars($sort)?>">
    <input type="hidden" name="order" value="<?=htmlspecialchars($order)?>">
    <div class="input-group" style="width:300px;">
      <input type="text" name="q" value="<?=htmlspecialchars($search)?>"
             class="form-control" placeholder="Keress projekt nevén">
      <button class="btn btn-outline-secondary">Keresés</button>
    </div>
  </form>

  <table class="table table-striped">
    <thead>
      <tr>
        <?php
        $cols=['id'=>'#','name'=>'Név','budget'=>'Költség (Ft)','status'=>'Státusz'];
        foreach($cols as $col=>$label){
          $next = ($sort===$col && $order==='asc')?'desc':'asc';
          $arrow= ($sort===$col)?($order==='asc'?' ▲':' ▼'):'';
          $url  = "?page=projects&sort={$col}&order={$next}"
                 .($search? '&q='.urlencode($search):'');
          echo "<th><a href=\"{$url}\">{$label}{$arrow}</a></th>";
        }
        ?>
        <th>Művelet</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($rows)): ?>
        <tr><td colspan="5">Nincsenek projektek.</td></tr>
      <?php else: foreach($rows as $r): ?>
        <tr>
          <td><?=$r['id']?></td>
          <td><?=htmlspecialchars($r['name'])?></td>
          <td><?=formatCurrency($r['budget'])?></td>
          <td>
            <form method="post" style="display:inline">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="id"     value="<?=$r['id']?>">
              <select name="status" onchange="this.form.submit()">
                <option value="planned"   <?=$r['status']=='planned'   ?'selected':''?>>Tervezett</option>
                <option value="active"    <?=$r['status']=='active'    ?'selected':''?>>Futó</option>
                <option value="completed" <?=$r['status']=='completed'?'selected':''?>>Befejezett</option>
              </select>
            </form>
          </td>
          <td>
            <form method="post" style="display:inline" 
                  onsubmit="return confirm('Tényleg törli a projektet?');">
              <input type="hidden" name="action" value="delete_project">
              <input type="hidden" name="id"     value="<?=$r['id']?>">
              <button class="btn btn-sm btn-danger">Törlés</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
