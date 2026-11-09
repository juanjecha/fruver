<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER - Bienvenido</title>

    <link rel="stylesheet" href="<?= base_url('css/inicio/v_principal_inicio.css') ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body>

<div class="portada-wrapper">
    <img class="bg"
         src="<?= base_url('img/portada2.png') ?>"
         alt="FRUVER - Bienvenido">

    <!-- BOTON ADMNISTRACIÓN— Persona → Clientes / Repartidores -->
    <div class="btn-area btn-area-1" id="btn1" onclick="toggle('btn1')">
        <div class="dropdown">
            <a href="<?= base_url('crud_cliente') ?>">
                <i class="fa-solid fa-users"></i> Clientes
            </a>
            <a href="<?= base_url('crud_repartidores') ?>">
                <i class="fa-solid fa-people-carry-box"></i> Repartidores
            </a>
        </div>
    </div>

    <!-- BOTON INVENTARIO— Inventario → Productos / Entradas / Merma / Existencias -->
    <div class="btn-area btn-area-2" id="btn2" onclick="toggle('btn2')">
        <div class="dropdown">
            <a href="<?= base_url('lista_productos') ?>">
                <i class="fa-solid fa-apple-whole"></i> Productos
            </a>
            <a href="<?= base_url('entradas') ?>">
                <i class="fa-solid fa-cart-flatbed"></i> Entradas
            </a>
            <a href="<?= base_url('merma') ?>">
                <i class="fa-solid fa-dumpster"></i> Merma
            </a>
            <a href="<?= base_url('existencias') ?>">
                <i class="fa-solid fa-warehouse"></i> Existencias
            </a>
        </div>
    </div>

    <!-- bton ventas — Pedidos → Control / Seguimiento / Gestión -->
    <div class="btn-area btn-area-3" id="btn3" onclick="toggle('btn3')">
        <div class="dropdown">
            <a href="<?= base_url('control_pedidos') ?>">
                <i class="fa-solid fa-boxes-packing"></i> Control de pedidos
            </a>
            <a href="<?= base_url('seguimiento_pedidos') ?>">
                <i class="fa-solid fa-truck-fast"></i> Seguimiento de pedidos
            </a>
            <a href="<?= base_url('gestion_entregas') ?>">
                <i class="fa-solid fa-check-double"></i> Gestión de entregas
            </a>
        </div>
    </div>

    

</div>

<script>
    //Esta función abre o cierra un panel segun su id
    function toggle(id) {
        const all = document.querySelectorAll('.btn-area'); // Obtiene todos los paneles

        // Recorre cada panel y elimina la clase 'open' a todos excepto al seleccionado
        // Así se asegura que ningún otro panel quede abierto al mismo tiempo
        all.forEach(el => {
            if (el.id !== id) el.classList.remove('open');// Obtiene todos los paneles
        });
        document.getElementById(id).classList.toggle('open');// Agrega 'open' si el panel estaba cerrado, o la quita si estaba abierto

    // Escucha el clic en toda la página para detectar si el usuario hizo clic fuera de un panel
    document.addEventListener('click', function(e) {

    // e.target.closest('.btn-area') devuelve null si el clic fue fuera de cualquier panel
    // En ese caso, elimina 'open' de todos los paneles cerrándolos
        if (!e.target.closest('.btn-area')) {
            document.querySelectorAll('.btn-area').forEach(el => el.classList.remove('open'));
        }
    });
}
</script>

</body>
</html>