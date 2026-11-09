<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Entrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_formulario.css') ?>?v=1">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="p-3">

<?php
    // PUNTO 15 - Repetir entrada: $base trae la entrada a copiar (o null si es una entrada nueva)
    $base = $base ?? null;
    $v = function (string $campo) use ($base) { return $base[$campo] ?? ''; };
?>

<form id="formEntradas">

    <?php if ($base): ?>
    <div class="alert alert-warning py-2 small mb-3">
        <b>Repitiendo entrada</b> del <?= esc($base['fecha']) ?>. Revisa la cantidad y el precio y guarda.
    </div>
    <?php endif; ?>

    <div class="row g-3">

        <!-- Producto -->
        <div class="col-md-6">
            <label class="form-label">Producto *</label>
            <select name="id_producto2" class="form-select" required>
                <option value="">Selecciona un producto</option>
                <?php foreach($productos as $p): ?>
                    <option value="<?= $p['id_producto'] ?>" <?= (string) $v('id_producto2') === (string) $p['id_producto'] ? 'selected' : '' ?>><?= esc($p['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Cantidad recibida -->
        <div class="col-md-6">
            <label class="form-label">Cantidad recibida *</label>
            <input type="number" step="any" min="0" name="cantidad"
                   class="form-control" required value="<?= esc($v('cantidad'), 'attr') ?>">
        </div>

        <!-- Precio de compra -->
        <div class="col-md-6">
            <label class="form-label">Precio de compra  *</label>
            <input type="number" step="any" min="0" name="precio_compra"
                   class="form-control" required value="<?= esc($v('precio_compra'), 'attr') ?>">
        </div>

        <!-- Fecha de entrada -->
        <div class="col-md-6">
            <label class="form-label">Fecha de entrada *</label>
            <input type="date" name="fecha" id="fecha"
                   class="form-control" required
                   value="<?= date('Y-m-d') ?>">
        </div>

        <!-- Fecha de caducidad (calculada) -->
        <div class="col-12">
            <label class="form-label text-muted">Fecha de Expiración</label>
            <input type="date" name="fecha_caducidad" id="fecha_caducidad"
                class="form-control"
                value="">
            <small class="text-muted">
                Se recalcula automáticamente si cambias la fecha de entrada,o edítala manualmente
            </small>
        </div>

        <hr>
        <h6 class="mb-0"><h4>Seleccion de unidades</h4></h6>

        <!-- Unidad de compra -->
        <div class="col-md-4">
            <label class="form-label">Unidad de Compra</label>
            <select name="u_compra" id="u_compra" class="form-select" required>
                <option value="">Selecciona unidad</option>
                <option value="Cajas" <?= $v('u_compra') === 'Cajas' ? 'selected' : '' ?>>Cajas</option>
                <option value="Mazo" <?= $v('u_compra') === 'Mazo' ? 'selected' : '' ?>>Mazo</option>
                <option value="Arpilla" <?= $v('u_compra') === 'Arpilla' ? 'selected' : '' ?>>Arpilla</option>
                <option value="Kilos" <?= $v('u_compra') === 'Kilos' ? 'selected' : '' ?>>Kilos</option>
                <option value="Unidad" <?= $v('u_compra') === 'Unidad' ? 'selected' : '' ?>>Unidad</option>
            </select>
        </div>

        <!-- Unidad de venta -->
        <div class="col-md-4">
            <label class="form-label">Unidad de Venta</label>
            <select name="u_venta" id="u_venta" class="form-select" required>
                <option value="">Selecciona unidad</option>
                <option value="Kilos" <?= $v('u_venta') === 'Kilos' ? 'selected' : '' ?>>Kilos</option>
                <option value="Domos" <?= $v('u_venta') === 'Domos' ? 'selected' : '' ?>>Domos</option>
                <option value="Ramos" <?= $v('u_venta') === 'Ramos' ? 'selected' : '' ?>>Ramos</option>
                <option value="Ramos">Unidad</option>
            </select>
        </div>

        <!-- Equivalente: cuántas unidades de venta tiene 1 unidad de compra -->
        <div class="col-md-4">
            <label class="form-label">Valor de Conversión</label>
            <input type="number" step="any" min="0" name="equivalente" id="equivalente"
                   class="form-control" placeholder="Ej: 50"
                   required value="<?= esc($v('equivalente'), 'attr') ?>">
            <small id="textoEquivalente" class="text-muted">
                Ej: Si es "Cajas", indica cuántos kilos o domos tiene cada caja
            </small>
        </div>

        <!-- Conversión calculada (cantidad * equivalente) -->
        <div class="col-12">
            <label class="form-label text-muted">Total de piezas para venta</label>
            <input type="text" id="conversion_preview" class="form-control" readonly disabled>
            <input type="hidden" name="conversion" id="conversion_hidden">
            <small class="text-muted">Cantidad recibida × Valor de conversión</small>
        </div>

        

    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-secondary"
                onclick="window.parent.cerrarModal()">Cancelar</button>
        <button type="submit" class="btn btn-dark">Registrar</button>
    </div>

</form>

<script>
    // Previsualizar fecha de caducidad (+5 días)
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
    actualizarCaducidad(); // Ejecutar al cargar con la fecha de hoy

    // Calcular conversión total en tiempo real
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

    // Texto de ayuda dinámico según unidades seleccionadas
    function actualizarTextoAyuda() {
        const uc = $('#u_compra').val();
        const uv = $('#u_venta').val();
        if (uc && uv) {
            $('#textoEquivalente').text(
                `¿Cuántos ${uv} tiene 1 ${uc}? Ej: 1 ${uc} = X ${uv}`
            );
        }
    }

    $('[name="cantidad"], #equivalente, #u_compra, #u_venta').on('input change', function() {
        calcularConversion();
        actualizarTextoAyuda();
    });

    // Si se está repitiendo una entrada, calcula el total desde el inicio
    calcularConversion();
    actualizarTextoAyuda();

    // Envío del formulario
    $("#formEntradas").submit(function(e) {
        e.preventDefault();

        // Validar que seleccionó producto
        if (!$('[name="id_producto2"]').val()) {
            alert("Por favor selecciona un producto");
            return;
        }

        $.ajax({
            url: "<?= base_url('guardar_entrada') ?>",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                if (res.trim() === "ok") {
                    window.parent.cerrarModal();
                } else {
                    alert("Error: " + res);
                }
            }
        });
    });

    
</script>
</body>
</html>