<?php
/**
 * FORMULARIO del pedido (solo el HTML del <form>, sin <html>/<head>/<body>).
 * Lo comparten dos pantallas:
 *   - v_alta_pedidos.php  -> versión modal (iframe en Control de pedidos)
 *   - v_nuevo_pedido.php  -> versión página completa con menú lateral
 * Necesita: $clientes
 * Opcional: $precarga (al reutilizar un pedido; ver PedidoModel::getPrecargaCopia)
 * Su lógica JS vive en v_form_pedido_js.php
 */
?>
<?php $precarga = $precarga ?? null; ?>
<form id="formPedido" class="p-3">

    <?php if ($precarga): ?>
    <div class="alert alert-info py-2" role="alert">
        Copia del pedido <strong>#<?= (int) $precarga['id_origen'] ?></strong>.
        Revisa fecha, precios y totales: al guardar se creará un pedido nuevo.
    </div>
    <?php endif; ?>

    <!-- Aviso de éxito (solo en la versión de página completa) -->
    <div id="avisoPedido" class="alert alert-success py-2 d-none" role="alert"></div>

    <!-- CLIENTE Y FECHA -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="id_cliente" class="form-label fw-semibold">Cliente *</label>
            <select name="id_cliente" id="id_cliente" class="form-select" required>
                <option value="">Selecciona un cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id_cliente'] ?>"
                        <?= ($precarga && $cliente['id_cliente'] == $precarga['id_cliente']) ? 'selected' : '' ?>>
                        <?= esc(nombre_cliente_texto($cliente['nombre_completo'], $cliente['preferencial'] ?? 0)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="fecha" class="form-label fw-semibold">Fecha de entrega*</label>
            <input type="date" name="fecha_entrega" min="<?= date('Y-m-d') ?>" id="fecha_entrega" class="form-control"
                   value="<?= esc($precarga['fecha_entrega'] ?? '') ?>" required>
        </div>
    </div>

    <!-- DIRECCIÓN -->
    <div class="mb-3">
        <label for="direccion" class="form-label fw-semibold">Dirección de Entrega</label>
        <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Dirección para la entrega" maxlength="200"
               value="<?= esc($precarga['direccion'] ?? '') ?>">
    </div>

    <hr>

    <!-- PRODUCTOS DEL PEDIDO -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Productos del Pedido</h6>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarProducto">
                + Agregar Producto
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="tablaProductos">
                <thead class="table-light">
                    <tr>
                        <th>Producto *</th>
                        <th>Cantidad *</th>
                        <th>Unidad *</th>
                        <th>Precio Unitario *</th>
                        <th>Total del producto</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="productosBody"></tbody>
            </table>
        </div>

        <!-- Recalcular + Total final editable -->
        <div class="d-flex flex-wrap justify-content-end align-items-center gap-3 mb-3">
            <button type="button" class="btn btn-secondary btn-sm" id="btnCalcular">
                Recalcular
            </button>
            <span class="fw-bold fs-5">
                Total: $<input type="number" id="totalGeneral" name="total_general"
                            class="form-control d-inline-block w-auto text-end fw-bold"
                            min="0" step="any" value="<?= number_format($precarga['total_general'] ?? 0, 2, '.', '') ?>"
                            title="Puedes editar el total final (por ejemplo, precio preferencial)">
            </span>
        </div>

    <!-- BOTONES -->
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" onclick="cancelarPedido()">Cancelar</button>
        <input type="submit" class="btn btn-primary" value="Guardar Pedido">
    </div>

</form>