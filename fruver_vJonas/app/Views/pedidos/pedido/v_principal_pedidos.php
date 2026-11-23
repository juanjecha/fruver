<?php
/**
 * Control de pedidos (layout con menú lateral, igual que Clientes y Seguimiento).
 * Lo llama PedidoController::listar_pedidos() con $this->render(..., 'pedidos').
 * Secciones: estilos -> <head> | contenido -> <main> | scripts -> final del <body> (después de jQuery y Bootstrap)
 */
?>
<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/pedido/v_principal_pedidos.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="fv-pagina" id="controlPedidos">

    <!-- ENCABEZADO -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h3 id="titulo">Control de pedidos</h3>
            <small>Registra y da seguimiento a los pedidos</small>
        </div>
        <?php if (puede('pedidos.crear')): ?>
        <button class="btn btn-success"
                onclick="abrirModal('<?= base_url('alta_pedido') ?>','Registrar Pedido')">
            + Registrar Pedido
        </button>
        <?php endif; ?>
    </div>

    <!-- LISTADO DE PEDIDOS -->
    <div class="card p-3">
        <h5>Listado de Pedidos (<?= count($pedidos) ?>)</h5>

        <?php if (empty($pedidos)): ?>
            <p class="text-muted text-center py-3 mb-0">Aún no hay pedidos registrados.</p>
        <?php endif; ?>

        <div class="row g-3 mt-1">
        <?php foreach ($pedidos as $pedido): ?>
        <?php
            $total = array_sum(array_column($pedido['productos'], 'total')); // suma el total de todos los productos

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
        <div class="col-12 col-xl-6">
            <article class="pedido-item h-100">

                <!-- Encabezado: número, estado y total -->
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <span class="pedido-item__num">Pedido #<?= $pedido['id_pedido'] ?></span>
                        <span class="badge bg-<?= $badgeColor ?> ms-1">
                            <?= ucfirst($pedido['status'] ?? 'Solicitado') ?>
                        </span>
                    </div>
                    <span class="pedido-item__total">$<?= number_format($total, 2) ?></span>
                </div>

                <!-- Info -->
                <div class="pedido-item__info">
                    <div><strong>Cliente:</strong> <?= estrella_cliente($pedido['cliente_preferencial']) ?> <?= esc($pedido['cliente']) ?></div>
                    <div><strong>Fecha:</strong> <?= esc($pedido['fecha']) ?></div>
                    <div><strong>Repartidor:</strong> <?= esc($pedido['repartidor'] ?? 'No asignado') ?></div>
                </div>

                <!-- Productos -->
                <div class="pedido-item__productos">
                    <strong>Productos:</strong>
                    <?php foreach ($pedido['productos'] as $prod): ?>
                    <div class="pedido-item__producto">
                        <span><?= esc($prod['nombre']) ?> - <?= $prod['cantidad'] ?> <?= esc($prod['unidad_venta']) ?></span>
                        <span>$<?= number_format($prod['total'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Acciones (data-id / data-status los lee el JS con .data()) -->
                <div class="pedido-item__acciones">

                    <button type="button" class="btn btn-outline-dark btn-sm"
                            onclick="abrirModal('<?= base_url('editarpedido/'.$pedido['id_pedido']) ?>', 'Editar Pedido')">
                        <i class="fa fa-pencil"></i> Editar
                    </button>

                    <?php if (is_null($pedido['status']) || $pedido['status'] === 'solicitado'): ?>
                        <button class="btn btn-dark btn-sm btnAbrirConfirmar" data-id="<?= $pedido['id_pedido'] ?>">
                            Confirmar Pedido
                        </button>

                    <?php elseif ($pedido['status'] === 'confirmado'): ?>
                        <button class="btn btn-dark btn-sm btnCambiarStatus"
                                data-id="<?= $pedido['id_pedido'] ?>" data-status="en transito">
                            En Tránsito
                        </button>

                    <?php elseif ($pedido['status'] === 'en transito'): ?>
                        <button class="btn btn-dark btn-sm btnCambiarStatus"
                                data-id="<?= $pedido['id_pedido'] ?>" data-status="entregado">
                            Entregado
                        </button>

                    <?php elseif ($pedido['status'] === 'entregado' && puede('pedidos.pagos')): ?>
                        <button class="btn btn-primary btn-sm btnAbrirPago" data-id="<?= $pedido['id_pedido'] ?>">
                            Marcar Pagado
                        </button>

                    <?php elseif ($pedido['status'] === 'credito' && puede('pedidos.pagos')): ?>
                        <button class="btn btn-primary btn-sm btnLiquidarCredito" data-id="<?= $pedido['id_pedido'] ?>">
                            Liquidar Crédito
                        </button>
                    <?php endif; ?>

                    <?php if (!in_array($pedido['status'], ['pagado', 'cancelado']) && puede('pedidos.editar')): ?>
                        <button class="btn btn-danger btn-sm btnCancelar" data-id="<?= $pedido['id_pedido'] ?>">
                            Cancelar
                        </button>
                    <?php endif; ?>

                    <!-- Reutilizar: SIEMPRE disponible (también entregado, pagado o cancelado).
                         Abre el formulario de alta ya relleno; al guardar sale como pedido nuevo. -->
                    <?php if (puede('pedidos.crear')): ?>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="abrirModal('<?= base_url('copiar_pedido/'.$pedido['id_pedido']) ?>', 'Reutilizar pedido #<?= $pedido['id_pedido'] ?>')">
                            <i class="fa fa-copy"></i> Reutilizar
                        </button>
                    <?php endif; ?>
                </div>

            </article>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- MODAL GENERAL (alta, edición y reutilizar, dentro de un iframe) -->
<div class="modal fade" id="miModal">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeContenido" title="Formulario de pedido"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR: asignar repartidor -->
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
                        <option value="<?= $r['id_repartidor'] ?>"><?= esc($r['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarConfirmar">Confirmar Pedido</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PAGO: seleccionar método de pago -->
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
                    Si es <strong>crédito</strong>, el pedido quedará marcado como pendiente de pago.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarPago">Marcar Pagado</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    // ── Modal con iframe (alta / editar / reutilizar). Los formularios llaman a cerrarModal() al guardar ──
    const modal = new bootstrap.Modal(document.getElementById('miModal'));

    function abrirModal(url, titulo) {
        document.querySelector('#miModal .modal-title').textContent = titulo;
        $("#iframeContenido").attr("src", url);
        modal.show();
    }

    function cerrarModal() {
        modal.hide();
        $("#iframeContenido").attr("src", "");
        location.reload();
    }

    // ── Cambiar estado (En tránsito / Entregado) ──
    $(document).on('click', '.btnCambiarStatus', function () {
        cambiarStatus($(this).data('id'), $(this).data('status'));
    });

    // ── Cancelar pedido ──
    $(document).on('click', '.btnCancelar', function () {
        if (!confirm("¿Cancelar este pedido?")) return;
        cambiarStatus($(this).data('id'), 'cancelado');
    });

    // ── Liquidar crédito ──
    $(document).on('click', '.btnLiquidarCredito', function () {
        if (!confirm("¿Confirmar pago del crédito?")) return;
        cambiarStatus($(this).data('id'), 'pagado');
    });

    function cambiarStatus(idPedido, status) {
        $.ajax({
            url: "<?= base_url('actualizar_status') ?>",
            method: "POST",
            data: { id_pedido: idPedido, status: status },
            success: function (res) {
                if (res.trim() === "ok") location.reload();
                else alert("Error: " + res);
            },
            error: function (xhr) {
                alert("Error " + xhr.status + ": " + xhr.responseText);
            }
        });
    }

    // ── Modal confirmar (asignar repartidor) ──
    const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmar'));
    const modalPago      = new bootstrap.Modal(document.getElementById('modalPago'));

    $(document).on('click', '.btnAbrirConfirmar', function () {
        $('#confirmar_id_pedido').val($(this).data('id'));
        $('#confirmar_repartidor').val('');
        modalConfirmar.show();
    });

    $('#btnGuardarConfirmar').on('click', function () {
        const idPedido     = $('#confirmar_id_pedido').val();
        const idRepartidor = $('#confirmar_repartidor').val();

        if (!idRepartidor) {
            alert("Selecciona un repartidor.");
            return;
        }

        $.ajax({
            url: "<?= base_url('confirmar_pedido') ?>",
            method: "POST",
            data: { id_pedido: idPedido, id_repartidor: idRepartidor },
            success: function (res) {
                if (res.trim() === "ok") {
                    modalConfirmar.hide();
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

    // ── Modal pago (contado / crédito) ──
    $(document).on('click', '.btnAbrirPago', function () {
        $('#pago_id_pedido').val($(this).data('id'));
        $('#pago_metodo').val('');
        modalPago.show();
    });

    $('#btnGuardarPago').on('click', function () {
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
            success: function (res) {
                if (res.trim() === "ok") {
                    modalPago.hide();
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
<?= $this->endSection() ?>