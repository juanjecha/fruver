<?php

namespace App\Controllers\pedidos;
//enlazar con el modelo
use App\Models\PedidoModel;
use Config\View;
use App\Controllers\BaseController;

class SeguimientoController extends BaseController  
{
    public function listar(){

        return view('layout/v_header')
                . view('pedidos/seguimiento/v_principal_seguimiento')
                . view('layout/v_footer');
    }

        // Confirmar pedido: asigna repartidor y cambia estatus
    public function confirmar_pedido()
    {
        $idPedido     = $this->request->getPost('id_pedido');
        $idRepartidor = $this->request->getPost('id_repartidor');

        if (!$idPedido || !$idRepartidor) {
            echo "error_validacion";
            return;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Asignar repartidor en tabla pedido
            $db->table('pedido')
            ->where('id_pedido', $idPedido)
            ->update(['id_repartidor' => $idRepartidor]);

            // INSERT del nuevo status
            $db->table('status')->insert([
                'estado'     => 'confirmado',
                'fecha'      => date('Y-m-d H:i:s'),
                'id_pedido2' => $idPedido
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) { //Verifica si la transacción activa tuvo algún error durante su ejecución
                throw new \Exception("Error en transacción");
            }

                echo "ok";

            } catch (\Exception $e) {
                $db->transRollback();
                log_message('error', 'confirmar_pedido: ' . $e->getMessage());
                echo "error";
            }
    }

    // Marcar pagado: registra método de pago y cambia estatus
    public function marcar_pagado()
    {
        $idPedido = $this->request->getPost('id_pedido');
        $pago     = $this->request->getPost('pago');

        if (!$idPedido || !in_array($pago, ['pago', 'credito'])) {
            echo "error_validacion";
            return;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Guardar método de pago en tabla pedido
            $db->table('pedido')
            ->where('id_pedido', $idPedido)
            ->update(['pago' => $pago]);

            $nuevoEstatus = $pago === 'pago' ? 'pagado' : 'credito';

            // INSERT del nuevo status 
            $db->table('status')->insert([
                'estado'     => $nuevoEstatus,
                'fecha'      => date('Y-m-d H:i:s'),
                'id_pedido2' => $idPedido
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Error en transacción");
            }

            echo "ok";

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'marcar_pagado: ' . $e->getMessage());
            echo "error";
        }
    }

//----------------seguimiento-------------------
    public function seguimiento()
    {
        // La vista global es del personal: el cliente solo tiene la suya (mis_pedidos)
        if (session('rol') === 'cliente') {
            return redirect()->to(base_url('mis_pedidos'));
        }

        $pedidoModel = new \App\Models\PedidoModel();

        // Recibir filtros del GETa

        // Obtiene el valor "buscar" desde la URL (GET)

        // trim() elimina espacios al inicio y final
        // ?? '' evita que sea null
        $buscar = trim($this->request->getGet('buscar') ?? '');
        $status = trim($this->request->getGet('status') ?? '');  // Obtiene el filtro de estado desde la URL

        // Validar que status sea un valor permitido
        $permitidos = ['', 'solicitado', 'confirmado', 'en transito', 'entregado', 'pagado', 'cancelado', 'credito'];
        if (!in_array($status, $permitidos)) {
            // Se reinicia a vacío (como si no hubiera filtro)
            $status = '';
        }

        // Pedidos filtrados
        $pedidos = $pedidoModel->getPedidosParaSeguimiento($buscar, $status); //Llama al modelo para obtener los pedidos con filtros aplicados

        // Agregar productos a cada pedido
        foreach ($pedidos as &$pedido) { // Recorre cada pedido
            $pedido['productos'] = $pedidoModel->getProductosDePedido($pedido['id_pedido']); // A cada pedido le agrega sus productos
        }

        // Resumen siempre sin filtros (para las tarjetas)
        $resumen = $pedidoModel->getResumenEstatus(); //Obtiene el resumen general (sin filtros)



        $datos = [
                        'titulo'       => 'Seguimiento pedidos',
                        'pedidos'      => $pedidos,
                        'repartidores' => $pedidoModel->getRepartidores(), 
                        'total'        => $resumen['total'],
                        'solicitados'  => $resumen['solicitados'],
                        'enProceso'    => $resumen['enProceso'],
                        'completados'  => $resumen['completados'],
                        'buscar'       => $buscar,
                        'status'       => $status,
                    ];
        // Pantalla compartida: si se llegó desde Clientes o Repartidores se conserva ese menú;
        // en cualquier otro caso (por ejemplo desde el panel) se usa el menú de Pedidos.
        $modulo = session('modulo_actual');
        if (! in_array($modulo, ['clientes', 'repartidores', 'pedidos'], true)) {
            $modulo = 'pedidos';
        }

        return $this->render('pedidos/seguimiento/v_principal_seguimiento', $datos, $modulo);
    }

    public function detalle_pedido()
    {
        $idPedido = $this->request->getPost('id_pedido');

        if (!$idPedido || !is_numeric($idPedido)) {
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        if (in_array(session('rol'), ['repartidor', 'repartidor_avanzado'], true)) {
            $idRep = (new \App\Models\RepartidorModel())->idPorUsuario((int) session('id_usuario'));
            $esSuyo = $idRep !== null && \Config\Database::connect()->table('pedido')
                ->where('id_pedido', (int) $idPedido)
                ->where('id_repartidor', $idRep)
                ->countAllResults() > 0;

            if (! $esSuyo) {
                echo json_encode(['error' => 'No tienes acceso a este pedido']);
                return;
            }
        }

        $pedidoModel = new \App\Models\PedidoModel();

        // Un cliente solo puede ver el detalle de SUS pedidos (aunque cambie el id en la petición)
        if (session('rol') === 'cliente') {
            $cliente = (new \App\Models\ClienteModel())->getPorUsuario((int) session('id_usuario'));

            if (! $cliente || ! $pedidoModel->pedidoEsDeCliente((int) $idPedido, (int) $cliente['id_cliente'])) {
                echo json_encode(['error' => 'No tienes acceso a este pedido']);
                return;
            }
        }

        echo json_encode([
            'pedido'    => $pedidoModel->getDetallePedido($idPedido)['pedido'],
            'productos' => $pedidoModel->getProductosDePedido($idPedido),
            'historial' => $pedidoModel->getHistorialStatus($idPedido), 
        ]);

        
    }
}
