<?php

namespace App\Controllers\auth;

use App\Controllers\BaseController;

/**
 * Pantalla de inicio de cada rol. Cada ruta está protegida con el filtro "rol".
 * Administrador, vendedor avanzado y repartidor avanzado tienen su panel con tarjetas;
 * repartidor y cliente siguen con la versión de prueba hasta que les toque.
 */
class PanelController extends BaseController
{
    public function administrador()
    {
        $this->olvidarModulo();
         return view('panel/admin/v_panel_admin', [
            'nombre' => session('nombre'),
        ]);
    }

    public function vendedorAvanzado()
    {
        $this->olvidarModulo();
        return view('panel/vendedor_avanzado/v_panel_va', [
            'nombre' => session('nombre'),
        ]);
    }

    public function repartidorAvanzado()
    {
        $this->olvidarModulo();
        return view('panel/repartidor_avanzado/v_panel_ra', [
            'nombre' => session('nombre'),
        ]);
    }

    public function repartidor()
    {
        return redirect()->to(base_url('gestion_entregas'));
    }

    public function cliente()
    {
        $this->olvidarModulo();
        return redirect()->to(base_url('mis_pedidos'));
    }

    public function conocenos()
    {
        return view('panel/v_invitado');
    }

    /**
     * Al entrar a un panel se borra el módulo que el menú lateral tenía recordado.
     * Así, desde el panel, "Seguimiento de pedidos" abre el menú de Pedidos y no el
     * de la última pantalla visitada (por ejemplo Clientes).
     */
    private function olvidarModulo(): void
    {
        session()->remove('modulo_actual');
    }

    private function mostrar(string $titulo, string $mensaje)
    {
        return view('panel/v_panel', [
            'titulo'  => $titulo,
            'mensaje' => $mensaje,
            'nombre'  => session('nombre'),
            'rol'     => session('rol'),
        ]);
    }
}
