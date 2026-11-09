<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_formulario.css') ?>?v=1">
</head>

<body>
    <form id="formEditar" method="post">
        <input type="hidden" name="id_cliente" value='<?= $datos_cliente["id_cliente"] ?>'>

        <label>Nombre*</label>
        <input type="text" name="nombre" value='<?= $datos_cliente["nombre"] ?>' 
               required maxlength="100" 
               pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
               oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');" />

        <label>Apellido Paterno*</label>
        <input type="text" name="ap_p" value='<?= $datos_cliente["ap_p"] ?>' 
               required maxlength="100"
               pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
               oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');" />

        <label>Apellido materno</label>
        <input type="text" name="ap_m" value='<?= $datos_cliente["ap_m"] ?>' 
               maxlength="100"
               pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
               oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');" />

        <label>Teléfono*</label>
        <input type="text" name="tel" value='<?= $datos_cliente["tel"] ?>' 
               required maxlength="10"
               pattern="\d{10}"
               oninput="this.value=this.value.replace(/[^0-9]/g,'');" />

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="preferencial" id="preferencial" value="1"
                <?= !empty($datos_cliente['preferencial']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="preferencial">
                <span class="estrella">★</span> Cliente preferencial
            </label>
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
                    autocomplete="off" placeholder="cliente@correo.com">
            </div>

            <div class="campo">
                <label for="password">Contraseña (opcional)</label>
                <input type="text" id="password" name="password" minlength="10" maxlength="72"
                    autocomplete="off" spellcheck="false" placeholder="Mínimo 10 caracteres">
            </div>

            <div class="alert alert-info">
                Sin crear la cuenta, el cliente <strong>no podrá ver su información desde la app</strong>.
            </div>
        <?php endif; ?>
        <div class="btn-row">
            <input type="submit" value="Guardar Cambios" >
        </div>    
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $("#formEditar").submit(function(e){
            e.preventDefault();

            $.ajax({
                url: "<?= base_url('actualizac') ?>",
                method: "POST",
                data: $(this).serialize(),
                success: function(res){
                    res = res.trim();
                    if (res === "ok") {
                        window.parent.cerrarModal();
                    } else {
                        alert(MENSAJES[res] || "Error al guardar: " + res);
                    }
                }
            });
        });
    </script>
</body>
</html>