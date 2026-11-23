<?php
/**
 * Pedidos del cliente (layout con menú lateral).
 * Lo llama PortalClienteController::pedidos() con $this->render(...).
 * Pestañas: Por entregar | Entregados. Cada pedido abre su detalle con el botón "Detalles".
 */
?>
<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap">
    <link rel="stylesheet" href="<?= base_url('css/panel/v_portal_cliente.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('encabezado') ?>
    <?= $this->include('panel/client/v_encabezado_cliente') ?>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="fv-pagina cli-pagina">

    <div class="cli-tabs" role="tablist" aria-label="Estado de mis pedidos">
        <button type="button" class="cli-tab" role="tab" data-panel="panelPorEntregar" data-hash="por-entregar">Por entregar</button>
        <button type="button" class="cli-tab" role="tab" data-panel="panelEntregados"  data-hash="entregados">Entregados</button>
    </div>

    <?php if ($sinCuenta): ?>
        <div class="cli-vacio">Tu cuenta aún no está unida a un cliente. Contacta a la empresa para que la vinculen.</div>
    <?php endif; ?>

    <?php
    $paneles = [
        'panelPorEntregar' => ['lista' => $porEntregar, 'vacio' => 'No tienes pedidos por entregar.'],
        'panelEntregados'  => ['lista' => $entregados,  'vacio' => 'Todavía no tienes pedidos entregados.'],
    ];
    ?>

    <?php foreach ($paneles as $idPanel => $panel): ?>
    <section id="<?= $idPanel ?>" class="cli-panel" role="tabpanel" hidden>
        <?php if (! $panel['lista'] && ! $sinCuenta): ?>
            <div class="cli-vacio"><?= esc($panel['vacio']) ?></div>
        <?php endif; ?>

        <?php foreach ($panel['lista'] as $pedido): ?>
        <article class="cli-card">
            <header class="cli-card__cab">
                <h2 class="cli-card__titulo">
                    Pedido #<?= (int) $pedido['id_pedido'] ?>
                    <span class="cli-card__fecha"><?= esc(fecha_corta($pedido['fecha'])) ?></span>
                </h2>
                <button type="button" class="cli-detalles btnVerDetalle" data-id="<?= (int) $pedido['id_pedido'] ?>">
                    Detalles <i class="fa-regular fa-eye"></i>
                </button>
            </header>

            <div class="cli-productos">
                <div class="cli-productos__cab">
                    <strong>Productos:</strong>
                    <span class="cli-total">Total <?= dinero($pedido['total']) ?></span>
                </div>
                <ul class="cli-productos__lista">
                    <?php foreach ($pedido['productos'] as $pr): ?>
                    <li>
                        <span><?= esc($pr['nombre']) ?> - <?= esc(cantidad_texto($pr['cantidad'])) ?> <?= esc($pr['unidad_venta']) ?></span>
                        <span class="cli-precio"><?= dinero($pr['total']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <span class="cli-estado"><?= esc(estado_pedido_texto($pedido['status'])) ?></span>
        </article>
        <?php endforeach; ?>
    </section>
    <?php endforeach; ?>

    <?= $this->include('panel/client/v_detalle_cliente') ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url('js/panel/v_portal_cliente.js') ?>"></script>
<?= $this->endSection() ?>
