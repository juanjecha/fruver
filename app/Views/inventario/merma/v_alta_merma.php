<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta Merma</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_formulario.css') ?>?v=1">
</head>
<body>

<form id="formProducto">

    <label>Entrada Asociada *</label>
    <select name="id_entrada" id="id_entrada" required>
        <option value="">Selecciona una entrada</option>
        <?php foreach ($entradas as $entrada): ?>
        <option 
            value="<?= $entrada['id_entrada'] ?>"
            data-cantidad="<?= esc($entrada['cantidad_disponible']) ?>"
            data-unidad="<?= esc($entrada['u_compra']) ?>"
            data-uventa="<?= esc($entrada['u_venta']) ?>"
            data-equivalente="<?= esc($entrada['equivalente']) ?>"
        >
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
            oninput="this.value=this.value.replace(/[^0-9]/g,''); calcularConversion();"
        />
        <span id="unidad_label" style="font-weight:bold; color:#555;"></span>
    </div>

    <label>Fecha *</label>
    <input type="date" name="fecha" required title="Seleccione una fecha válida." /><br>

    <label>Notas / Razón o motivo</label>
    <textarea 
        name="notas"
        maxlength="200"
        title="Solo letras. Máximo 200 caracteres."
        oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóú\s]/g,'');"
    ></textarea><br>


    <input type="hidden" name="u_compra" id="u_compra">
    <input type="hidden" name="u_venta"  id="u_venta">

    <input type="submit" value="Guardar" /><br>
</form>

<script>
    let equivalenteActual = 1;
    let maxKilosActual    = 0

    $("#id_entrada").on("change", function () {
        const selected    = $(this).find("option:selected");
        const unidad      = selected.data("unidad")      || ""; //u_compra
        const uventa      = selected.data("uventa")       || "";
        const cantidad    = selected.data("cantidad")     || "";
        equivalenteActual = selected.data("equivalente")  || 1;
        maxKilosActual    = cantidad * equivalenteActual;

        // Llena los campos ocultos que se envían al servidor
        $("#u_compra").val(unidad);
        $("#u_venta").val(uventa);
        $("#cantidad").attr("max", maxKilosActual);

        if (uventa) {
            $("#unidad_label").text(uventa);
            $("#cantidad").attr("placeholder", "Máx: " + maxKilosActual + " " + uventa);
        } else {
            $("#unidad_label").text("");
            $("#cantidad").removeAttr("placeholder");
        }

        // Recalcula la conversión si ya había cantidad escrita
        calcularConversion();
    });

    /**
     * Muestra en tiempo real cuántas unidades de venta
     * equivalen a la cantidad de compra ingresada.
     * Ej: 2 cajas × 10 = 20 kg
     */
    function calcularConversion() {
        const cantidadKg = parseFloat($("#cantidad").val()) || 0;
        const uventa     = $("#u_venta").val();
        const ucompra    = $("#u_compra").val();

        if (cantidadKg > 0 && equivalenteActual > 0 && ucompra) {
            const cajas = (cantidadKg / equivalenteActual).toFixed(4);
            $("#conversion_label").text(
                `≡ ${cajas} ${ucompra} se restarán de la entrada`
            );
        } else {
            $("#conversion_label").text("");
        }
    }

    $("#formProducto").submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: "<?= base_url('guardar_merma') ?>",
            method: "POST",
            data: $(this).serialize(),
            success: function (res) {
            res = res.trim();
            if (res === "ok") {
                window.parent.cerrarModal();
            } else if (res.startsWith("excede")) {
                const partes = res.split("|");
                alert(`Cantidad excede lo disponible.\nDisponible: ${partes[1]} ${partes[2]}`);
            } else {
                alert("Error: " + res);
            }
        }
        });
    });
</script>

</body>
</html>