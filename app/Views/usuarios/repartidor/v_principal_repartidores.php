<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/comun/v_paginacion.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_tabla_datos.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/repartidor/v_principal_repartidores.css') ?>?v=2">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="fv-pagina">

    <!-- ───────── Encabezado: título y botón de registrar ───────── -->
    <div class="fv-encabezado">
        <div>
            <h3 id="titulo">Gestión de Repartidores</h3>
            <small>Administra la información de tus repartidores</small>
        </div>
        <?php if (puede('repartidores.crear')): ?>
        <button class="btn btn-success"
            onclick="abrirModal('<?= base_url('alta_repartidores') ?>', 'Registrar Repartidor')">
            + Registrar Repartidor
        </button>
        <?php endif; ?>
    </div>

    <!-- ───────── Buscador ───────── -->
    <div class="card mb-3 p-3" id="buscador-card">
        <label for="buscar"><b><i class="fa-solid fa-magnifying-glass"></i> Búsqueda de Repartidores</b></label>
        <input type="text" id="buscar" class="form-control mt-2"
               value="<?= esc($filtros['q']) ?>"
               placeholder="Buscar por nombre, apellido o teléfono...">
    </div>

    <!-- ───────── Tabla de repartidores ───────── -->
    <div class="card p-3">
        <?= $this->include('usuarios/repartidor/v_tabla_repartidores') ?>
    </div>
</div>

<!-- ───────── Ventana modal (aquí se cargan los formularios de alta y edición) ───────── -->
<div class="modal fade" id="miModal" tabindex="-1" aria-labelledby="miModalTitulo">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="miModalTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeContenido" title="Formulario de repartidor"></iframe>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // ───────── Modal con el formulario (alta o edición) ─────────
    const modal  = new bootstrap.Modal(document.getElementById('miModal'));
    const iframe = document.getElementById('iframeContenido');

    function abrirModal(url, titulo) {
        document.getElementById('miModalTitulo').textContent = titulo;
        iframe.src = url;
        modal.show();
    }

    // La llaman los formularios (desde el iframe) cuando guardan bien
    function cerrarModal() {
        modal.hide();
        iframe.src = '';
        location.reload();
    }

    // El iframe toma la altura de su formulario, así nunca se corta el botón de guardar.
    // Se mide al cargar, cuando el modal ya terminó de abrir y si cambia el tamaño de la ventana.
    function ajustarAltura() {
        try {
            const doc = iframe.contentDocument;
            if (doc && doc.body && doc.body.children.length) {
                iframe.style.height = '0px';                       // reinicia para medir bien
                iframe.style.height = doc.documentElement.scrollHeight + 'px';
            }
        } catch (e) { /* si no se puede medir, se queda con la altura mínima del CSS */ }
    }
    iframe.addEventListener('load', function () { setTimeout(ajustarAltura, 60); });
    document.getElementById('miModal').addEventListener('shown.bs.modal', ajustarAltura);
    window.addEventListener('resize', ajustarAltura);

    // ───────── Eliminar: primero verifica que no tenga pedidos asignados ─────────
    $(document).on('click', '.eliminar-rep-btn', function () {
        const id = $(this).data('id');

        $.ajax({
            url: "<?= base_url('verificar_repartidor') ?>/" + id,
            method: "GET",
            success: function (response) {
                if (!response.puede_eliminar) {
                    alert("No se puede eliminar: " + response.mensaje);
                    return;
                }

                if (confirm("¿Estás seguro de que deseas eliminar este repartidor?")) {
                    $.ajax({
                        url: "<?= base_url('eliminarrep') ?>/" + id,
                        method: "GET",
                        success: function () {
                            alert("Repartidor eliminado correctamente.");
                            location.reload();
                        }
                    });
                }
            },
            error: function (xhr) {
                console.log("ERROR:", xhr.status, xhr.responseText);
                alert("Error al conectar con el servidor.");
            }
        });
    });

    // ───────── Buscador: espera a que termines de escribir y recarga con ?q= ─────────
    let espera = null;
    const buscar = document.getElementById('buscar');

    buscar.addEventListener('input', function () {
        clearTimeout(espera);
        const termino = this.value.trim();

        espera = setTimeout(function () {
            const url = new URL(window.location.href);
            if (termino) {
                url.searchParams.set('q', termino);   // searchParams escapa el texto: llega como texto plano
            } else {
                url.searchParams.delete('q');
            }
            url.searchParams.delete('page');          // una búsqueda nueva empieza en la página 1
            window.location.href = url.toString();
        }, 400);
    });

    // Al cargar, deja el cursor al final del buscador para seguir escribiendo
    document.addEventListener('DOMContentLoaded', function () {
        if (buscar.value) {
            buscar.focus();
            buscar.setSelectionRange(buscar.value.length, buscar.value.length);
        }
    });
</script>
<?= $this->endSection() ?>
