<?php
/**
 * Versión PÁGINA COMPLETA del formulario de pedido (ruta: nuevo_pedido).
 * Se dibuja dentro del layout con menú lateral (layout/v_base), igual que Clientes.
 * Llega aquí desde PedidoController::nuevo_pedido() -> $this->render(...)
 */
?>
<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/pedido/v_nuevo_pedido.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="fv-pagina">

    <div class="mb-3">
        <h3 id="titulo">Nuevo pedido</h3>
        <small>Registra un pedido con sus productos</small>
    </div>

    <div class="card" id="tarjetaPedido">
        <?= $this->include('pedidos/pedido/v_form_pedido') ?>
    </div>

</div>
<?= $this->endSection() ?>

<?php /* El JS va en la sección "scripts" porque v_base carga jQuery AL FINAL del <body>:
         si el script se pusiera dentro de "contenido", $ todavía no existiría. */ ?>
<?= $this->section('scripts') ?>
<script>
<?= $this->include('pedidos/pedido/v_form_pedido_js') ?>
</script>
<?= $this->endSection() ?>
