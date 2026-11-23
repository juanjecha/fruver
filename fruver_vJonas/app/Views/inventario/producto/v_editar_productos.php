<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_formulario.css') ?>?v=1">
</head>

<body>

    <form id="formEditar" method="post">
        <input type="hidden" name="id_producto" value="<?= $datos_producto['id_producto'] ?>">

        <label>Código</label>
        <input type="text" value="<?= esc($datos_producto['codigo'] ?? '', 'attr') ?>" readonly class="campo-fijo">

        <label>Nombre</label>
        <input type="text" name="nom" value="<?= esc($datos_producto['nombre']) ?>"
       maxlength="200" 
       required
       title="Solo letras. Máximo 200 caracteres."
       pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
       oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');">

        <label>Categoria</label>
        <select name="categoria">
            <option value="fruta"   <?= $datos_producto['categoria'] == 'fruta'   ? 'selected' : '' ?>>fruta</option>
            <option value="verdura" <?= $datos_producto['categoria'] == 'verdura' ? 'selected' : '' ?>>verdura</option>
            <option value="hierba"  <?= $datos_producto['categoria'] == 'hierba'  ? 'selected' : '' ?>>hierba</option>
        </select>

        <label>Descripción</label>
        <textarea type="text" name="descripcion"
                maxlength="200" 
                 title="Solo letras. Máximo 200 caracteres."
                 pattern="[A-Za-aÁÉÍÓÚáéíóúÑñ\s]"
                 oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóú\s]/g,'');"
        ><?= esc($datos_producto['descripcion']) ?></textarea>

        <label>Foto</label>
        <?php $hayFoto = ! empty($datos_producto['imagen']) && is_file(FCPATH . 'img/productos/' . $datos_producto['imagen']); ?>
        <div class="foto-campo">
            <img id="vistaFoto" class="foto-vista" alt=""
                 src="<?= $hayFoto ? base_url('img/productos/' . $datos_producto['imagen']) : '' ?>"
                 <?= $hayFoto ? '' : 'hidden' ?>>
            <input type="file" name="imagen" id="imagen" accept="image/jpeg,image/png,image/webp">
        </div>
        <small class="foto-ayuda">Elige una foto solo si quieres cambiarla (JPG, PNG o WEBP, máximo 2 MB).</small>

        <div class="btn-row">
            <input type="submit" value="Guardar">
        </div>

    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $("#formEditar").submit(function(e){
            e.preventDefault();

            $.ajax({
                url: "<?= base_url('actualiza') ?>",
                method: "POST",
                data: new FormData(this),   // FormData para poder enviar la foto
                processData: false,
                contentType: false,
                success: function(res){
                    if(res.trim() === "ok"){
                        window.parent.cerrarModal();
                    } else if(res.trim() === "foto_invalida"){
                        alert("La foto debe ser JPG, PNG o WEBP y pesar máximo 2 MB.");
                    } else {
                        alert("No se pudieron guardar los cambios.");
                    }
                },
                error: function(xhr){
                    alert("No se pudieron guardar los cambios (error " + xhr.status + "). Revisa que ya ejecutaste CAMBIOS_BD_productos.sql en phpMyAdmin.");
                }
            });
        });

        // Vista previa de la foto elegida
        $("#imagen").on("change", function(){
            const archivo = this.files[0];
            if (archivo) {
                $("#vistaFoto").attr("src", URL.createObjectURL(archivo)).prop("hidden", false);
                // Avisa a la página para que el modal crezca y no se corte el botón
                $("#vistaFoto").one("load", function(){
                    if (window.parent.ajustarAltura) window.parent.ajustarAltura();
                });
            }
        });
    </script>

</body>
</html>