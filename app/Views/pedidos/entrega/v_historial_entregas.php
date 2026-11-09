<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('css/entrega/v_historial_entregas.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="fv-pagina" id="historial-entregas">
    <div class="historial-encabezado">
        <div>
            <h3>Historial de Pedidos Entregados</h3>
            <p>Consulta las entregas registradas, ordenadas de la más reciente a la más antigua.</p>
        </div>
        <div class="historial-total">
            <span>Total de entregas</span>
            <strong><?= count($pedidos) ?></strong>
        </div>
    </div>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info historial-vacio">
            <i class="fa-solid fa-box-open me-2"></i>
            Aún no hay pedidos entregados para mostrar en este historial.
        </div>
    <?php else: ?>
        <div class="card historial-tabla-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Dirección</th>
                            <th>Fecha y hora de entrega</th>
                            <th>Estado actual</th>
                            <th>Tipo de pago</th>
                            <?php if (!in_array(session('rol'), ['repartidor', 'repartidor_avanzado'], true)): ?>
                                <th>Repartidor</th>
                            <?php endif; ?>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <?php
                            $estado = $pedido['status'] ?? 'solicitado';
                            $colorEstado = match ($estado) {
                                'confirmado' => 'primary',
                                'en transito' => 'warning',
                                'entregado' => 'success',
                                'pagado' => 'dark',
                                'credito' => 'info',
                                'cancelado' => 'danger',
                                default => 'secondary',
                            };
                            $fechaEntrega = $pedido['fecha_entregado'] ?? null;
                            $fechaEntregaTexto = $fechaEntrega
                                ? date('d/m/Y H:i', strtotime($fechaEntrega))
                                : 'Sin fecha registrada';
                            $pago = ($pedido['pago'] ?? '') === 'contado'
                                ? 'Contado'
                                : ((($pedido['pago'] ?? '') === 'credito') ? 'Crédito' : 'No definido');
                        ?>
                        <tr>
                            <td><span class="historial-numero">#<?= (int) $pedido['id_pedido'] ?></span></td>
                            <td><?= esc($pedido['cliente']) ?></td>
                            <td class="historial-direccion"><?= esc($pedido['direccion_pedido'] ?? 'No especificada') ?></td>
                            <td><?= esc($fechaEntregaTexto) ?></td>
                            <td><span class="badge bg-<?= $colorEstado ?>"><?= esc(ucfirst($estado)) ?></span></td>
                            <td><?= esc($pago) ?></td>
                            <?php if (!in_array(session('rol'), ['repartidor', 'repartidor_avanzado'], true)): ?>
                                <td><?= esc($pedido['repartidor'] ?? 'No asignado') ?></td>
                            <?php endif; ?>
                            <td><button type="button" class="btn btn-outline-secondary btn-sm btnVerDetalle" data-id="<?= (int) $pedido['id_pedido'] ?>">👁 Detalles</button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle del Pedido <span id="detalle_id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="modalDetalleBody"></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const modalDetalle = new bootstrap.Modal(document.getElementById('modalDetalle'));
// --------------- Ver detalles ---------------
$(document).on('click', '.btnVerDetalle', function() {
    const idPedido = $(this).data('id');
    $('#detalle_id').text('#' + idPedido);
    $('#modalDetalleBody').html('<p class="text-center">Cargando...</p>');
    modalDetalle.show();

    $.ajax({ //peticion ajax para la info del detalle
        url: "<?= base_url('detalle_pedido') ?>",
        method: "POST",
        data: { id_pedido: idPedido }, // Envía el id al Controlador por POST
        success: function(res) {
            const data = JSON.parse(res);//Convierte el string JSON recibido del servidor en un de js

            if (data.error) {// Si el servidor devolvió un error, se muestra en el modal y se detiene
                $('#modalDetalleBody').html('<p class="text-danger">' + data.error + '</p>');
                return;
            }

            // Desestructura la respuesta en variables más legibles
            const p        = data.pedido;
            const prod     = data.productos;
            const historial = data.historial;

            const colores = {
                'confirmado' : 'primary',
                'en transito': 'warning',
                'entregado'  : 'success',
                'pagado'     : 'dark',
                'cancelado'  : 'danger',
                'credito'    : 'info',
            };


             // Si el status no existe en el objeto colores, usa 'secondary' como fallback (es para solicitado)
            const color  = colores[p.status] || 'secondary';
            const status = p.status ? p.status.charAt(0).toUpperCase() + p.status.slice(1) : 'Solicitado';//Si status es null (sin registro aún), muestra 'Solicitado'

            // Productos
            let filasProductos = '';
            let total = 0;
            prod.forEach(function(pr) {//Itera sobre cada producto del array y acumula el HTML
                total += parseFloat(pr.total || 0); //convierte a decimal y || evita que vaya vacio o null
                filasProductos += `
                <tr>
                    <td>${pr.nombre}</td>
                    <td>${pr.cantidad}</td>
                    <td>${pr.unidad_venta}</td>
                    <td>$${parseFloat(pr.precio_venta).toFixed(2)}</td>
                    <td>$${parseFloat(pr.total).toFixed(2)}</td>
                </tr>`;
            });

            // Historial
            let filasHistorial = '';
            if (historial.length === 0) {// Si no hay registros de historial, muestra mensaje vacío
                filasHistorial = `<tr><td colspan="2" class="text-center text-muted">Sin historial</td></tr>`;
            } else {
                historial.forEach(function(h) {
                    const colorH  = colores[h.estado] || 'secondary';
                    const estadoH = h.estado.charAt(0).toUpperCase() + h.estado.slice(1);// Capitaliza el estado del historial para mostrarlo en el badge
                    filasHistorial += `
                    <tr>
                        <td><span class="badge bg-${colorH}">${estadoH}</span></td>
                        <td>${h.fecha}</td>
                    </tr>`;
                });
            }

            $('#modalDetalleBody').html(`
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Cliente:</strong> ${p.cliente_preferencial == 1 ? '<i class="fa fa-star text-warning" title="Cliente preferencial"></i> ' : ''}${p.cliente}</p>
                        <p class="mb-1"><strong>Teléfono:</strong> ${p.telefono ?? '—'}</p>
                        <p class="mb-1"><strong>Fecha:</strong> ${p.fecha}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Repartidor:</strong> ${p.repartidor ?? 'No asignado'}</p>
                        <p class="mb-1"><strong>Método de pago:</strong> ${p.pago ?? 'No definido'}</p>
                        <p class="mb-1">
                            <strong>Estatus:</strong>
                            <span class="badge bg-${color}">${status}</span>
                        </p>
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold">Productos</h6>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Unidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>${filasProductos}</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total:</td>
                            <td class="fw-bold text-success">$${total.toFixed(2)}</td>
                        </tr>
                    </tfoot>
                </table>

                <hr>

                <h6 class="fw-bold">Historial de Estatus</h6>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Estatus</th>
                            <th>Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody>${filasHistorial}</tbody>
                </table>
            `);
        },
        error: function(xhr) {
            $('#modalDetalleBody').html('<p class="text-danger">Error al cargar el detalle.</p>');
        }
    });
});
</script>
<?= $this->endSection() ?>
