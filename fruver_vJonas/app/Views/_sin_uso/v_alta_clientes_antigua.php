<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>log_client</title>
</head>

<body>
    <form action="<?= base_url("guarda_cliente") ?>" method="post">
        <label>Nombre</label>
        <input type="text" name="nom" required/><br>

        <label>Apellido Paterno</label>
        <input type="text" name="ap_p" required/><br>

        <label>Apellido materno</label>
        <input type="tel" name="ap_m" required/><br>

        <label>Teléfono</label>
        <input type="text" name="tel" required/><br>
  
        <input type="submit" value="guardar"/><br>
    </form>
</body>
</html>