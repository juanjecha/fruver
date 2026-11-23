<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo producto</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('css/comun/v_formulario.css') ?>?v=1">

</head>

<body>

    <form id="formProducto">
        <label>Nombre*</label>
        <input type="text" name="nombre" 
       maxlength="200" 
       required
       title="Solo letras. Máximo 200 caracteres."
       pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
       oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');" /><br>

        <label>Categoria*</label>
        <select id="opciones" name="categoria">
            <option value="fruta">fruta</option>
            <option value="verdura">verdura</option>
            <option value="hierba">hierba</option>
        </select>

        <!--<label>Categoria</label>
        <input type="text" name="categoria" maxlength="200" required/><br>-->

        <label>Descripcion</label>
        <textarea type="text" name="descripcion"
                maxlength="200" 
                 title="Solo letras. Máximo 200 caracteres."
                 pattern="[A-Za-aÁÉÍÓÚáéíóúÑñ\s]"
                 oninput="this.value=this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóú\s]/g,'');"
        ></textarea>   <br>

        <label>Foto</label>
        <div class="foto-campo">
            <img id="vistaFoto" class="foto-vista" alt="" hidden>
            <input type="file" name="imagen" id="imagen" accept="image/jpeg,image/png,image/webp">
        </div>
        <small class="foto-ayuda">JPG, PNG o WEBP de máximo 2 MB. El código del producto se genera solo al guardar.</small>
  
        <input type="submit" value="Guardar"/><br>
    </form>

    <script>
        $("#formProducto").submit(function(e){
            e.preventDefault();

            $.ajax({
                url: "<?= base_url('guardar_producto') ?>",
                method: "POST",
                data: new FormData(this),   // FormData para poder enviar la foto
                processData: false,
                contentType: false,

                success: function(res){
                console.log("RESPUESTA:", res);

                if(res.trim() === "ok"){
                    window.parent.cerrarModal();
                } else if(res.trim() === "duplicado"){
                    alert("Producto ya existe");
                } else if(res.trim() === "foto_invalida"){
                    alert("La foto debe ser JPG, PNG o WEBP y pesar máximo 2 MB.");
                } else {
                    alert("Error en datos: " + res);
                }
            },
            error: function(xhr){
                // Error del servidor (500): casi siempre es que falta ejecutar CAMBIOS_BD_productos.sql
                alert("No se pudo guardar (error " + xhr.status + "). Revisa que ya ejecutaste CAMBIOS_BD_productos.sql en phpMyAdmin.");
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