<?php
// templates/home.php
$loggedIn = !empty($_SESSION['user']);
?>
<div class="container mt-5">

  <!-- Hero szekció -->
  <div class="bg-primary text-white p-3 rounded shadow-sm">
    <h1 class="display-4">Építőipari Projekt Rendszer</h1>
    <p class="lead">
      Hatékony projekt‑ és feladatkezelés az építőipar számára.  
      Könnyen nyomon követheted a munkafolyamatokat, dokumentumokat és statisztikákat valós időben.
    </p>
    <hr class="border-light">
    <p>
      Növeld céged hatékonyságát, tartsd kézben a költségvetést és a határidőket!  
      Kezdj el mindent egy kattintással:
    </p>
    <a href="?page=projects" class="btn btn-light btn-lg me-2">Projektek</a>
    <a href="?page=tasks"    class="btn btn-outline-light btn-lg">Feladatok</a>
  </div>

  <!-- Videók -->
  <div class="row mt-5 gy-4">
   <div class="col-lg-6">
      <h3>YouTube bemutató</h3>
      <div class="ratio ratio-16x9 rounded border">
        <iframe
          src="https://youtube.com/embed/SGyOaCXr8Lw"
          title="YouTube videó"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen>
        </iframe>
      </div>
    </div>
    <div class="col-lg-6">
      <h3>Belső bemutató</h3>
      <video class="w-100 rounded border" controls>
        <source src="uploads/videos/promo.mp4" type="video/mp4">
        A böngésződ nem támogatja a videó tag-et.
      </video>
    </div>
  </div>

  <!-- Bejelentkezés/Regisztráció gombok csak vendégnek -->
  <?php if (!$loggedIn): ?>
  <div class="text-center mt-5">
    <a href="?page=register" class="btn btn-primary btn-lg me-3">Regisztráció</a>
    <a href="?page=login"    class="btn btn-secondary btn-lg">Belépés</a>
  </div>
  <?php else: ?>
  <div class="text-center mt-5">
    <p class="lead">Üdv, <?= htmlspecialchars($_SESSION['user']['username']) ?>!</p>
    <a href="?page=dashboard" class="btn btn-light btn-lg">Áttekintés</a>
  </div>
  <?php endif; ?>
<div style="width: 100%; margin-top: 25px;">
  <h2 style="text-align:center;">Székhelyünk térképen</h2>
  <iframe
    width="100%"
    height="450"
    frameborder="0"
    style="border:0"
    allowfullscreen
    loading="lazy"
    referrerpolicy="no-referrer-when-downgrade"
    src="https://www.google.com/maps?q=46.896252,19.668381&hl=hu&z=17&output=embed">
  </iframe>
</div>
46.896252, 19.668381
</div>
