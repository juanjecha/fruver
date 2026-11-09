<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Entrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_formulario.css') ?>?v=1">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="p-3">

<form id="formEditarEnt">
    <input type="hidden" name="id_entrada" value="<?= $datos_entrada['id_entrada'] ?>">

    <div class="row g-3">

        <!-- Producto -->
        <div class="col-md-6">
            <label class="form-label">Producto *</label>
            <select name="id_producto2" class="form-select" required>
                <option value="">Selecciona un producto</option>
                <?php foreach($productos as $p): ?>
                    <option value="<?= $p['id_producto'] ?>"
                        <?= $datos_entrada['id_producto2'] == $p['id_producto'] ? 'selected' : '' ?>>
                        <?= esc($p['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Cantidad -->
        <div class="col-md-6">
            <label class="form-label">Cantidad recibida *</label>
            <input type="number" step="0.01" min="0" name="cantidad"
                   class="form-control" required
                   value="<?= $datos_entrada['cantidad'] ?>">
        </div>

        <!-- Precio de compra -->
        <div class="col-md-6">
            <label class="form-label">Precio de compra *</label>
            <input type="number" step="0.01" min="0" name="precio_compra"
                   class="form-control" required
                   value="<?= $datos_entrada['precio_compra'] ?>">
        </div>

        <!-- Fecha de entrada -->
        <div class="col-md-6">
            <label class="form-label">Fecha de entrada *</label>
            <input type="date" name="fecha" id="fecha"
                   class="form-control" required
                   value="<?= $datos_entrada['fecha'] ?>">
        </div>

        <!-- Fecha de caducidad-->
        <div class="col-12">
            <label class="form-label text-muted">Fecha de Expiración</label>
            <input type="date" name="fecha_caducidad" id="fecha_caducidad"
                class="form-control"
                value="<?= $datos_entrada['fecha_caducidad'] ?>">
            <small class="text-muted">
                Se recalcula automáticamente si cambias la fecha de entrada,o edítala manualmente
            </small>
        </div>

        <hr>
        <h6 class="mb-0">Unidad de Medida y Conversión</h6>

        <!-- Unidad de compra -->
        <div class="col-md-4">
            <label class="form-label">Unidad de Medida</label>
            <select name="u_compra" id="u_compra" class="form-select" required>
                <?php
                $opciones_compra = ['Cajas','Mazo','Arpilla','Kilos','Unidad'];
                foreach($opciones_compra as $op): ?>
                    <option value="<?= $op ?>"
                        <?= $datos_entrada['u_compra'] === $op ? 'selected' : '' ?>>
                        <?= $op ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Unidad de venta -->
        <div class="col-md-4">
            <label class="form-label">Unidad de Conversión</label>
            <select name="u_venta" id="u_venta" class="form-select" required>
                <?php
                $opciones_venta = ['Kilos','Domos','Ramos'];
                foreach($opciones_venta as $op): ?>
                    <option value="<?= $op ?>"
                        <?= $datos_entrada['u_venta'] === $op ? 'selected' : '' ?>>
                        <?= $op ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Equivalente -->
        <div class="col-md-4">
            <label class="form-label">Valor de Conversión</label>
            <input type="number" step="0.01" min="0" name="equivalente" id="equivalente"
                   class="form-control" required
                   value="<?= $datos_entrada['equivalente'] ?>">
            <small id="textoEquivalente" class="text-muted">
                ¿Cuántos <span id="spanVenta"><?= $datos_entrada['u_venta'] ?></span>
                tiene 1 <span id="spanCompra"><?= $datos_entrada['u_compra'] ?></span>?
            </small>
        </div>

        <!-- Conversión total (calculada) -->
        <div class="col-12">
            <label class="form-label text-muted">Conversión total (calculada)</label>
            <input type="text" id="conversion_preview" class="form-control" readonly disabled
                   value="<?= $datos_entrada['conversion'] ?>">
            <input type="hidden" name="conversion" id="conversion_hidden"
                   value="<?= $datos_entrada['conversion'] ?>">
            <small class="text-muted">Cantidad recibida × Valor de conversión</small>
        </div>

        <!-- Cantidad vendida -->
        <div class="col-md-6">
            <label class="form-label">Cantidad vendida</label>
            <input type="number" step="0.01" min="0" name="cantidad_vendida"
                   class="form-control"
                   value="<?= $datos_entrada['cantidad_vendida'] ?>">
        </div>

    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-secondary"
                onclick="window.parent.cerrarModal()">Cancelar</button>
        <button type="submit" class="btn btn-dark">Guardar cambios</button>
    </div>

</form>

<script>
    // Calcular conversión en tiempo real
    function calcularConversion() {
        const cantidad  = parseFloat($('[name="cantidad"]').val()) || 0;
        const equiv     = parseFloat($('#equivalente').val()) || 0;
        const uCompra   = $('#u_compra').val();
        const uVenta    = $('#u_venta').val();
        const resultado = cantidad * equiv;

        $('#conversion_preview').val(
            uCompra && uVenta
                ? `${cantidad} ${uCompra} × ${equiv} = ${resultado.toFixed(2)} ${uVenta}`
                : resultado.toFixed(2)
        );
        $('#conversion_hidden').val(resultado.toFixed(2));
    }

    // Texto de ayuda dinámico
    function actualizarTextoAyuda() {
        const uc = $('#u_compra').val();
        const uv = $('#u_venta').val();
        if (uc && uv) {
            $('#spanCompra').text(uc);
            $('#spanVenta').text(uv);
        }
    }

    // Ejecutar al cargar para mostrar el valor guardado
    calcularConversion();

    $('[name="cantidad"], #equivalente, #u_compra, #u_venta').on('input change', function() {
        calcularConversion();
        actualizarTextoAyuda();
    });

    // Envío
    $("#formEditarEnt").submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "<?= base_url('actualizarentrada') ?>",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                if (res.trim() === "ok") {
                    window.parent.cerrarModal();
                } else {
                    alert("Error al guardar: " + res);
                }
            }
        });
    });

    function actualizarCaducidad() {
            const fecha = document.getElementById('fecha').value;
            if (fecha) {
                const d = new Date(fecha + 'T00:00:00');
                d.setDate(d.getDate() + 5);
                document.getElementById('fecha_caducidad').value =
                    d.toISOString().split('T')[0];
            }
        }
        document.getElementById('fecha').addEventListener('change', actualizarCaducidad);
</script>
</body>
</html>