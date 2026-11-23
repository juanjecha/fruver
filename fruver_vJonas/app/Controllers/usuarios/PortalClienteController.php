<?php

namespace App\Controllers\Usuarios;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Models\PedidoModel;

/**
 * Pantallas que ve el CLIENTE al entrar con su cuenta (rol "cliente"):
 *   mis_pedidos  -> pedidos por entregar / entregados
 *   mi_credito   -> pedidos a crédito pendientes / liquidados
 *   mis_datos    -> sus datos y el aviso de privacidad
 * Todo se consulta con el id_cliente de la SESIÓN, nunca con un id que llegue por la URL.
 */
class PortalClienteController extends BaseController
{
    public function pedidos()
    {
        $cliente = $this->clienteDeLaSesion();

        $porEntregar = [];
        $entregados  = [];
        foreach ($this->pedidosDe($cliente) as $p) {
            if ($p['entregado']) {
                $entregados[] = $p;
            } elseif ($p['status'] !== 'cancelado') {
                $porEntregar[] = $p;
            }
        }

        return $this->render('panel/client/v_pedidos_cliente', [
            'titulo'      => 'Mis pedidos',
            'sinCuenta'   => $cliente === null,
            'porEntregar' => $porEntregar,
            'entregados'  => $entregados,
        ], 'cliente');
    }

    public function credito()
    {
        $cliente = $this->clienteDeLaSesion();

        $deudas  = [];
        $pagados = [];
        foreach ($this->pedidosDe($cliente) as $p) {
            if ($p['status'] === 'credito') {
                $deudas[] = $p;                                   // a crédito y sin liquidar
            } elseif ($p['status'] === 'pagado' && $p['tuvo_credito']) {
                $pagados[] = $p;                                  // estuvo a crédito y ya se liquidó
            }
        }

        return $this->render('panel/client/v_credito_cliente', [
            'titulo'     => 'Mi crédito',
            'sinCuenta'  => $cliente === null,
            'deudas'     => $deudas,
            'pagados'    => $pagados,
            'totalDeuda' => array_sum(array_column($deudas, 'total')),
        ], 'cliente');
    }

    public function datos()
    {
        $cliente = $this->clienteDeLaSesion();
        $c       = config('Contacto');

        return $this->render('panel/client/v_datos_cliente', [
            'titulo'      => 'Mis datos',
            'sinCuenta'   => $cliente === null,
            'cliente'     => $cliente,
            'primerNombre'=> $cliente ? (preg_split('/\s+/', trim($cliente['nombre']))[0] ?? '') : '',
            // Botón "Contacto": abre WhatsApp con el mensaje ya escrito (mismo número que el invitado)
            'urlContacto' => 'https://wa.me/' . preg_replace('/\D+/', '', $c->lada . $c->whatsapp)
                           . '?text=' . rawurlencode('Hola, soy cliente de Fruver y quiero solicitar la actualización, corrección o eliminación de mis datos personales.'),
        ], 'cliente');
    }

    // ───────────── internos ─────────────

    /** El cliente de la cuenta en sesión (null si la cuenta no está unida a ningún cliente). */
    private function clienteDeLaSesion(): ?array
    {
        return (new ClienteModel())->getPorUsuario((int) session('id_usuario'));
    }

    private function pedidosDe(?array $cliente): array
    {
        return $cliente ? (new PedidoModel())->getPedidosDeCliente((int) $cliente['id_cliente']) : [];
    }
}
