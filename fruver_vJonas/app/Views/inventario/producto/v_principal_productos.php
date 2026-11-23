<?php
/**
 * Productos (Inventario) con layout y menú lateral.
 * Lo llama ProductoController::listar_productos() con:
 *     $this->render('inventario/producto/v_principal_productos', $datos, 'inventario');
 * Secciones: estilos -> <head> | contenido -> <main> | scripts -> final del <body> (después de jQuery y Bootstrap)
 */
?>
<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/producto/v_principal_productos.css') ?>?v=2">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_paginacion.css') ?>">
<?= $this->endSection() ?>


<?= $this->section('contenido') ?>
<div class="fv-pagina">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 id="titulo">Gestión de Productos</h3>
            <small>Administra el catálogo de productos</small>
        </div>
        <button class="btn btn-success"
            onclick="abrirModal('<?= base_url('alta_productos') ?>','Nuevo producto')">
            + Registrar Producto
        </button>
    </div>

    <!-- BUSCADOR -->

    <!-- ESC= seguridad al imprimir datos en la página-->
    <div class="card mb-3 p-3"

        id="buscador-card"
        data-url-tabla="<?= esc($url_tabla) ?>">

        <label><b><i class="fa fa-search"></i> Búsqueda de Productos</b></label>
        <div class="input-group mt-2">
            <input type="text" id="buscar" class="form-control mt-2"
                   value="<?= esc($filtros['q']) ?>"
                   placeholder="Buscar por código, nombre, categoría o descripción...">
            <?php if (!empty($filtros['q'])): ?>
     
            <?php endif; ?>
        </div>
    </div>

    <!-- LISTADO -->
    <div class="card p-3">
        <h5>Lista de Productos (<?= $pager->getTotal() ?>)</h5>

        <div class="table-responsive">
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Código</th>
                    <th>Nombre*</th>
                    <th>Categoría*</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($datos_producto)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-3">
                        Sin resultados para "<strong><?= esc($filtros['q']) ?></strong>"
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach($datos_producto as $producto): ?>
                <tr class="fila-producto">
                    <td class="foto">
                        <?php if (! empty($producto['imagen']) && is_file(FCPATH . 'img/productos/' . $producto['imagen'])): ?>
                            <img src="<?= base_url('img/productos/' . $producto['imagen']) ?>" alt="<?= esc($producto['nombre'], 'attr') ?>">
                        <?php else: ?>
                            <span class="sin-foto" title="Sin foto"><i class="fa-solid fa-image"></i></span>
                        <?php endif; ?>
                    </td>
                    <td class="codigo"><?= esc($producto['codigo'] ?? '') ?></td>
                    <td class="nombre"><?= esc($producto['nombre']) ?></td>
                    <td><?= esc($producto['categoria']) ?></td>
                    <td class="descripcion"><?= esc($producto['descripcion']) ?></td>
                    <td class="acciones">
                        <button class="btn btn-warning btn-sm"
                            onclick="abrirModal('<?= base_url('editar/'.$producto['id_producto']) ?>','Editar producto')">
                            <i class="fa fa-pencil"></i> Editar
                        </button>
                        <button class="btn btn-danger btn-sm eliminar-prod-btn"
                            data-id="<?= $producto['id_producto'] ?>">
                            <i class="fa fa-trash"></i> Eliminar
                        </button>
                        <button class="btn btn-info btn-sm historial-btn"
                            data-id="<?= $producto['id_producto'] ?>"
                            data-nombre="<?= esc($producto['nombre'], 'attr') ?>">
                            <i class="fa-solid fa-clock-rotate-left"></i> Entradas
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <!-- PAGINACIÓN -->
        
            <?= $pager->links("default","v_paginacion") ?>

        
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="miModal">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeContenido" style="width:100%; height:400px; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!--modal de historial-->
<div class="modal fade" id="modalHistorial">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloHistorial">Historial de Entradas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="cuerpoHistorial">
                    <!-- Se llena con AJAX -->
                </div>
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
<script src="<?= base_url('js/producto/v_principal_productos.js') ?>?v=2"></script>
<?= $this->endSection() ?>