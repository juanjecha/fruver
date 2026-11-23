<?php
/**
 * Mis datos (Ley Federal de Protección de Datos Personales en Posesión de los Particulares).
 * Lo llama PortalClienteController::datos() con $this->render(...).
 * El botón "Ver" abre el aviso de privacidad en un modal.
 */
?>
<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Chewy&display=swap">
    <link rel="stylesheet" href="<?= base_url('css/panel/v_portal_cliente.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('encabezado') ?>
    <?= $this->include('panel/client/v_encabezado_cliente') ?>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="fv-pagina cli-pagina">

    <div class="cli-card cli-datos">

        <?php if ($sinCuenta): ?>
            <div class="cli-vacio cli-vacio--interno">Tu cuenta aún no está unida a un cliente. Contacta a la empresa para que la vinculen.</div>
        <?php endif; ?>

        <div class="cli-avatar" aria-hidden="true"><i class="fa-solid fa-user"></i></div>
        <p class="cli-datos__primer"><?= esc($primerNombre) ?></p>

        <dl class="cli-datos__lista">
            <div><dt>Nombre:</dt>           <dd><?= esc($cliente['nombre'] ?? '') ?></dd></div>
            <div><dt>Apellido Paterno:</dt> <dd><?= esc($cliente['ap_p'] ?? '') ?></dd></div>
            <div><dt>Apellido Materno:</dt> <dd><?= esc($cliente['ap_m'] ?? '') ?></dd></div>
            <div><dt>Teléfono:</dt>         <dd><?= esc($cliente['tel'] ?? '') ?></dd></div>
        </dl>

        <div class="cli-aviso">
            <span>Aceptaste el aviso de privacidad</span>
            <button type="button" class="cli-boton" data-bs-toggle="modal" data-bs-target="#modalAviso">
                Ver <i class="fa-regular fa-eye"></i>
            </button>
        </div>

        <p class="cli-datos__ayuda">
            ¿Tienes algún problema con tus datos? ¿Información incorrecta? ¿Actualización de información?
            ¿Necesitas que tus datos sean eliminados de nuestro registro? Contáctate con la empresa
            mediante sus medios de contacto. Da clic al botón de contacto y envía tu solicitud.
        </p>

        <a class="cli-boton cli-boton--centro" href="<?= esc($urlContacto, 'attr') ?>" target="_blank" rel="noopener">Contacto</a>
    </div>

    <?= $this->include('panel/client/v_aviso_privacidad_cliente') ?>
</div>
<?= $this->endSection() ?>
