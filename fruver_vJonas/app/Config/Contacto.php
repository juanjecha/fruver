<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Datos de contacto e instalaciones que se muestran en la pantalla del invitado.
 * Edita solo este archivo para cambiarlos.
 */
class Contacto extends BaseConfig
{
    // WhatsApp: número que se muestra y lada del país para el enlace (52 = México)
    public string $whatsapp = '48484884848';
    public string $lada     = '52';

    // Redes: texto que se muestra y dirección completa del perfil
    public string $facebook     = 'Fruver';
    public string $facebookUrl  = 'https://www.facebook.com/';       // <- pon aquí tu página
    public string $instagram    = 'Instagram';
    public string $instagramUrl = 'https://www.instagram.com/';      // <- pon aquí tu perfil

    // Instalaciones (cada una abre Google Maps con su dirección)
    public array $instalaciones = [
        ['nombre' => 'Instalación 1', 'direccion' => 'Calle, número, colonia, municipio, estado'],
        ['nombre' => 'Instalación 2', 'direccion' => 'Calle, número, colonia, municipio, estado'],
        ['nombre' => 'Instalación 3', 'direccion' => 'Calle, número, colonia, municipio, estado'],
        ['nombre' => 'Instalación 4', 'direccion' => 'Calle, número, colonia, municipio, estado'],
    ];
}
