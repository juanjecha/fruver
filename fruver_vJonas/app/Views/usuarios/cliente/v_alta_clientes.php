<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta Cliente</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('css/comun/v_formulario.css') ?>?v=1">
</head>

<body>

    <form id="formCliente">
        <div class="campo">
        <label>Nombre*</label>
            <input type="text" name="nombre" 
                maxlength="100" required
                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');" />
        </div>

        <label>Apellido Paterno*</label>
        <input type="text" name="ap_p" 
               maxlength="100" required
               pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
               oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');" />

        <label>Apellido Materno</label>
        <input type="text" name="ap_m" 
               maxlength="100"
               pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
               oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');" />

        <div class="campo">
            <label>Teléfono*</label>
            <input type="num" name="tel" 
                minlength="10"
                step="1"
                maxlength="10" required
                placeholder="ej. 2765789107"
                pattern="\d{10}"
                oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                min="0"
                onkeydown="return event.key !== '-'" />
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="preferencial" id="preferencial" value="1">
            <label class="form-check-label" for="preferencial">
                <span class="estrella">★</span> Cliente preferencial
            </label>
        </div>

        <hr>

        <div class="campo">
            <label for="correo">Correo (opcional)</label>
            <input type="email" id="correo" name="correo" maxlength="150"
                autocomplete="off" placeholder="cliente@correo.com">
        </div>

        <div class="campo">
            <label for="password">Contraseña (opcional)</label>
            <input type="text" id="password" name="password" minlength="10" maxlength="72"
                autocomplete="off" spellcheck="false" placeholder="Mínimo 10 caracteres">
        </div>

        <div class="alert alert-info">
            Sin crear la cuenta, el cliente <strong>no podrá ver su información desde la app</strong>.
            Si capturas correo y contraseña, la cuenta se crea al guardar.
        </div>

        <input type="submit" value="Guardar Cliente"/>
    </form>

        <script>
        const MENSAJES = {
            duplicado:         'Este cliente ya se encuentra registrado.',
            cuenta_incompleta: 'Para crear la cuenta captura correo y contraseña.',
            correo_invalido:   'El correo no es válido.',
            correo_repetido:   'Ese correo ya tiene una cuenta.',
            password_invalida: 'La contraseña debe tener entre 10 y 72 caracteres.',
            error_guardar:     'No se pudo guardar. Revisa que la llave de cifrado esté configurada (php spark key:generate).'
        };

        // Si se llena uno de los dos campos de cuenta, el otro pasa a ser obligatorio
        $('#correo, #password').on('input', function () {
            const alguno = $('#correo').val().trim() !== '' || $('#password').val().trim() !== '';
            $('#correo, #password').prop('required', alguno);
        });

        $("#formCliente").submit(function(e){
            e.preventDefault();

            $.ajax({
                url: "<?= base_url('guarda_cliente') ?>",
                method: "POST",
                data: $(this).serialize(),
                success: function(res){
                    res = res.trim();
                    if (res === "ok") {
                        window.parent.cerrarModal();
                    } else {
                        alert(MENSAJES[res] || "Error al guardar: " + res);
                    }
                },
                error: function(xhr){
                    alert("Error " + xhr.status + " al guardar. Revisa el servidor.");
                }
            });
        });
    </script>

</body>
</html>