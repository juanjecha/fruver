<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro "permiso": primero exige sesión (AuthFilter) y luego que el rol tenga
 * AL MENOS UNO de los permisos indicados.
 *
 * Uso en rutas:  ['filter' => 'permiso:clientes.ver']
 *                ['filter' => 'permiso:pedidos.ver,pedidos.ver_propios']
 */
class PermisoFilter extends AuthFilter
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $respuesta = parent::before($request, $arguments);
        if ($respuesta instanceof ResponseInterface) {
            return $respuesta;
        }

        helper('permisos');

        if (! empty($arguments) && puede(...$arguments)) {
            return;
        }

        // Sin permiso: las peticiones AJAX reciben 403; las demás vuelven a su panel
        if ($request->isAJAX()) {
            return service('response')->setStatusCode(403)->setJSON(['error' => 'Sin permiso']);
        }

        $cfg = config('Auth');
        $rol = (string) session()->get('rol');

        if (isset($cfg->panelPorRol[$rol])) {
            return redirect()->to(base_url($cfg->panelPorRol[$rol]))
                ->with('error', 'No tienes permiso para entrar a esa sección.');
        }

        session()->destroy();
        return redirect()->to(base_url('login?aviso=requiere'));
    }
}
