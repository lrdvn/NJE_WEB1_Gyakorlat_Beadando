<?php
// templates/tasks.php

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

// POST: státusz update és törlés
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(($_POST['action']??'')==='update_status'){
        $id=intval($_POST['id']);
        $status=$_POST['status']??'';
        if(in_array($status,['pending','done'],true)){
            $stmt = $db->prepare("UPDATE tasks SET status=? WHERE id=? AND username=?");
            $stmt->execute([$status, $id, $username]);
        }
    }
    if(($_POST['action']??'')==='delete_task'){
        $id=intval($_POST['id']);
        $stmt = $db->prepare("DELETE FROM tasks WHERE id=? AND username=?");
        $stmt->execute([$id, $username]);
    }
    // redirect
    $params=[
      'page=tasks',
      'sort=' .urlencode($_GET['sort']  ?? 'id'),
      'order='.urlencode($_GET['order'] ?? 'asc'),
    ];
    if(!empty($_GET['q'])) $params[]='q='.urlencode($_GET['q']);
    header('Location: index.php?'.implode('&',$params));
    exit;
}

// GET paraméterek
$validSorts  = ['id','project','title','due_date','status'];
$validOrders = ['asc','desc'];
$sort  = (isset($_GET['sort']) && in_array($_GET['sort'],$validSorts,true)) ? $_GET['sort']  : 'id';
$order = (isset($_GET['order'])&& in_array($_GET['order'],$validOrders,true))? $_GET['order'] : 'asc';
$search= trim($_GET['q']??'');

// Adatok
$stmt = $db->prepare("
  SELECT t.id, p.name AS project, t.title, t.due_date, t.status
  FROM projects p 
  JOIN tasks t ON t.project_id = p.id
  WHERE p.username = ? AND t.username = ?
");
$stmt->execute([$username, $username]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Rendezés
usort($rows, function($a,$b)use($sort,$order){
    if($sort==='project'){
        $cmp=strcasecmp(removeAccents($a['project']), removeAccents($b['project']));
    }elseif($sort==='title'){
        $cmp=strcasecmp(removeAccents($a['title']), removeAccents($b['title']));
    }elseif($sort==='due_date'){
        $cmp=strtotime($a['due_date'])<=>strtotime($b['due_date']);
    }elseif($sort==='status'){
        $cmp=strcasecmp(removeAccents($a['status']), removeAccents($b['status']));
    }else{
        $cmp=$a['id']<=>$b['id'];
    }
    return $order==='asc' ? $cmp : -$cmp;
});

// Szűrés
if($search!==''){
    $rows=array_filter($rows, function($r)use($search){
        return mb_stripos(removeAccents($r['project']),removeAccents($search))!==false
            || mb_stripos(removeAccents($r['title']),  removeAccents($search))!==false;
    });
}
?>
<div class="container">
  <h2>Feladatok listája</h2>

  <!-- Keresés -->
  <form method="get" class="mb-3">
    <input type="hidden" name="page"  value="tasks">
    <input type="hidden" name="sort"  value="<?=htmlspecialchars($sort)?>">
    <input type="hidden" name="order" value="<?=htmlspecialchars($order)?>">
    <div class="input-group" style="width:300px;">
      <input type="text" name="q" value="<?=htmlspecialchars($search)?>"
             class="form-control" placeholder="Keress feladatban">
      <button class="btn btn-outline-secondary">Keresés</button>
    </div>
  </form>

  <table class="table table-hover">
    <thead>
      <tr>
        <?php
        $cols=['id'=>'#','project'=>'Projekt','title'=>'Cím','due_date'=>'Határidő','status'=>'Státusz'];
        foreach($cols as $col=>$lab){
          $next=($sort===$col&&$order==='asc')?'desc':'asc';
          $arrow=($sort===$col)?($order==='asc'?' ▲':' ▼'):'';
          $url="?page=tasks&sort={$col}&order={$next}"
              .($search?'&q='.urlencode($search):'');
          echo "<th><a href=\"{$url}\">{$lab}{$arrow}</a></th>";
        }
        ?>
        <th>Művelet</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($rows)): ?>
        <tr><td colspan="6">Nincsenek feladatok.</td></tr>
      <?php else: foreach($rows as $r): ?>
        <tr>
          <td><?=$r['id']?></td>
          <td><?=htmlspecialchars($r['project'])?></td>
          <td><?=htmlspecialchars($r['title'])?></td>
          <td><?=htmlspecialchars($r['due_date'])?></td>
          <td>
            <form method="post" style="display:inline">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="id"     value="<?=$r['id']?>">
              <select name="status" onchange="this.form.submit()">
                <option value="pending" <?=$r['status']=='pending'?'selected':''?>>Függőben</option>
                <option value="done"    <?=$r['status']=='done'   ?'selected':''?>>Kész</option>
              </select>
            </form>
          </td>
          <td>
            <form method="post" style="display:inline"
                  onsubmit="return confirm('Tényleg törli a feladatot?');">
              <input type="hidden" name="action" value="delete_task">
              <input type="hidden" name="id"     value="<?=$r['id']?>">
              <button class="btn btn-sm btn-danger">Törlés</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
