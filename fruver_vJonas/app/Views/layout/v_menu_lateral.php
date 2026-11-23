<?php
/** Menú lateral. Recibe $menu = ['titulo' => ..., 'items' => [[etiqueta, icono, url, activo], ...]] */
$panel = config('Auth')->panelPorRol[session('rol')] ?? 'login';
?>
<aside class="fv-menu" id="fvMenu" aria-label="Menú de accesos">

    <div class="fv-menu__cabecera">
        <span class="fv-menu__titulo fv-texto">Accesos</span>
        <button type="button" id="fvMenuToggle" class="fv-menu__toggle"
                aria-controls="fvMenu" aria-expanded="true" aria-label="Abrir o cerrar el menú">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <nav class="fv-menu__lista">
        <?php foreach ($menu['items'] as $item): ?>
            <a class="fv-menu__enlace<?= $item['activo'] ? ' activo' : '' ?>"
               href="<?= esc($item['url'], 'attr') ?>"
               title="<?= esc($item['etiqueta'], 'attr') ?>"
               <?= $item['activo'] ? 'aria-current="page"' : '' ?>>
                <i class="fa-solid <?= esc($item['icono'], 'attr') ?>"></i>
                <span class="fv-texto"><?= esc($item['etiqueta']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="fv-menu__pie">
        <a class="fv-menu__enlace" href="<?= base_url($panel) ?>" title="Menú principal">
            <i class="fa-solid fa-house"></i>
            <span class="fv-texto">Menú principal</span>
        </a>
        <form action="<?= base_url('logout') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="fv-menu__enlace" title="Cerrar sesión">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="fv-texto">Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>
