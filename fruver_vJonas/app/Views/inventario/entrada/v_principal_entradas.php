<?php
/**
 * Entradas (Inventario) con layout y menú lateral, igual que Productos.
 * Lo llama EntradaController::entrada() con:
 *     $this->render('inventario/entrada/v_principal_entradas', $data, 'inventario');
 */
?>
<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/comun/v_tabla_datos.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_paginacion.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/entrada/v_principal_entradas.css') ?>?v=2">
<?= $this->endSection() ?>


<?= $this->section('contenido') ?>
<div class="fv-pagina">

    <div class="fv-encabezado">
        <div>
            <h3 id="titulo">Gestión de Entradas</h3>
            <small>Administra las entradas de producto</small>
        </div>
        <button class="btn btn-success"
            onclick="abrirModal('<?= base_url('alta_entrada') ?>','Nueva entrada')">
            + Nueva entrada
        </button>
    </div>

    <!-- BUSCADOR -->
    <div class="card mb-3 p-3"
        id="buscador-card"
        data-url-tabla="<?= esc($url_tabla) ?>">

        <label for="buscar"><b><i class="fa fa-search"></i> Búsqueda de Entradas</b></label>
        <input type="text" id="buscar" class="form-control mt-2"
            value="<?= esc($filtros['q']) ?>"
            placeholder="Buscar por producto o fecha de entrada (AAAA-MM-DD)...">
    </div>

    <!-- LISTADO -->
    <div class="card p-3" id="tablaEntradas">
        <?= $this->include('inventario/entrada/v_tabla_entradas') ?>
    </div>

</div>

<!-- MODAL (alta, edición y repetir) -->
<div class="modal fade" id="miModal">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva entrada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeContenido" style="width:100%; height:400px; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA HISTORIAL DE MERMAS -->
<div class="modal fade" id="modalMermas">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloMermas">Historial de Mermas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="cuerpoMermas"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    const BASE_URL  = "<?= base_url() ?>";
    const URL_TABLA = "<?= esc($url_tabla) ?>";
</script>
<script src="<?= base_url('js/entrada/v_principal_entradas.js') ?>?v=2"></script>
<?= $this->endSection() ?>
