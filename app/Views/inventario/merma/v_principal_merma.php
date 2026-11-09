<?php
/**
 * Merma (Inventario) con layout y menú lateral, igual que Productos.
 * Lo llama MermaController::listar_merma() con:
 *     $this->render('inventario/merma/v_principal_merma', $datos, 'inventario');
 */
?>
<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/comun/v_tabla_datos.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_paginacion.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/merma/v_principal_merma.css') ?>?v=2">
<?= $this->endSection() ?>


<?= $this->section('contenido') ?>
<div class="fv-pagina">

    <div class="fv-encabezado">
        <div>
            <h3 id="titulo">Gestión de Mermas</h3>
            <small>Gestiona las pérdidas y productos dañados</small>
        </div>
        <button class="btn btn-success"
            onclick="abrirModal('<?= base_url('alta_merma') ?>','Nueva merma')">
            + Registrar Merma
        </button>
    </div>

    <!-- BUSCADOR -->
    <div class="card mb-3 p-3"
        id="buscador-card"
        data-url-tabla="<?= esc($url_tabla) ?>">

        <label for="buscar"><b><i class="fa fa-search"></i> Búsqueda de Mermas</b></label>
        <input type="text" id="buscar" class="form-control mt-2"
            value="<?= esc($filtros['q']) ?>"
            placeholder="Buscar por producto o fecha de la merma (AAAA-MM-DD)...">
    </div>

    <!-- LISTADO -->
    <div class="card p-3">
        <h5>Listado de Mermas (<?= $pager->getTotal() ?>)</h5>

        <div class="table-responsive">
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Fecha entrada</th>
                    <th>Unidad</th>
                    <th>Cantidad merma</th>
                    <th>Fecha merma</th>
                    <th>Notas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($datos_merma)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">
                        <?php if (! empty($filtros['q'])): ?>
                            Sin resultados para "<strong><?= esc($filtros['q']) ?></strong>"
                        <?php else: ?>
                            Aún no hay mermas registradas.
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach($datos_merma as $merma): ?>
                <tr class="fila-producto">
                    <td class="nombre"><?= esc($merma['nombre']) ?></td>       <!-- nombre del producto -->
                    <td class="fecha"><?= esc($merma['fecha_entrada']) ?></td>  <!-- fecha de la entrada -->
                    <td><?= esc($merma['u_venta']) ?></td>
                    <td class="cantidad"><?= number_format($merma['cantidad_venta'], 2) ?></td>
                    <td class="fecha"><?= esc($merma['fecha']) ?></td>          <!-- fecha de la merma -->
                    <td class="descripcion"><?= esc($merma['notas']) ?></td>
                    <td class="acciones">
                        <button class="btn btn-warning btn-sm"
                            onclick="abrirModal('<?= base_url('editar_merma/'.$merma['id_merma']) ?>','Editar merma')">
                            <i class="fa fa-pencil"></i> Editar
                        </button>
                        <button class="btn btn-danger btn-sm"
                            onclick="eliminarMerma(<?= $merma['id_merma'] ?>)">
                            <i class="fa fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <!-- Paginación -->
        <?= $pager->links("default","v_paginacion") ?>
    </div>

</div>

<!-- -------------MODAL------------ -->
<div class="modal fade" id="miModal">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeContenido" style="width:100%; height:400px; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    let modal = new bootstrap.Modal(document.getElementById('miModal'));

    function abrirModal(url, titulo)
    {
        document.querySelector('#miModal .modal-title').textContent = titulo;
        $("#iframeContenido").attr("src", url);
        modal.show();
    }

    function cerrarModal() {
        modal.hide();
        $("#iframeContenido").attr("src", "");
        location.reload();
    }

    // El iframe toma la altura de su formulario, así nunca se corta el botón de guardar
    const iframeModal = document.getElementById('iframeContenido');
    function ajustarAltura() {
        try {
            const doc = iframeModal.contentDocument;
            if (doc && doc.body && doc.body.children.length) {
                iframeModal.style.height = '0px';
                iframeModal.style.height = doc.documentElement.scrollHeight + 'px';
            }
        } catch (e) { /* si no se puede medir, se queda con su altura */ }
    }
    iframeModal.addEventListener('load', function () { setTimeout(ajustarAltura, 60); });
    document.getElementById('miModal').addEventListener('shown.bs.modal', ajustarAltura);

    function eliminarMerma(id) {
        if (confirm("¿Seguro que deseas eliminar el registro?")) {
            $.ajax({
                url: "<?= base_url('eliminar_merma') ?>/" + id,
                method: "GET",
                success: function () {
                    location.reload();
                }
            });
        }
    }

    // ───────── BUSCADOR ─────────
    let delay = null; // Temporizador del buscador

    document.getElementById('buscar').addEventListener('input', function () {
        clearTimeout(delay);                       // cancela la búsqueda anterior
        const termino = this.value.trim();

        // espera 400ms después de la última tecla antes de buscar
        delay = setTimeout(function () {
            const url = new URL(window.location.href);
            url.searchParams.set('q', termino);    // agrega ?q= a la URL
            if (!termino) url.searchParams.delete('q');
            url.searchParams.delete('page');       // una búsqueda nueva empieza en la página 1
            window.location.href = url.toString();
        }, 400);
    });

    // Al cargar, deja el cursor al final del buscador
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('buscar');
        if (input && input.value) {
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
        }
    });
</script>
<?= $this->endSection() ?>
