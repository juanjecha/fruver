<?php

namespace App\Controllers\pedidos;
//enlazar con el modelo
use App\Models\PedidoModel;
use App\Models\RepartidorModel;
use Config\View;
use App\Controllers\BaseController;

class EntregaController extends BaseController  
{
///------------FUNCIONES PARA -GESTION DE ENTREGAS-------------
    public function entregas()
    {
        $pedidoModel  = new PedidoModel();
        $miRepartidor = $this->repartidorDeLaSesion();   // null = ve todos; número = solo los suyos

        $status = trim($this->request->getGet('status') ?? '');
        if (! in_array($status, ['', 'solicitado', 'confirmado', 'en transito', 'entregado'], true)) {
            $status = '';
        }

        // Un repartidor SIEMPRE queda limitado a su propio id (aunque manipule la URL)
        $idRepartidor = $miRepartidor !== null
            ? (string) $miRepartidor
            : trim($this->request->getGet('id_repartidor') ?? '');

        $pedidos = $pedidoModel->getPedidosEntregaHoy($idRepartidor, $status);

        // El historial se carga en la misma pantalla de Gestión de Pedidos.
        $historialPedidos = $miRepartidor !== null
            ? $pedidoModel->getHistorialPedidosEntregados((int) $miRepartidor)
            : []; 

        // Tarjetas: todos los de hoy dentro de su alcance (sin el filtro de status)
        $todos = $pedidoModel->getPedidosEntregaHoy($miRepartidor !== null ? (string) $miRepartidor : null);

        $solicitados = $enProceso = $completados = 0;
        foreach ($todos as $p) {
            $est = $p['status'] ?? 'solicitado';
            if ($est === 'solicitado')                              $solicitados++;
            elseif (in_array($est, ['confirmado', 'en transito'])) $enProceso++;
            elseif ($est === 'entregado')                          $completados++;
        }

        return $this->render('pedidos/entrega/v_principal_entregas', [
            'titulo'       => $miRepartidor !== null ? 'Mis entregas' : 'Gestión de entregas',
            'pedidos'      => $pedidos,
            'historialPedidos' => $historialPedidos,
            'repartidores' => $miRepartidor !== null ? [] : $pedidoModel->getRepartidores(),
            'total'        => count($todos),
            'solicitados'  => $solicitados,
            'enProceso'    => $enProceso,
            'completados'  => $completados,
            'idRepartidor' => $idRepartidor,
            'status'       => $status,
            'soloMias'     => $miRepartidor !== null,
        ], session('rol') === 'repartidor' ? 'entregas' : 'pedidos');
    }

    /**
     * Si quien entra es repartidor (normal o avanzado) devuelve SU id_repartidor
     * (o -1 si su cuenta no está unida a ningún repartidor: así no ve nada).
     * Para los demás roles devuelve null (ven todas las entregas).
     */
    private function repartidorDeLaSesion(): ?int
    {
        if (! in_array(session('rol'), ['repartidor', 'repartidor_avanzado'], true)) {
            return null;
        }

        return (new RepartidorModel())->idPorUsuario((int) session('id_usuario')) ?? -1;
    }

    public function asignar_repartidor()
    {
        $idPedido     = $this->request->getPost('id_pedido');
        $idRepartidor = $this->request->getPost('id_repartidor');

        if (!$idPedido || !$idRepartidor) {
            echo "error_validacion";
            return;
        }

        $db = \Config\Database::connect();

        // Verificar si el pedido ya tiene repartidor asignado
        $pedido = $db->table('pedido')
                    ->where('id_pedido', $idPedido)
                    ->get()->getRowArray();

        $db->transStart();

        // Asignar repartidor
        $db->table('pedido')
        ->where('id_pedido', $idPedido)
        ->update(['id_repartidor' => $idRepartidor]);

        // Si NO tenía repartidor → es primera asignación → confirmar
        if (empty($pedido['id_repartidor'])) {
            $db->table('status')->insert([
                'estado'     => 'confirmado',
                'fecha'      => date('Y-m-d H:i:s'),
                'id_pedido2' => $idPedido
            ]);
        }

        $db->transComplete();

        echo $db->transStatus() ? "ok" : "error";
    }

    /** Historial de pedidos entregados por el repartidor autenticado. */
    public function historial()
    {
        $rol = (string) session('rol');
        $idRepartidor = null;

        if (in_array($rol, ['repartidor', 'repartidor_avanzado'], true)) {
            $idRepartidor = (new RepartidorModel())->idPorUsuario((int) session('id_usuario'));
            // Si la cuenta no está vinculada a un repartidor, no debe ver registros.
            $pedidos = $idRepartidor === null
                ? []
                : (new PedidoModel())->getHistorialPedidosEntregados((int) $idRepartidor);
        } else {
            $pedidos = (new PedidoModel())->getHistorialPedidosEntregados();
        }

        return $this->render('pedidos/entrega/v_historial_entregas', [
            'titulo'  => 'Historial de Pedidos Entregados',
            'pedidos' => $pedidos,
        ], 'pedidos');
    }
}