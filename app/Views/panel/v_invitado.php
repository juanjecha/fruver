<?php
    $c = config('Contacto');
    $wa = 'https://wa.me/' . preg_replace('/\D+/', '', $c->lada . $c->whatsapp);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FRUVER - Conócenos</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="<?= base_url('css/auth/invitado.css') ?>">
</head>
<body class="invitado">

    <footer class="inv-pie">
        <spam>Fruver, es una empresa que te acerca tus compras de fruta y verdura con opciones optimixadas para seguimiento de entregas, con productos de la mejor calidad.</spam>
        <spam>A continuacion te mostramos los servicios</spam>
    </footer>

<main class="inv-principal">

    <!-- ───────── SECCIÓN 1: presentación ───────── -->
    <section class="inv inv--hero" id="presentacion">
        <?= view('panel/_garabatos') ?>

        <h1 class="inv-titulo">FRUVER</h1>

        <div class="inv-banners">
            <figure class="inv-banner">
                <img src="<?= base_url('img/invitado/banner-que-vendemos.png') ?>"
                     alt="¿Qué vendemos? Dedicados a la comercialización de frutas y verduras frescas, ofreciendo productos de calidad a precios accesibles.">
            </figure>
            <figure class="inv-banner">
                <img src="<?= base_url('img/invitado/banner-ventas.png') ?>"
                     alt="Ventas: atendemos ventas al mayoreo y al menudeo, adaptándonos a las necesidades de cada cliente.">
            </figure>
            <figure class="inv-banner">
                <img src="<?= base_url('img/invitado/banner-pagos.png') ?>"
                     alt="Opciones de pago: en ocasiones especiales brindamos facilidades de crédito para fortalecer la relación comercial con nuestros clientes.">
            </figure>
            <figure class="inv-banner">
                <img src="<?= base_url('img/invitado/banner-entregas.png') ?>"
                     alt="Entregas: distribuimos y exportamos nuestros productos a comunidades, garantizando frescura, calidad y un servicio confiable.">
            </figure>
        </div>

        <!-- La flecha lleva a la siguiente sección -->
        <a href="#consulta" class="inv-flecha" aria-label="Ver más información">
            <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
        </a>
    </section>

    <!-- ───────── SECCIÓN 2: consulta y contacto ----Commi preuba ───────── -->
    <section class="inv inv--contacto" id="consulta">
        <?= view('panel/_garabatos') ?>

        <figure class="inv-consulta">
            <img src="<?= base_url('img/invitado/banner-consulta.png') ?>"
                 alt="Consulta tu información: Fruver también te ofrece una página personalizada donde puedes ver el estado de tu pedido en camino, los detalles del mismo y, si tienes pedidos a crédito, información sobre tu estado de adeudos o pagados.">
        </figure>

        <h2 class="inv-subtitulo">¿Cómo nos contactas?</h2>
        <p class="inv-texto">
            Visita nuestras instalaciones personalmente para hacer tu pedido o contáctanos por línea.
            A continuación, te proporcionamos las instalaciones donde nos encontramos y nuestros medios de contacto:
        </p>

        <ul class="inv-contacto">
            <li>
                <a href="<?= esc($wa, 'url') ?>" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i><span><?= esc($c->whatsapp) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= esc($c->facebookUrl, 'url') ?>" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-facebook-f" aria-hidden="true"></i><span><?= esc($c->facebook) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= esc($c->instagramUrl, 'url') ?>" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-instagram" aria-hidden="true"></i><span><?= esc($c->instagram) ?></span>
                </a>
            </li>
        </ul>

        <ul class="inv-sitios" aria-label="Nuestras instalaciones">
            <?php foreach ($c->instalaciones as $sitio): ?>
                <li>
                    <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode($sitio['direccion']) ?>"
                       target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <span><strong><?= esc($sitio['nombre']) ?></strong><br><?= esc($sitio['direccion']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</main>

    <footer class="inv-pie">
        <span>¿Ya eres cliente?</span>
        <a href="<?= base_url('login') ?>" class="btn-salir">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Iniciar sesión
        </a>
    </footer>

</body>
</html>
