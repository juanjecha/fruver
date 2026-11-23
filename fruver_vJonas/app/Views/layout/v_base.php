<?php
/**
 * Layout único del sistema. Las vistas lo usan así:
 *
 *   <?= $this->extend('layout/v_base') ?>
 *   <?= $this->section('estilos') ?>   ...<link> propios (opcional)...   <?= $this->endSection() ?>
 *   <?= $this->section('contenido') ?> ...HTML de la pantalla...         <?= $this->endSection() ?>
 *   <?= $this->section('scripts') ?>   ...<script> propios (opcional)... <?= $this->endSection() ?>
 *
 * $menu (lo arma BaseController::render) trae el menú lateral; si es null (pantalla de inicio) no hay menú.
 */
$menu = $menu ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo ?? 'FRUVER') ?></title>

    <!-- Aplica el estado guardado del menú ANTES de pintar (evita parpadeo). En teléfono arranca cerrado. -->
    <script>
        (function () {
            var raiz = document.documentElement;
            try {
                var movil = window.matchMedia('(max-width: 767.98px)').matches;
                var guardado = localStorage.getItem('fv_menu');
                raiz.classList.toggle('fv-menu-abierto', !movil && guardado !== 'cerrado');
            } catch (e) {
                raiz.classList.add('fv-menu-abierto');
            }
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= base_url('css/layout/base.css') ?>">
    <?= $this->renderSection('estilos') ?>
</head>
<body>
<?= $this->renderSection('encabezado') ?>
<div class="fv-app">

    <?php if (! empty($menu)): ?>
        <?= $this->include('layout/v_menu_lateral') ?>
        <div class="fv-fondo-movil" id="fvMenuFondo"></div>
    <?php endif; ?>

    <main class="fv-contenido">
        <?php if (! empty($menu)): ?>
            <button type="button" class="fv-abrir-movil" id="fvAbrirMovil" aria-label="Abrir el menú">
                <i class="fa-solid fa-bars"></i>
            </button>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-warning py-2"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?= $this->renderSection('contenido') ?>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>window.FV_BASE_URL = "<?= base_url() ?>";</script>
<script src="<?= base_url('js/layout/sesion.js') ?>"></script>
<script src="<?= base_url('js/layout/menu.js') ?>"></script>
<script src="<?= base_url('js/layout/menu.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
