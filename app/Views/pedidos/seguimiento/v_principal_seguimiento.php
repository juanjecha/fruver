<?php
/**
 * Seguimiento de pedidos (layout con menú lateral).
 * Lo llama SeguimientoController::seguimiento() con $this->render(...).
 * Secciones: estilos -> <head> | contenido -> <main> | scripts -> final del <body> (después de jQuery y Bootstrap)
 */

$soloLecturaRepartidor = session('rol') === 'repartidor_avanzado';
?>
<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/seguimiento/v_principal_seguimiento.css') ?>">
<?= $this->endSection() ?>


<?= $this->section('contenido') ?>
<div class="fv-pagina">

<!-- ENCABEZADO -->
<h3 id="titulo">Seguimiento de Pedidos</h3>
<small>Vista global de todos los pedidos y sus estatus</small>

<!-- TARJETAS RESUMEN -->
<div class="row mt-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3">
            <small class="text-muted">Total Pedidos</small>
            <h4 class="fw-bold"><?= $total ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <small class="text-muted">Solicitados</small>
            <h4 class="fw-bold"><?= $solicitados ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <small class="text-muted">En Proceso</small>
            <h4 class="fw-bold text-primary"><?= $enProceso ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <small class="text-muted">Completados</small>
            <h4 class="fw-bold text-success"><?= $completados ?></h4>
        </div>
    </div>
</div>

<!-- BUSCAR Y FILTRAR -->
<form method="GET" action="<?= base_url('seguimiento_pedidos') ?>" id="formFiltro">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card p-3">
                <label class="fw-semibold mb-2">Buscar Pedidos</label>
                <input type="text" name="buscar" class="form-control"
                       placeholder="Buscar por ID, cliente o fecha..."
                       value="<?= esc($buscar) ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <label class="fw-semibold mb-2">Filtrar por Estatus</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="solicitado"  <?= $status === 'solicitado'  ? 'selected' : '' ?>>Solicitado</option>
                    <option value="confirmado"  <?= $status === 'confirmado'  ? 'selected' : '' ?>>Confirmado</option>
                    <option value="en transito" <?= $status === 'en transito' ? 'selected' : '' ?>>En Tránsito</option>
                    <option value="entregado"   <?= $status === 'entregado'   ? 'selected' : '' ?>>Entregado</option>
                    <option value="pagado"      <?= $status === 'pagado'      ? 'selected' : '' ?>>Pagado</option>
                    <option value="cancelado"   <?= $status === 'cancelado'   ? 'selected' : '' ?>>Cancelado</option>
                    <option value="credito"     <?= $status === 'credito'     ? 'selected' : '' ?>>Crédito</option>
                </select>
            </div>
        </div>
        <div class="col-12 text-end mt-2">
            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
            <a href="<?= base_url('seguimiento_pedidos') ?>" class="btn btn-secondary btn-sm">Limpiar</a>
        </div>
    </div>
</form>

<!-- TABLA -->
<div class="card p-3">
    <p class="fw-semibold">Lista de Pedidos (<?= count($pedidos) ?>)</p>
    <div class="table-responsive">
    <table class="table align-middle" id="tablaPedidos">
        <thead class="table-light">
            <tr>
                <th>ID Pedido</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Fecha Entrega</th>
                <th>Dirección</th>
                <th>Tipo de pago</th>
                <th>Total</th>
                <th>Estatus actual</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pedidos as $pedido): ?>
        <?php
            $total_pedido = array_sum(array_column($pedido['productos'], 'total'));

            $badgeColor = match($pedido['status']) {
                'confirmado'  => 'primary',
                'en transito' => 'warning',
                'entregado'   => 'success',
                'pagado'      => 'dark',
                'cancelado'   => 'danger',
                'credito'     => 'info',
                default       => 'secondary'
            };
        ?>
        <tr>
            <td>#<?= $pedido['id_pedido'] ?></td>
            <td><?= estrella_cliente($pedido['cliente_preferencial']) ?> <?= esc($pedido['cliente']) ?></td>
            <td><?= $pedido['fecha'] ?></td>
            <td><?= esc($pedido['fecha_entrega'] ?? 'Sin fecha') ?></td>
            <td><?= esc($pedido['direccion_pedido'] ?? 'No especificada') ?></td>
            <td><?= esc(($pedido['pago'] ?? '') === 'contado' ? 'Contado' : ((($pedido['pago'] ?? '') === 'credito') ? 'Crédito' : 'No definido')) ?></td>
            <td>$<?= number_format($total_pedido, 2) ?></td>
            <td>
                <span class="badge bg-<?= $badgeColor ?>">
                    <?= ucfirst($pedido['status'] ?? 'Solicitado') ?>
                </span>
            </td>
            <td class="d-flex gap-2">
                <!-- Detalles -->
                <button class="btn btn-outline-secondary btn-sm btnVerDetalle"
                        data-id="<?= $pedido['id_pedido'] ?>">
                    👁 Detalles
                </button>

                <!-- Las acciones de cambio quedan ocultas para el repartidor avanzado: seguimiento es de solo lectura. -->
                <?php if (! $soloLecturaRepartidor): ?>
                <!-- Botones de estatus -->
                <?php if (is_null($pedido['status']) || $pedido['status'] === 'solicitado'): ?>
                    <button class="btn btn-dark btn-sm btnAbrirConfirmar"
                            data-id="<?= $pedido['id_pedido'] ?>">
                        Confirmar
                    </button>

                <?php elseif ($pedido['status'] === 'confirmado'): ?>
                    <button class="btn btn-dark btn-sm btnCambiarStatus"
                            data-id="<?= $pedido['id_pedido'] ?>"
                            data-status="en transito">
                        En Tránsito
                    </button>

                <?php elseif ($pedido['status'] === 'en transito'): ?>
                    <button class="btn btn-dark btn-sm btnCambiarStatus"
                            data-id="<?= $pedido['id_pedido'] ?>"
                            data-status="entregado">
                        Entregado
                    </button>

                <?php elseif ($pedido['status'] === 'entregado'): ?>
                    <button class="btn btn-success btn-sm btnAbrirPago"
                            data-id="<?= $pedido['id_pedido'] ?>">
                        Marcar Pagado
                    </button>

                <?php elseif ($pedido['status'] === 'credito'): ?>
                    <button class="btn btn-primary btn-sm btnLiquidarCredito"
                            data-id="<?= $pedido['id_pedido'] ?>">
                        Liquidar
                    </button>

                <?php endif; ?>

                <?php if (!in_array($pedido['status'], ['pagado', 'cancelado'])): ?>
                    <button class="btn btn-danger btn-sm btnCancelar"
                            data-id="<?= $pedido['id_pedido'] ?>">
                        Cancelar
                    </button>
                <?php endif; ?>
                <?php endif; // fin acciones de modificación ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- ==================== MODALES ==================== -->

<!-- MODAL CONFIRMAR -->
<div class="modal fade" id="modalConfirmar">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="confirmar_id_pedido">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Asignar Repartidor *</label>
                    <select id="confirmar_repartidor" class="form-select">
                        <option value="">Selecciona un repartidor</option>
                        <?php foreach ($repartidores as $r): ?>
                        <option value="<?= $r['id_repartidor'] ?>">
                            <?= esc($r['nombre_completo']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="btnGuardarConfirmar">Confirmar Pedido</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PAGO -->
<div class="modal fade" id="modalPago">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Método de Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pago_id_pedido">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Selecciona el método de pago *</label>
                    <select id="pago_metodo" class="form-select">
                        <option value="">Selecciona...</option>
                        <option value="pago">Contado</option>
                        <option value="credito">Crédito</option>
                    </select>
                </div>
                <small class="text-muted">
                    Si es <strong>crédito</strong>, el pedido quedará pendiente de pago.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarPago">Marcar Pagado</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETALLE -->
<div class="modal fade" id="modalDetalle">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Pedido <span id="detalle_id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetalleBody">
                <!-- Se llena dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

</div><!-- /fv-pagina -->
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
// Inicializar modales
const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmar'));
const modalPago      = new bootstrap.Modal(document.getElementById('modalPago'));
const modalDetalle   = new bootstrap.Modal(document.getElementById('modalDetalle'));

// ── Cambiar estatus directo ──
$(document).on('click', '.btnCambiarStatus', function() {
    const idPedido = $(this).data('id');
    const status   = $(this).data('status');

    $.ajax({
        url: "<?= base_url('actualizar_status') ?>",
        method: "POST",
        data: { id_pedido: idPedido, status: status },
        success: function(res) {
            if (res.trim() === "ok") {
                location.reload();
            } else {
                alert("Error: " + res);
            }
        },
        error: function(xhr) {
            alert("Error " + xhr.status + ": " + xhr.responseText);
        }
    });
});

// ── Abrir modal confirmar ──
$(document).on('click', '.btnAbrirConfirmar', function() {
    $('#confirmar_id_pedido').val($(this).data('id'));
    $('#confirmar_repartidor').val('');
    modalConfirmar.show();
});

// ── Guardar confirmación ──
$('#btnGuardarConfirmar').on('click', function() {
    const idPedido     = $('#confirmar_id_pedido').val();
    const idRepartidor = $('#confirmar_repartidor').val();

    if (!idRepartidor) {
        
        return;
    }

    $.ajax({
        url: "<?= base_url('confirmar_pedido') ?>",
        method: "POST",
        data: { id_pedido: idPedido, id_repartidor: idRepartidor },
        success: function(res) {
            if (res.trim() === "ok") {
                modalConfirmar.hide();
                location.reload();
            } else {
                alert("Error: " + res);
            }
        }
    });
});

// ── Abrir modal pago ──
$(document).on('click', '.btnAbrirPago', function() {
    $('#pago_id_pedido').val($(this).data('id'));
    $('#pago_metodo').val('');
    modalPago.show();
});

// ── Guardar pago ──
$('#btnGuardarPago').on('click', function() {
    const idPedido = $('#pago_id_pedido').val();
    const pago     = $('#pago_metodo').val();

    if (!pago) {
        alert("Selecciona un método de pago.");
        return;
    }

    $.ajax({
        url: "<?= base_url('marcar_pagado') ?>",
        method: "POST",
        data: { id_pedido: idPedido, pago: pago },
        success: function(res) {
            if (res.trim() === "ok") {
                modalPago.hide();
                location.reload();
            } else {
                alert("Error: " + res);
            }
        }
    });
});

// ── Liquidar crédito ──
$(document).on('click', '.btnLiquidarCredito', function() {
    if (!confirm("¿Confirmar pago del crédito?")) return;

    const idPedido = $(this).data('id');

    $.ajax({
        url: "<?= base_url('actualizar_status') ?>",
        method: "POST",
        data: { id_pedido: idPedido, status: 'pagado' },
        success: function(res) {
            if (res.trim() === "ok") {
                location.reload();
            } else {
                alert("Error: " + res);
            }
        }
    });
});

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