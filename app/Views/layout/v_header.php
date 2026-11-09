<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
      integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
      crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="<?= base_url('css/layout/dashboard.css') ?>">
    <script src="<?= base_url('js/layout/dashboard.js') ?>" defer></script>
</head>
<body>
    <div class="menu-dashboard">
        <div class="top-menu">
            <div class="logo">
                <img src="<?= base_url('img/vegetal.png') ?>" alt="">
                <span id="tFruver">FRUVER</span>
            </div>

            <div class="toggle">
                <i class="fa-solid fa-bars"></i>
            </div>
            
        </div>

        
        <div class="menu">

                

                <a href="<?= base_url("crud_cliente") ?>">
                    <div class="enlace">
                        <i class="fa-solid fa-user"></i>
                        <span>Clientes</span>
                    </div>
                </a>

                <a href="<?= base_url("crud_repartidores") ?>">
                    <div class="enlace">
                        <i class="fa-solid fa-people-carry-box"></i>
                        <span>Repartidores</span>
                    </div>
                </a>

                <a href="<?= base_url("lista_productos") ?>">
                    <div class="enlace">
                        <i class="fa-solid fa-apple-whole"></i>
                    <span>Productos</span>
                </div>
                </a>

                <a href="<?= base_url("entradas") ?>">
                    <div class="enlace">
                        <i class="fa-solid fa-cart-flatbed"></i>
                        <span>Entradas</span>
                    </div>
                </a>
        

                <a href="<?= base_url("existencias") ?>">
                    <div class="enlace">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Existencias</span>
                    </div>
                </a>

                <a href="<?= base_url("merma") ?>">
                    <div class="enlace">
                        <i class="fa-solid fa-dumpster"></i>
                        <span>merma</span>
                    </div>
                </a>

                <a href="<?= base_url("control_pedidos") ?>">
                    <div class="enlace">
                        <i class="fa-solid fa-boxes-packing"></i>
                        <span>Control de pedidos</span>
                    </div>
                </a>

                <a href="<?= base_url("seguimiento_pedidos") ?>">
                    <div class="enlace">
                        <i class="fa-solid fa-truck-fast"></i>
                        <span>Seguimiento de pedidos</span>
                    </div>
                </a>

                <a href="<?= base_url("gestion_entregas") ?>">
                    <div class="enlace">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Gestión de entregass</span>
                    </div>
                </a>



        </div>


    </div>

    <div class="content">
