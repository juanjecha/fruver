<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Ajustes de seguridad y rutas del login.
 */
class Auth extends BaseConfig
{
    // Segundos sin actividad antes de cerrar la sesión (30 min)
    public int $inactividad = 1800;

    // Duración máxima de una sesión aunque haya actividad (8 h)
    public int $duracionMaxima = 28800;

    // Intentos fallidos antes de bloquear la cuenta y minutos de bloqueo
    public int $maxIntentos    = 5;
    public int $minutosBloqueo = 15;

    // A qué panel va cada rol después de iniciar sesión
    public array $panelPorRol = [
        'administrador'       => 'panel/administrador',
        'vendedor_avanzado'   => 'panel/vendedor-avanzado',
        'repartidor_avanzado' => 'panel/repartidor-avanzado',
        'repartidor'          => 'panel/repartidor',
        'cliente'             => 'panel/cliente',
    ];
}
