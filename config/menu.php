<?php
// config/menu.php

$menu = [
    'home'        => 'Fogadó oldal',
    'dashboard'   => 'Áttekintés',
    'projects'    => 'Projektek',
    'add_project' => 'Új projekt',
    'tasks'       => 'Feladatok',
    'add_task'    => 'Új feladat',
    'gallery'     => 'Munkaképek',
    'upload'      => 'Kép feltöltése',
    'documents'   => 'Dokumentumok',
];

if (empty($_SESSION['user'])) {
    $menu['contact']   = 'Kapcsolat';
    $menu['login']     = 'Belépés';
    $menu['register']  = 'Regisztráció';
} else {
    $menu['messages']  = 'Üzenetek';
    $menu['logout']    = 'Kilépés';
}
