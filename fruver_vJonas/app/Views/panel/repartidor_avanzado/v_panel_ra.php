<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER - Panel de repartidor avanzado</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Nunito:wght@900&display=swap">
<link rel="stylesheet" href="<?= base_url('css/panel/v_panel_admin.css') ?>?v=2">

</head>
<body class="panel-admin">

<!-- ───────── Saludo y cerrar sesión ───────── -->
<header class="barra-sesion">
    <span><i class="fa-solid fa-circle-user"></i> Hola, <strong><?= esc($nombre) ?></strong></span>

    <!-- Cerrar sesión por POST + CSRF -->
    <form action="<?= base_url('logout') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="btn-salir">
            <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
        </button>
    </form>
</header>

<!-- ───────── Parte de arriba: foto, logo y título ───────── -->
<section class="hero">
    <img class="hero-logo" src="<?= base_url('img/auth/logo-fruver.png') ?>" alt="Logo de Fruver">
    <h1 class="hero-titulo">
        Gestor de ventas de frutas y verduras.<br>
        ¿Por dónde quieres empezar?
    </h1>
</section>

<!-- ───────── Parte de abajo: tarjetas de módulos ───────── -->
<main class="zona-menu">

    <?php if (session()->getFlashdata('error')): ?>
        <div class="aviso" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <nav class="tarjetas" aria-label="Módulos del sistema">

        <!-- Pedidos: abre submenú -->
        <div class="tarjeta-grupo">
            <button type="button" class="tarjeta" aria-expanded="false" aria-controls="menu-pedidos">
                <img class="tarjeta-icono" src="<?= base_url('img/panel/icono-pedidos.svg') ?>" alt="">
                <span class="tarjeta-texto">Pedidos</span>
            </button>
            <div class="submenu" id="menu-pedidos" hidden>
                <a href="<?= base_url('control_pedidos') ?>"><i class="fa-solid fa-boxes-packing"></i> Control de pedidos</a>
                <a href="<?= base_url('seguimiento_pedidos') ?>"><i class="fa-solid fa-truck-fast"></i> Seguimiento de pedidos</a>
            </div>
        </div>

        <!-- Entregas: abre submenú -->
        <div class="tarjeta-grupo">
            <button type="button" class="tarjeta" aria-expanded="false" aria-controls="menu-entregas">
                <img class="tarjeta-icono" src="<?= base_url('img/panel/icono-pedidos.svg') ?>" alt="">
                <span class="tarjeta-texto">Entregas</span>
            </button>
            <div class="submenu" id="menu-entregas" hidden>
                <a href="<?= base_url('gestion_entregas') ?>"><i class="fa-solid fa-truck"></i> Gestión de entregas</a>
            </div>
        </div>

    </nav>
</main>

<script src="<?= base_url('js/panel/v_panel_admin.js') ?>"></script>
</body>
</html>