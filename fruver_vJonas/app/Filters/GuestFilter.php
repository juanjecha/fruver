<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro "guest": si ya hay sesión vigente, no muestra login/registro y manda al panel.
 */
class GuestFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $cfg    = config('Auth');
        $sesion = session();

        $vigente = $sesion->get('logged_in')
            && (time() - (int) $sesion->get('last_activity')) <= $cfg->inactividad
            && isset($cfg->panelPorRol[(string) $sesion->get('rol')]);

        if ($vigente) {
            return redirect()->to(base_url($cfg->panelPorRol[$sesion->get('rol')]));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
