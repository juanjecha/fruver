<?php
/**
 * Versión MODAL del formulario de pedido (ruta: alta_pedido).
 * Se carga dentro del iframe de "Control de pedidos". No usa el layout con menú lateral.
 * El formulario y su JS viven en v_form_pedido.php y v_form_pedido_js.php (compartidos).
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>alta_pedido</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= base_url('css/pedido/v_alta_pedidos.css') ?>">
</head>

<body>
<?= $this->include('pedidos/pedido/v_form_pedido') ?>

<script>
<?= $this->include('pedidos/pedido/v_form_pedido_js') ?>
</script>

</body>
</html>
