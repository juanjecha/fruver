<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FRUVER - <?= esc($titulo) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Playfair+Display:wght@700&display=swap">
    <link rel="stylesheet" href="<?= base_url('css/auth/auth.css') ?>">
</head>
<body class="auth">
<main class="registro-main" style="position:static">
    <div class="registro-card text-center">
        <p class="marca">FRUVER</p>
        <h1 class="titulo"><?= esc($titulo) ?></h1>
        <p class="mb-1">Hola, <strong><?= esc($nombre) ?></strong></p>
        <p class="mb-3"><?= esc($mensaje) ?></p>
        <p class="small text-muted">Rol en sesión: <code><?= esc($rol) ?></code> · La sesión se cierra tras 30 min sin actividad.</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-warning py-2 small"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <!-- Cerrar sesión por POST + CSRF (un enlace GET podría activarse desde otra página) -->
        <form action="<?= base_url('logout') ?>" method="post" class="d-grid mt-3">
            <?= csrf_field() ?>
            <button type="submit" class="btn-fruver">Cerrar sesión</button>
        </form>
    </div>
</main>
</body>
</html>
