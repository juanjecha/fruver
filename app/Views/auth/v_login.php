<!DOCTYPE html>
<html lang="es" class="auth-html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FRUVER - Iniciar sesión</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap">
    <link rel="stylesheet" href="<?= base_url('css/auth/auth.css') ?>">
    <script src="<?= base_url('js/auth/auth.js') ?>" defer></script>
    <script>
        if (window.top !== window.self) { window.top.location.href = window.location.href; }
    </script>
</head>
<body class="auth">

<div class="auth-shell">

    <!-- Logo (arriba a la izquierda en PC, centrado en tablet/celular) -->
    <header class="auth-logo">
        <img src="<?= base_url('img/auth/logo-fruver.png') ?>" alt="FRUVER" width="170" height="170">
    </header>

    <!-- Panel crema con el formulario -->
    <main class="panel-crema">
        <img class="hoja" src="<?= base_url('img/auth/hoja.svg') ?>" alt="" aria-hidden="true">

        <div class="auth-contenido">
            <p class="saludo">Bienvenido</p>
            <h1 class="titulo">Inicia sesión en tu cuenta</h1>
            <p class="subtitulo">Ingresa tus datos para empezar a usar fruver</p>

            <?php if ($aviso): ?>
                <div class="alert alert-<?= esc($aviso[0]) ?> py-2" role="alert"><?= esc($aviso[1]) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="post" autocomplete="on">
                <?= csrf_field() ?>

                <div class="campo mb-2">
                    <label for="correo" class="visually-hidden">Correo electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo"
                           placeholder="Correo electrónico" maxlength="150" required
                           autocomplete="username" inputmode="email"
                           value="<?= esc(session()->getFlashdata('correo_previo') ?? '') ?>">
                </div>

                <div class="campo campo-pass mb-4">
                    <label for="password" class="visually-hidden">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Contraseña" maxlength="72" required autocomplete="current-password">
                    <button type="button" class="btn-ojo" data-ver-pass="password" aria-label="Mostrar u ocultar contraseña">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn-fruver">Iniciar sesión <i class="fa-solid fa-arrow-right ms-1"></i></button>
                    <a href="<?= base_url('conocenos') ?>" class="btn-fruver text-center text-decoration-none">Conoce más de nosotros</a>
                </div>

                <p class="text-center small mb-0">
                    ¿Deseas conocer fruver? <a class="enlace-fruver" href="<?= base_url('conocenos') ?>">Conócenos aquí</a>
                </p>
            </form>
        </div>
    </main>

    <!-- Tarjetas informativas -->
    <section class="auth-tarjetas" aria-label="Qué puedes hacer en FRUVER">
        <article class="tarjeta tarjeta--banner">
            <img src="<?= base_url('img/auth/card-administra.webp') ?>"
                alt="Administra: dale seguimiento a todo tu inventario y asegura los pedidos de los clientes">
        </article>
        <article class="tarjeta tarjeta--banner">
            <img src="<?= base_url('img/auth/card-ventas.webp') ?>"
                alt="Ventas: gestiona tus ventas de manera detallada y mejora estrategias con estadísticas">
        </article>
        <article class="tarjeta tarjeta--banner">
            <img src="<?= base_url('img/auth/card-servicio.webp') ?>"
                alt="Servicio: mejora el servicio a tus clientes con información de entregas y compras">
        </article>
    </section>

</div>
</body>
</html>
