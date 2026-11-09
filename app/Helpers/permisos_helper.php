<?php

/**
 * puede('clientes.ver')                       -> ¿el rol en sesión tiene ese permiso?
 * puede('pedidos.ver', 'pedidos.ver_propios') -> ¿tiene AL MENOS UNO de ellos?
 *
 * Sirve en controladores, filtros y vistas. Recuerda: ocultar un botón NO protege;
 * la protección real es el filtro de la ruta y el alcance en la consulta.
 */
if (! function_exists('puede')) {
    function puede(string ...$permisos): bool
    {
        $rol       = (string) session('rol');
        $asignados = config('Permisos')->porRol[$rol] ?? [];

        foreach ($permisos as $pedido) {
            foreach ($asignados as $tiene) {
                if ($tiene === $pedido) {
                    return true;
                }
                // Comodín 'modulo.*'
                if (str_ends_with($tiene, '.*') && str_starts_with($pedido, substr($tiene, 0, -1))) {
                    return true;
                }
            }
        }

        return false;
    }
}
