<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Repartidor</title>
    <link rel="stylesheet" href="<?= base_url('css/comun/v_formulario.css') ?>?v=1">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php $r = $datos_repartidor; ?>
    <form id="formEditar" class="form-repartidor">
        <input type="hidden" name="id_repartidor" value="<?= esc($r['id_repartidor'], 'attr') ?>">

        <div class="campo">
            <label for="nombre">Nombre*</label>
            <input type="text" id="nombre" name="nombre" value="<?= esc($r['nombre'], 'attr') ?>"
                   maxlength="100" required
                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                   oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');">
        </div>

        <div class="fila">
            <div class="campo">
                <label for="ap_p">Apellido Paterno*</label>
                <input type="text" id="ap_p" name="ap_p" value="<?= esc($r['ap_p'], 'attr') ?>"
                       maxlength="100" required
                       pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                       oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');">
            </div>
            <div class="campo">
                <label for="ap_m">Apellido Materno</label>
                <input type="text" id="ap_m" name="ap_m" value="<?= esc($r['ap_m'] ?? '', 'attr') ?>"
                       maxlength="100"
                       pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                       oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');">
            </div>
        </div>

        <div class="campo">
            <label for="tel">Teléfono*</label>
            <input type="tel" id="tel" name="tel" value="<?= esc($r['tel'], 'attr') ?>"
                   maxlength="10" required inputmode="numeric" pattern="\d{10}"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            <span class="ayuda">10 dígitos, sin espacios.</span>
        </div>

        <div class="campo">
            <label for="direccion">Dirección*</label>
            <input type="text" id="direccion" name="direccion" value="<?= esc($r['direccion'], 'attr') ?>"
                   maxlength="500" required
                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s#.,\-]+"
                   oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ0-9\s#.,\-]/g,'');">
        </div>

        <div class="campo">
            <label for="notas">Notas</label>
            <!-- En un textarea el texto va entre las etiquetas, no en value="" -->
            <textarea id="notas" name="notas" maxlength="500"><?= esc($r['notas'] ?? '') ?></textarea>
        </div>

        <hr>

        <?php if (! empty($cuenta)): ?>
            <div class="alert alert-success">
                <strong>Cuenta activa</strong><br>
                Correo: <?= esc($cuenta['correo']) ?>
                <?php if ($cuenta['password'] !== null): ?>
                    <br>Contraseña asignada: <code><?= esc($cuenta['password']) ?></code>
                <?php elseif (puede('usuarios.ver_password')): ?>
                    <br><small>Contraseña no disponible (la cuenta se creó antes de este cambio).</small>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="campo">
                <label for="correo">Correo (opcional)</label>
                <input type="email" id="correo" name="correo" maxlength="150"
                    autocomplete="off" placeholder="repartidor@correo.com">
            </div>

            <div class="campo">
                <label for="password">Contraseña (opcional)</label>
                <input type="text" id="password" name="password" minlength="10" maxlength="72"
                    autocomplete="off" spellcheck="false" placeholder="Mínimo 10 caracteres">
            </div>
        <?php endif; ?>

        <div class="campo">
            <label for="tipo_cuenta">Tipo de cuenta</label>
            <?php $tipo = $cuenta['rol'] ?? 'repartidor'; ?>
            <select id="tipo_cuenta" name="tipo_cuenta">
                <option value="repartidor" <?= $tipo === 'repartidor' ? 'selected' : '' ?>>Repartidor (solo ve sus pedidos asignados)</option>
                <option value="repartidor_avanzado" <?= $tipo === 'repartidor_avanzado' ? 'selected' : '' ?>>Repartidor avanzado (también crea pedidos)</option>
            </select>
        </div>

        <?php if (empty($cuenta)): ?>
            <div class="alert alert-info">
                Sin crear la cuenta, el repartidor <strong>no podrá ver su información desde la app</strong>.
            </div>
        <?php endif; ?>

        <input type="submit" value="Guardar Cambios">
    </form>

    <script>
        const MENSAJES = {
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

        $("#formEditar").submit(function (e) {
            e.preventDefault();
            const boton = $(this).find('[type=submit]').prop('disabled', true);

            $.ajax({
                url: "<?= base_url('actualizarepart') ?>",
                method: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    res = res.trim();
                    if (res === "ok") {
                        window.parent.cerrarModal();
                    } else {
                        alert(MENSAJES[res] || "Revisa los datos: nombre, apellido paterno, teléfono (10 dígitos) y dirección son obligatorios.");
                    }
                },
                error: function (xhr) {
                    alert("Error " + xhr.status + " al guardar los cambios.");
                },
                complete: function () {
                    boton.prop('disabled', false);
                }
            });
        });
    </script>
</body>
</html>
