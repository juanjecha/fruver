<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta de vendedor avanzado</title>
    <link rel="stylesheet" href="<?= base_url('css/comun/v_formulario.css') ?>?v=1">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <form id="formVendedor" class="form-vendedor" autocomplete="off">

        <div class="campo">
            <label for="nombre">Nombre*</label>
            <input type="text" id="nombre" name="nombre" maxlength="100" required
                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" placeholder="ej. Juan Pérez López"
                   oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');">
        </div>

        <div class="campo">
            <label for="tel">Teléfono*</label>
            <input type="tel" id="tel" name="tel" maxlength="10" required
                   inputmode="numeric" pattern="\d{10}" placeholder="ej. 2765789107"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            <span class="ayuda">10 dígitos, sin espacios.</span>
        </div>

        <div class="campo">
            <label for="correo">Correo*</label>
            <input type="email" id="correo" name="correo" maxlength="150" required
                   autocomplete="off" placeholder="vendedor@correo.com">
        </div>

        <div class="campo">
            <label for="password">Contraseña*</label>
            <input type="text" id="password" name="password" minlength="10" maxlength="72" required
                   autocomplete="off" spellcheck="false" placeholder="Mínimo 10 caracteres">
        </div>

        <input type="submit" value="Crear cuenta">
    </form>

    <script>
        const MENSAJES = {
            cuenta_incompleta: 'Captura correo y contraseña.',
            correo_invalido:   'El correo no es válido.',
            correo_repetido:   'Ese correo ya tiene una cuenta.',
            password_invalida: 'La contraseña debe tener entre 10 y 72 caracteres.',
            error_guardar:     'No se pudo guardar. Revisa que la tabla usuario tenga la columna "tel" y que la llave de cifrado esté configurada (php spark key:generate).'
        };

        $("#formVendedor").submit(function (e) {
            e.preventDefault();
            const boton = $(this).find('[type=submit]').prop('disabled', true);

            $.ajax({
                url: "<?= base_url('guardar_vendedor') ?>",
                method: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    res = res.trim();
                    if (res === "ok") {
                        window.parent.cerrarModal();
                    } else {
                        alert(MENSAJES[res] || "Revisa los datos: nombre, teléfono (10 dígitos), correo y contraseña son obligatorios.");
                    }
                },
                error: function (xhr) {
                    alert("Error " + xhr.status + " al guardar.");
                },
                complete: function () {
                    boton.prop('disabled', false);
                }
            });
        });
    </script>
</body>
</html>
