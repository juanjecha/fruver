<?php
/**
 * Crédito del cliente (layout con menú lateral).
 * Lo llama PortalClienteController::credito() con $this->render(...).
 * Pestañas: Deudas (con la suma de lo adeudado) | Pagados.
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

    <div class="cli-tabs" role="tablist" aria-label="Estado de mi crédito">
        <button type="button" class="cli-tab" role="tab" data-panel="panelDeudas"  data-hash="deudas">Deudas</button>
        <button type="button" class="cli-tab" role="tab" data-panel="panelPagados" data-hash="pagados">Pagados</button>
    </div>

    <?php if ($sinCuenta): ?>
        <div class="cli-vacio">Tu cuenta aún no está unida a un cliente. Contacta a la empresa para que la vinculen.</div>
    <?php endif; ?>

    <?php
    $paneles = [
        'panelDeudas' => [
            'titulo' => 'Pedidos a crédito no liquidados:',
            'lista'  => $deudas,
            'total'  => $totalDeuda,
            'vacio'  => 'No tienes adeudos pendientes.',
        ],
        'panelPagados' => [
            'titulo' => 'Pedidos a crédito liquidados:',
            'lista'  => $pagados,
            'total'  => null,
            'vacio'  => 'Todavía no has liquidado pedidos a crédito.',
        ],
    ];
    ?>

    <?php foreach ($paneles as $idPanel => $panel): ?>
    <section id="<?= $idPanel ?>" class="cli-panel" role="tabpanel" hidden>
        <div class="cli-card">
            <header class="cli-card__cab">
                <h2 class="cli-card__titulo"><?= esc($panel['titulo']) ?></h2>
                <?php if ($panel['total'] !== null): ?>
                    <span class="cli-total cli-total--grande">Total: <?= dinero($panel['total']) ?></span>
                <?php endif; ?>
            </header>

            <?php if (! $panel['lista']): ?>
                <div class="cli-vacio cli-vacio--interno"><?= esc($panel['vacio']) ?></div>
            <?php endif; ?>

            <?php foreach ($panel['lista'] as $pedido): ?>
            <div class="cli-fila">
                <div class="cli-fila__info">
                    <span class="cli-fila__pedido">Pedido #<?= (int) $pedido['id_pedido'] ?></span>
                    <span class="cli-fila__total">Total: <?= dinero($pedido['total']) ?></span>
                </div>
                <div class="cli-fila__lado">
                    <span class="cli-fila__fecha"><?= esc(fecha_corta($pedido['fecha'])) ?></span>
                    <?php if ($idPanel === 'panelPagados' && $pedido['fecha_pagado']): ?>
                        <span class="cli-fila__fecha">Liquidado: <?= esc(fecha_corta($pedido['fecha_pagado'])) ?></span>
                    <?php endif; ?>
                    <button type="button" class="cli-detalles btnVerDetalle" data-id="<?= (int) $pedido['id_pedido'] ?>">
                        Detalles <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>

    <?= $this->include('panel/client/v_detalle_cliente') ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url('js/panel/v_portal_cliente.js') ?>"></script>
<?= $this->endSection() ?>
