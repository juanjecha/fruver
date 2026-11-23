<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/comun/v_paginacion.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_tabla_datos.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/vendedor/v_principal_vendedores.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="fv-pagina">

    <!-- ───────── Encabezado: título, descripción y botón de crear ───────── -->
    <div class="fv-encabezado">
        <div>
            <h3 id="titulo">Cuentas de vendedores avanzados</h3>
            <small>
                Los vendedores avanzados tienen acceso a productos, entradas, merma y existencias;
                a pedidos (crearlos, editarlos, cambiar su estado, asignar repartidor y registrar pagos)
                y a créditos. No pueden gestionar clientes, repartidores ni cuentas.
            </small>
        </div>
        <?php if (puede('usuarios.crear')): ?>
        <button class="btn btn-success"
            onclick="abrirModal('<?= base_url('alta_vendedores') ?>', 'Crear cuenta nueva')">
            + Crear cuenta nueva
        </button>
        <?php endif; ?>
    </div>

    <!-- ───────── Lista de cuentas ───────── -->
    <div class="card p-3">
        <?= $this->include('usuarios/vendedor/v_tabla_vendedores') ?>
    </div>
</div>

<!-- ───────── Ventana modal (aquí se carga el formulario) ───────── -->
<div class="modal fade" id="miModal" tabindex="-1" aria-labelledby="miModalTitulo">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="miModalTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeContenido" title="Formulario de vendedor avanzado"></iframe>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // ───────── Modal con el formulario ─────────
    const modal  = new bootstrap.Modal(document.getElementById('miModal'));
    const iframe = document.getElementById('iframeContenido');

    function abrirModal(url, titulo) {
        document.getElementById('miModalTitulo').textContent = titulo;
        iframe.src = url;
        modal.show();
    }

    // La llama el formulario (desde el iframe) cuando guarda bien
    function cerrarModal() {
        modal.hide();
        iframe.src = '';
        location.reload();
    }

    // El iframe toma la altura de su formulario, así nunca se corta el botón de guardar
    function ajustarAltura() {
        try {
            const doc = iframe.contentDocument;
            if (doc && doc.body && doc.body.children.length) {
                iframe.style.height = '0px';
                iframe.style.height = doc.documentElement.scrollHeight + 'px';
            }
        } catch (e) { /* si no se puede medir, queda la altura mínima del CSS */ }
    }
    iframe.addEventListener('load', function () { setTimeout(ajustarAltura, 60); });
    document.getElementById('miModal').addEventListener('shown.bs.modal', ajustarAltura);
    window.addEventListener('resize', ajustarAltura);
    $(document).on('click', '.btn-ver-pw', function () {
    const span  = $(this).siblings('.pw-texto');
    const oculto = span.text().startsWith('•');
    span.text(oculto ? span.data('pw') : '••••••••••');
    $(this).find('i').toggleClass('fa-eye fa-eye-slash');
});
</script>
<?= $this->endSection() ?>
