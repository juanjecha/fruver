<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro "auth": solo deja pasar a quien tiene sesión iniciada y vigente.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $cfg    = config('Auth');
        $sesion = session();

        if (! $sesion->get('logged_in')) {
            return $this->aLogin($request, 'requiere');
        }

        $ahora    = time();
        $inactivo = ($ahora - (int) $sesion->get('last_activity')) > $cfg->inactividad;
        $vencida  = ($ahora - (int) $sesion->get('login_at')) > $cfg->duracionMaxima;

        if ($inactivo || $vencida) {
            $sesion->destroy();
            return $this->aLogin($request, 'expirada');
        }

        // Sesión vigente: renueva el reloj de inactividad
        $sesion->set('last_activity', $ahora);
    }

    private function aLogin(RequestInterface $request, string $aviso)
    {
        $url = base_url('login?aviso=' . $aviso);

        if ($request->isAJAX()) {
            return service('response')->setStatusCode(401)->setJSON(['expirada' => true, 'login' => $url]);
        }

        return redirect()->to($url);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Evita que el navegador guarde páginas privadas (botón "atrás" tras cerrar sesión)
        return $response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache');
    }
}
