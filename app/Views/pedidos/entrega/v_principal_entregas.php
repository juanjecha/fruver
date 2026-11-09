<?php
/**
 * Gestión de entregas (layout con menú lateral).
 * Lo llama EntregaController::entregas() con $this->render(...).
 * $soloMias = true cuando entra un repartidor: ve únicamente sus pedidos asignados
 * y no ve ni el filtro por repartidor ni los botones para asignar.
 */
$soloMias = $soloMias ?? false;
$puedeActualizarEntrega = $soloMias && puede('pedidos.cambiar_estado');
?>
<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/entrega/v_principal_entregas.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="fv-pagina" id="entregas">

    <!-- ENCABEZADO -->
    <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h3 id="titulo"><?= $soloMias ? 'Mis entregas de hoy' : 'Gestión de Entregas' ?></h3>
            <small><?= $soloMias ? 'Pedidos que se te asignaron para hoy' : 'Administra las entregas de hoy' ?></small>
        </div>
        <?php if ($soloMias): ?>
            <button type="button" class="btn btn-outline-primary" id="btnMostrarHistorial"><i class="fa-solid fa-clock-rotate-left me-1"></i> Historial</button>
        <?php endif; ?>
    </div>

    <div id="panelGestionPedidos">

    <!-- TARJETAS RESUMEN -->
    <div class="row g-3 mb-4" id="resumen">
        <div class="col-6 col-md-3">
            <div class="card p-3 stat stat--total">
                <small>Total Pedidos</small>
                <h4 class="fw-bold"><?= $total ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card p-3 stat stat--solicitado">
                <small>Solicitados</small>
                <h4 class="fw-bold"><?= $solicitados ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card p-3 stat stat--proceso">
                <small>En Proceso</small>
                <h4 class="fw-bold"><?= $enProceso ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card p-3 stat stat--completo">
                <small>Completados</small>
                <h4 class="fw-bold"><?= $completados ?></h4>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="card p-3 mb-4" id="filtros">
        <form method="GET" action="<?= base_url('gestion_entregas') ?>">
            <div class="row g-3 align-items-end">

                <?php if (! $soloMias): ?>
                <div class="col-12 col-md-4">
                    <label class="form-label">Filtrar por repartidor</label>
                    <select name="id_repartidor" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($repartidores as $r): ?>
                            <option value="<?= $r['id_repartidor'] ?>"
                                <?= $idRepartidor == $r['id_repartidor'] ? 'selected' : '' ?>>
                                <?= esc($r['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-12 col-md-4">
                    <label class="form-label">Filtrar por status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="solicitado"  <?= $status === 'solicitado'  ? 'selected' : '' ?>>Solicitado</option>
                        <option value="confirmado"  <?= $status === 'confirmado'  ? 'selected' : '' ?>>Confirmado</option>
                        <option value="en transito" <?= $status === 'en transito' ? 'selected' : '' ?>>En Tránsito</option>
                        <option value="entregado"   <?= $status === 'entregado'   ? 'selected' : '' ?>>Entregado</option>
                    </select>
                </div>

                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="<?= base_url('gestion_entregas') ?>" class="btn btn-secondary">Limpiar</a>
                </div>
            </div>
        </form>
    </div>

    <!-- LISTA DE PEDIDOS -->
    <?php if (empty($pedidos)): ?>
        <?php if (! empty($status) || (! $soloMias && ! empty($idRepartidor))): ?>
            <div class="alert alert-warning">No se encontraron pedidos para hoy con los filtros seleccionados.</div>
        <?php else: ?>
            <div class="alert alert-info">
                <?= $soloMias ? 'No tienes entregas asignadas para hoy.' : 'No hay entregas programadas para hoy.' ?>
            </div>
        <?php endif; ?>
    <?php else: ?>

        <div class="row g-3">
        <?php foreach ($pedidos as $pedido): ?>
        <?php
            $badgeColor = match($pedido['status']) {
                'confirmado'  => 'primary',
                'en transito' => 'warning',
                'entregado'   => 'success',
                default       => 'secondary'  // solicitado o null
            };
        ?>
        <div class="col-12 col-xl-6">
            <article class="entrega-item entrega-item--<?= $badgeColor ?> h-100">

                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <span class="entrega-item__num">Pedido #<?= $pedido['id_pedido'] ?></span>
                        <span class="badge bg-<?= $badgeColor ?> ms-1">
                            <?= ucfirst($pedido['status'] ?? 'Solicitado') ?>
                        </span>
                    </div>
                    <span class="entrega-item__fecha"><?= esc($pedido['fecha']) ?></span>
                </div>

                <div class="entrega-item__info">
                    <div><strong>Cliente:</strong> <?= estrella_cliente($pedido['cliente_preferencial']) ?> <?= esc($pedido['cliente']) ?></div>
                    <div><strong>Teléfono:</strong> <?= esc($pedido['telefono'] ?? 'N/A') ?></div>
                    <div><strong>Dirección:</strong> <?= esc($pedido['direccion_pedido'] ?? 'No especificada') ?></div>
                    <?php if (! $soloMias): ?>
                    <div><strong>Repartidor:</strong> <?= esc($pedido['repartidor'] ?? 'No asignado') ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($soloMias): ?>
                <div class="entrega-item__acciones mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btnVerDetalle" data-id="<?= (int) $pedido['id_pedido'] ?>">👁 Detalles</button>
                </div>
                <?php endif; ?>

                <?php if ($soloMias && $puedeActualizarEntrega): ?>
                <div class="entrega-item__acciones">
                    <?php if (($pedido['status'] ?? 'solicitado') === 'confirmado'): ?>
                        <button type="button" class="btn btn-warning btn-sm btnCambiarEstadoEntrega"
                                data-id="<?= (int) $pedido['id_pedido'] ?>" data-status="en transito">
                            <i class="fa-solid fa-truck-fast me-1"></i> Iniciar entrega
                        </button>
                    <?php elseif (($pedido['status'] ?? '') === 'en transito'): ?>
                        <button type="button" class="btn btn-success btn-sm btnCambiarEstadoEntrega"
                                data-id="<?= (int) $pedido['id_pedido'] ?>" data-status="entregado">
                            <i class="fa-solid fa-check me-1"></i> Marcar entregado
                        </button>
                    <?php elseif (($pedido['status'] ?? '') === 'entregado'): ?>
                        <span class="text-success small fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Entrega completada</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (! $soloMias): ?>
                <div class="entrega-item__acciones">
                    <?php if (is_null($pedido['status']) || $pedido['status'] === 'solicitado'): ?>
                        <button class="btn btn-dark btn-sm btnAsignarRepartidor"
                                data-id="<?= $pedido['id_pedido'] ?>"
                                data-repartidor="<?= $pedido['id_repartidor'] ?? '' ?>"
                                data-titulo="Asignar Repartidor">
                            Asignar Repartidor
                        </button>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary btn-sm btnAsignarRepartidor"
                                data-id="<?= $pedido['id_pedido'] ?>"
                                data-repartidor="<?= $pedido['id_repartidor'] ?? '' ?>"
                                data-titulo="Cambiar Repartidor">
                            Cambiar Repartidor
                        </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </article>
        </div>
        <?php endforeach; ?>
        </div>

    <?php endif; ?>
    </div><!-- /panelGestionPedidos -->

    <?php if ($soloMias): ?>
    <div id="panelHistorialPedidos" style="display:none">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div><h4 class="mb-1">Historial de Pedidos Entregados</h4><small class="text-muted">Ordenados del más reciente al más antiguo.</small></div>
            <button type="button" class="btn btn-outline-secondary" id="btnVolverGestion"><i class="fa-solid fa-arrow-left me-1"></i> Volver a Gestión</button>
        </div>
        <?php if (empty($historialPedidos)): ?>
            <div class="alert alert-info">Aún no tienes pedidos entregados en el historial.</div>
        <?php else: ?>
        <div class="card p-3"><div class="table-responsive"><table class="table align-middle">
            <thead class="table-light"><tr><th>Pedido</th><th>Cliente</th><th>Dirección</th><th>Fecha de entrega</th><th>Estado</th><th>Tipo de pago</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($historialPedidos as $hp): ?>
                <?php $estadoHistorial = $hp['status'] ?? 'entregado'; $pagoHistorial = ($hp['pago'] ?? '') === 'contado' ? 'Contado' : ((($hp['pago'] ?? '') === 'credito') ? 'Crédito' : 'No definido'); ?>
                <tr>
                    <td>#<?= (int) $hp['id_pedido'] ?></td><td><?= esc($hp['cliente']) ?></td>
                    <td><?= esc($hp['direccion_pedido'] ?? 'No especificada') ?></td>
                    <td><?= esc(!empty($hp['fecha_entregado']) ? date('d/m/Y H:i', strtotime($hp['fecha_entregado'])) : 'Sin fecha') ?></td>
                    <td><span class="badge bg-success"><?= esc(ucfirst($estadoHistorial)) ?></span></td><td><?= esc($pagoHistorial) ?></td>
                    <td><button type="button" class="btn btn-outline-secondary btn-sm btnVerDetalle" data-id="<?= (int) $hp['id_pedido'] ?>">👁 Detalles</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($soloMias): ?>
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
    <?php endif; ?>
</div>

<?php if (! $soloMias): ?>
<!-- MODAL ASIGNAR / CAMBIAR REPARTIDOR -->
<div class="modal fade" id="modalRepartidor">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRepartidorTitulo">Asignar Repartidor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rep_id_pedido">
                <div class="mb-3">
                    <label class="form-label">Repartidor *</label>
                    <select id="rep_id_repartidor" class="form-select">
                        <option value="">Selecciona un repartidor</option>
                        <?php foreach ($repartidores as $r): ?>
                            <option value="<?= $r['id_repartidor'] ?>"><?= esc($r['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarRepartidor">Guardar</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<?php if ($soloMias): ?>
<?php if ($puedeActualizarEntrega): ?>
<script>
$(document).on('click', '.btnCambiarEstadoEntrega', function () {
    const boton = $(this);
    const idPedido = boton.data('id');
    const status = boton.data('status');
    const mensaje = status === 'entregado'
        ? '¿Confirmas que este pedido ya fue entregado al cliente?'
        : '¿Deseas iniciar la entrega de este pedido?';

    if (!confirm(mensaje)) return;

    boton.prop('disabled', true);
    $.ajax({
        url: "<?= base_url('actualizar_status') ?>",
        method: 'POST',
        data: { id_pedido: idPedido, status: status },
        success: function (res) {
            if (String(res).trim() === 'ok') {
                location.reload();
            } else {
                boton.prop('disabled', false);
                alert('No se pudo actualizar el estado del pedido.');
            }
        },
        error: function (xhr) {
            boton.prop('disabled', false);
            alert('No se pudo actualizar el pedido (error ' + xhr.status + ').');
        }
    });
});
</script>
<?php endif; ?>
<script>
$('#btnMostrarHistorial').on('click', function () { $('#panelGestionPedidos').hide(); $('#panelHistorialPedidos').show(); });
$('#btnVolverGestion').on('click', function () { $('#panelHistorialPedidos').hide(); $('#panelGestionPedidos').show(); });
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

            const metodoPago = p.pago === 'contado' ? 'Contado' : (p.pago === 'credito' ? 'Crédito' : 'No definido');
            const fechaPedido = p.fecha ?? 'No disponible';
            const fechaEntrega = p.fecha_entrega ?? 'Sin fecha de entrega';

            $('#modalDetalleBody').html(`
                <h6 class="fw-bold mb-3">Información del pedido</h6>
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="small text-muted">Cliente</div>
                                <div class="fw-semibold">${p.cliente_preferencial == 1 ? '<i class="fa fa-star text-warning me-1" title="Cliente preferencial"></i>' : ''}${p.cliente ?? 'No disponible'}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">Teléfono</div>
                                <div class="fw-semibold">${p.telefono ?? 'No disponible'}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">Fecha del pedido</div>
                                <div class="fw-semibold">${fechaPedido}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">Fecha de entrega</div>
                                <div class="fw-semibold">${fechaEntrega}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">Repartidor</div>
                                <div class="fw-semibold">${p.repartidor ?? 'No asignado'}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">Método de pago</div>
                                <div class="fw-semibold">${metodoPago}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">Dirección</div>
                                <div class="fw-semibold">${p.direccion_pedido ?? 'No especificada'}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">Estatus actual</div>
                                <div><span class="badge bg-${color}">${status}</span></div>
                            </div>
                        </div>
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
<?php endif; ?>
<?php if (! $soloMias): ?>
<script>
    const modalRepartidor = new bootstrap.Modal(document.getElementById('modalRepartidor'));

    // Abrir modal
    $(document).on('click', '.btnAsignarRepartidor', function () {
        $('#rep_id_pedido').val($(this).data('id'));
        $('#rep_id_repartidor').val($(this).data('repartidor') || '');
        $('#modalRepartidorTitulo').text($(this).data('titulo'));
        modalRepartidor.show();
    });

    // Guardar
    $('#btnGuardarRepartidor').on('click', function () {
        const idPedido     = $('#rep_id_pedido').val();
        const idRepartidor = $('#rep_id_repartidor').val();

        if (!idRepartidor) {
            alert("Selecciona un repartidor.");
            return;
        }

        $.ajax({
            url: "<?= base_url('asignar_repartidor') ?>",
            method: "POST",
            data: { id_pedido: idPedido, id_repartidor: idRepartidor },
            success: function (res) {
                if (res.trim() === "ok") {
                    modalRepartidor.hide();
                    location.reload();
                } else {
                    alert("Error: " + res);
                }
            },
            error: function (xhr) {
                alert("Error " + xhr.status + ": " + xhr.responseText);
            }
        });
    });
</script>
<?php else: ?>
<?php if ($puedeActualizarEntrega): ?>
<script>
$(document).on('click', '.btnCambiarEstadoEntrega', function () {
    const boton = $(this);
    const idPedido = boton.data('id');
    const status = boton.data('status');
    const mensaje = status === 'entregado'
        ? '¿Confirmas que este pedido ya fue entregado al cliente?'
        : '¿Deseas iniciar la entrega de este pedido?';

    if (!confirm(mensaje)) return;

    boton.prop('disabled', true);
    $.ajax({
        url: "<?= base_url('actualizar_status') ?>",
        method: 'POST',
        data: { id_pedido: idPedido, status: status },
        success: function (res) {
            if (String(res).trim() === 'ok') {
                location.reload();
            } else {
                boton.prop('disabled', false);
                alert('No se pudo actualizar el estado del pedido.');
            }
        },
        error: function (xhr) {
            boton.prop('disabled', false);
            alert('No se pudo actualizar el pedido (error ' + xhr.status + ').');
        }
    });
});
</script>
<?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>