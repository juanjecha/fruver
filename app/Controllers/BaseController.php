<?php

namespace App\Controllers;

use App\Libraries\MenuLateral;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /** Helpers disponibles en todos los controladores y vistas (puede() vive en 'permisos'). */
    protected $helpers = ['permisos','cliente'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Dibuja una vista dentro del layout con el menú lateral del módulo.
     * $modulo: clave de Config\Menu desde la que se entró ('clientes', 'inventario'...).
     *          Las vistas compartidas por varios módulos pasan null.
     */
    protected function render(string $vista, array $datos = [], ?string $modulo = null): string
    {
        $datos['menu'] = (new MenuLateral())->construir($modulo);

        return view($vista, $datos);
    }
}
