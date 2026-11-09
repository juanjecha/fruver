<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro "rol": primero exige sesión (AuthFilter) y luego revisa el rol.
 * Uso en rutas:  ['filter' => 'rol:administrador']  o  'rol:administrador,vendedor_avanzado'
 */
class RolFilter extends AuthFilter
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $respuesta = parent::before($request, $arguments);
        if ($respuesta instanceof ResponseInterface) {
            return $respuesta;
        }

        $cfg = config('Auth');
        $rol = (string) session()->get('rol');

        if (! empty($arguments) && ! in_array($rol, $arguments, true)) {
            // Rol no permitido: lo regresa a SU panel (o al login si el rol no existe)
            if (isset($cfg->panelPorRol[$rol])) {
                return redirect()->to(base_url($cfg->panelPorRol[$rol]))
                    ->with('error', 'No tienes permiso para entrar a esa sección.');
            }
            session()->destroy();
            return redirect()->to(base_url('login?aviso=requiere'));
        }
    }
}
