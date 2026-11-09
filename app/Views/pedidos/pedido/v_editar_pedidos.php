<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>editar_pedido</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/pedido/v_editar_pedidos.css') ?>">
</head>

<body>
<form id="formEditarPedido" class="p-3">

    <!-- ID oculto del pedido -->
    <input type="hidden" name="id_pedido" value="<?= esc($pedido['id_pedido']) ?>">

    <!-- CLIENTE Y FECHA -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="id_cliente" class="form-label fw-semibold">Cliente *</label>
            <select name="id_cliente" id="id_cliente" class="form-select" required>
                <option value="">Selecciona un cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id_cliente'] ?>"
                        <?= ($cliente['id_cliente'] == $pedido['id_cliente1']) ? 'selected' : '' ?>>
                        <?= esc(nombre_cliente_texto($cliente['nombre_completo'], $cliente['preferencial'] ?? 0)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="fecha_entrega" class="form-label fw-semibold">Fecha de entrega *</label>
            <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control"
                   value="<?= esc($pedido['fecha_entrega']) ?>" required>
        </div>
    </div>

    <!-- DIRECCIÓN -->
    <div class="mb-3">
        <label for="direccion" class="form-label fw-semibold">Dirección de Entrega</label>
        <input type="text" name="direccion" id="direccion" class="form-control"
               placeholder="Dirección para la entrega"
               value="<?= esc($pedido['direccion_pedido']) ?>">
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
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="productosBody"></tbody>
        </table>
    </div>

    <!-- Botón calcular + Total editable -->
    <div class="d-flex justify-content-end align-items-center gap-3 mb-3">
        <button type="button" class="btn btn-secondary btn-sm" id="btnCalcular">
            Calcular Total
        </button>
        <span class="fw-bold fs-5">
            Total: $<input type="number" id="totalGeneral" name="total_general"
                        class="form-control d-inline-block w-auto text-end fw-bold"
                        min="0" step="any"
                        value="<?= esc($pedido['total_general']) ?>"
                        title="Puedes editar el total si aplica precio preferencial">
        </span>
    </div>

    <!-- BOTONES -->
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" onclick="window.parent.cerrarModal()">Cancelar</button>
        <input type="submit" class="btn btn-primary" value="Actualizar Pedido">
    </div>

</form>

<script>
// Datos enviados desde PHP convertidos a arrays de JavaScript
const productos         = <?= json_encode($productos) ?>;
const unidades          = ['caja','mazo','arpilla','kilos','ramos'];
const productosExistentes = <?= json_encode($detalle_pedido) ?>; // array de líneas guardadas en el pedido
let filaIndex           = 0; // Contador global para asignar un índice único a cada fila generada

// forEach recorre cada elemnto del array, y al cargar la página, pre-rellena la tabla con los productos que ya tiene el pedido
productosExistentes.forEach(item => {
    agregarFila(item);
});

document.getElementById('btnAgregarProducto').addEventListener('click', () => agregarFila());

// Calcular total al presionar botón
document.getElementById('btnCalcular').addEventListener('click', function () {
    let total = 0;

    document.querySelectorAll('#productosBody tr').forEach(fila => { //// querySelectorAll('#productosBody tr') → selecciona todas las filas <tr> dentro de la tabla
        const cantidad = parseFloat(fila.querySelector('.inp-cantidad').value) || 0; // // querySelector busca el primer elemento que coincida dentro de la fila
        const precio   = parseFloat(fila.querySelector('.inp-precio').value)   || 0;
        const subtotal = (cantidad * precio).toFixed(2);

        fila.querySelector('.subtotal-fila').textContent = subtotal;
        total += parseFloat(subtotal);
    });

    document.getElementById('totalGeneral').value = total.toFixed(2);
});

/**
 * agregarFila(datos)
 * Si se pasa un objeto con datos, los pre-rellena (modo edición).
 * Si no se pasa nada, crea una fila vacía (modo agregar).
 */
function agregarFila(datos = null) {
    const tbody = document.getElementById('productosBody');
    const index = filaIndex++; // Incrementa el contador para que cada fila tenga un ID único

    // recorre el array de productos y genera un <option> por cada uno
    let optsProducto = `<option value="">Selecciona...</option>`;
    productos.forEach(p => {
        const selected = datos && datos.id_producto == p.id_producto ? 'selected' : '';
        optsProducto += `<option value="${p.id_producto}" ${selected}>${p.nombre}</option>`;
    });

    // Opciones de unidad
    // Si 'datos' tiene el mismo id_producto, le agrega 'selected' para que aparezca seleccionado
    let optsUnidad = `<option value="">Selecciona...</option>`;
    unidades.forEach(u => {
        const selected = datos && datos.unidad_venta === u ? 'selected' : '';
        optsUnidad += `<option value="${u}" ${selected}>${u.charAt(0).toUpperCase() + u.slice(1)}</option>`; //charAt obtiene el primer caracter de la caddena, toUpperCase lo convierte en mayustcula y u.slice ontiene el resto de la caddena
    });

    // Si hay datos existentes los usa, si no deja los campos vacíos o en cero
    const cantidad  = datos ? datos.cantidad   : '';
    const precio    = datos ? datos.precio_venta : '';
    const subtotal  = datos ? (parseFloat(datos.cantidad) * parseFloat(datos.precio_venta)).toFixed(2) : '0.00';

    //// ID del detalle del pedido, necesario para que el servidor sepa si actualizar o insertar la línea
    const idDetalle = datos && datos.id_producto_pedido ? datos.id_producto_pedido : '';


    const fila = `
    <tr id="fila_${index}">
        <td>
            <input type="hidden" name="productos[${index}][id_detalle]" value="${idDetalle}">
            <select name="productos[${index}][id_producto]" class="form-select form-select-sm" required>
                ${optsProducto}
            </select>
        </td>
        <td>
            <input type="number" name="productos[${index}][cantidad]"
                   class="form-control form-control-sm inp-cantidad"
                   min="0.01" step="0.01" placeholder="0"
                   value="${cantidad}" required>
        </td>
        <td>
            <select name="productos[${index}][unidad_venta]" class="form-select form-select-sm" required>
                ${optsUnidad}
            </select>
        </td>
        <td>
            <input type="number" name="productos[${index}][precio_venta]"
                   class="form-control form-control-sm inp-precio"
                   min="0" step="0.01" placeholder="0.00"
                   value="${precio}" required>
        </td>
        <td class="text-center">
            $<span class="subtotal-fila fw-semibold">${subtotal}</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm"
                    onclick="eliminarFila(${index})">✕</button>
        </td>
    </tr>`;

    tbody.insertAdjacentHTML('beforeend', fila); // inserta el HTML al final del tbody sin borrar lo que ya hay
}


// Busca la fila por su ID en el y la elimina
// Se llama desde el botón ✕ de cada fila
function eliminarFila(index) {
    const fila = document.getElementById(`fila_${index}`);
    if (fila) fila.remove();
}

// .submit intercepta el envío del formulario
// e.preventDefault() cancela el comportamiento nativo (recargar página) para manejarlo con AJAX
$("#formEditarPedido").submit(function (e) {
    e.preventDefault();

    if (document.querySelectorAll('#productosBody tr').length === 0) {// querySelectorAll cuenta las filas; si no hay ninguna, detiene el envío
        alert("Agrega al menos un producto al pedido.");
        return;
    }

    $.ajax({
        url: "<?= base_url('actualizar_pedido') ?>",
        method: "POST",
        data: $(this).serialize(), // .serialize() convierte todos los inputs del formulario en query string (key=value&key=value...)
        success: function (res) {
            console.log("RESPUESTA:", res);
            if (res.trim() === "ok") { // .trim() elimina espacios o saltos de línea al inicio y final de la respuesta
                window.parent.cerrarModal();
            } else {
                alert("Error: " + res);
            }
        },
        error: function (xhr, status, err) {
            console.log("STATUS:", xhr.status);
            console.log("RESPUESTA SERVIDOR:", xhr.responseText);
            alert("Error " + xhr.status + ": " + xhr.responseText);
        }
    });
});
</script>

</body>
</html>
