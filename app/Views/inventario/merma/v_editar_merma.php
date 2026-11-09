<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Merma</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_formulario.css') ?>?v=1">
</head>
<body>

<form id="editar_merma" method="post">

    <input type="hidden" name="id_merma" value="<?= esc($datos_merma['id_merma']) ?>">

    <label>Entrada Asociada *</label>
    <select name="id_entrada" id="id_entrada" required>
        <option value="">Selecciona una entrada</option>
        <?php foreach ($entradas as $entrada): ?>
            <option 
                value="<?= $entrada['id_entrada'] ?>"
                data-cantidad="<?= esc($entrada['cantidad_disponible']) ?>"
                data-equivalente="<?= esc($entrada['equivalente']) ?>"
                data-unidad="<?= esc($entrada['u_compra']) ?>"
                data-uventa="<?= esc($entrada['u_venta']) ?>"
                <?= $entrada['id_entrada'] == $datos_merma['id_entrada'] ? 'selected' : '' ?>>
                <?= esc($entrada['nombre']) ?> — <?= esc($entrada['fecha']) ?>
                (Disponible: <?= esc($entrada['cantidad_disponible'] * $entrada['equivalente']) ?> <?= esc($entrada['u_venta']) ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label>Cantidad *</label>
    <div style="display:flex; align-items:center; gap:8px;">
        <input 
            type="number" 
            name="cantidad" 
            id="cantidad"
            min="0.01" 
            step="0.01"
            max="999999"
            required
            value="<?= esc($datos_merma['cantidad'] * $datos_merma['equivalente']) ?>"
        />
        <span id="unidad_label" style="font-weight:bold; color:#555;"></span>
    </div>

    <label>Fecha *</label>
    <input type="date" name="fecha"
        value="<?= esc($datos_merma['fecha']) ?>"
        required
        title="Seleccione una fecha válida.">

    <label>Notas / Razón o motivo</label>
    <textarea name="notas"
        maxlength="200"
        oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');"
    ><?= esc($datos_merma['notas']) ?></textarea>

    <div class="btn-row">
        <input type="submit" value="Guardar">
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let equivalenteActual = <?= (float) $datos_merma['equivalente'] ?>;
    let maxKilosActual    = 0;

    // Inicializa la unidad al cargar según la entrada ya seleccionada
    (function init() {
        const selected = $("#id_entrada option:selected");
        const uventa   = selected.data("uventa") || "";
        const cantidad = parseFloat(selected.data("cantidad")) || 0;
        equivalenteActual = parseFloat(selected.data("equivalente")) || equivalenteActual;
        maxKilosActual    = cantidad * equivalenteActual;

        if (uventa) {
            $("#unidad_label").text(uventa);
            $("#cantidad").attr("placeholder", "Máx: " + maxKilosActual + " " + uventa);
        }
        calcularConversion();
    })();

    $("#id_entrada").on("change", function () {
        const selected    = $(this).find("option:selected");
        const uventa      = selected.data("uventa")      || "";
        const ucompra     = selected.data("unidad")      || "";
        const cantidad    = parseFloat(selected.data("cantidad")) || 0;
        equivalenteActual = parseFloat(selected.data("equivalente")) || 1;
        maxKilosActual    = cantidad * equivalenteActual;

        $("#cantidad").attr("max", maxKilosActual);

        if (uventa) {
            $("#unidad_label").text(uventa);
            $("#cantidad").attr("placeholder", "Máx: " + maxKilosActual + " " + uventa);
        } else {
            $("#unidad_label").text("");
            $("#cantidad").removeAttr("placeholder");
        }

        calcularConversion();
    });

    function calcularConversion() {
        const cantidadKg = parseFloat($("#cantidad").val()) || 0;
        const ucompra    = $("#id_entrada option:selected").data("unidad") || "";

        if (cantidadKg > 0 && equivalenteActual > 0 && ucompra) {
            const cajas = (cantidadKg / equivalenteActual).toFixed(4);
            $("#conversion_label").text(`≡ ${cajas} ${ucompra} se restarán de la entrada`);
        } else {
            $("#conversion_label").text("");
        }
    }

    $("#cantidad").on("input", calcularConversion);

    $("#editar_merma").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "<?= base_url('actualizar_merma') ?>",
            method: "POST",
            data: $(this).serialize(),
            success: function(res){
                window.parent.cerrarModal();
            }
        });
    });
</script>

</body>
</html>