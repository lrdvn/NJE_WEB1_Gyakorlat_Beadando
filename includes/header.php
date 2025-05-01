<?php
// includes/header.php
//session_start();
$loggedIn = !empty($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Építőipari Projekt Rendszer</title>
  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <!-- Saját stílusok, ha van -->
  <link href="assets/css/custom.css" rel="stylesheet">
</head>
<body class="d-flex flex-column" style="min-height:100vh;">

  <!-- Fejléc -->
  <header class="bg-primary text-white py-1 mb-2">
    <div class="container d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0"><a href="/index.php?page=home" style="text-decoration: none; color: inherit;">Építőipari Projekt Rendszer</a></h1>
      <?php if ($loggedIn): ?>
        <span>Bejelentkezett: <?php echo $_SESSION['user']['fullname']; ?> (<?php echo $_SESSION['user']['username']; ?>)</span>
      <?php endif; ?>
    </div>
  </header>

  <!-- Navigáció -->
  <nav class="bg-dark">
    <div class="container">
      <ul class="nav nav-pills py-2">

        <?php if (! $loggedIn): ?>
          <!-- Vendég menü -->
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='login' ? ' active' : '' ?>"
               href="?page=login">Belépés</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='register' ? ' active' : '' ?>"
               href="?page=register">Regisztráció</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='contact' ? ' active' : '' ?>"
            href="?page=contact">Kapcsolat</a>
          </li>
          
        <?php else: ?>
          <!-- Bejelentkezett menü -->
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='home' ? ' active' : '' ?>"
               href="?page=home">Főoldal</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='dashboard' ? ' active' : '' ?>"
               href="?page=dashboard">Áttekintés</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='projects' ? ' active' : '' ?>"
               href="?page=projects">Projektek</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='add_project' ? ' active' : '' ?>"
               href="?page=add_project">Új projekt</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='tasks' ? ' active' : '' ?>"
               href="?page=tasks">Feladatok</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='add_task' ? ' active' : '' ?>"
               href="?page=add_task">Új feladat</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='gallery' ? ' active' : '' ?>"
               href="?page=gallery">Munkaképek</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='upload' ? ' active' : '' ?>"
               href="?page=upload">Kép feltöltése</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='documents' ? ' active' : '' ?>"
               href="?page=documents">Dokumentumok</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white<?= ($_GET['page'] ?? '')==='messages' ? ' active' : '' ?>"
            href="?page=messages">Üzenetek</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="?page=logout">Kilépés</a>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </nav>

  <!-- Fő tartalom -->
  <main class="flex-fill">
