<?php

namespace App\Controllers\pedidos;

use App\Controllers\BaseController;
use App\Libraries\Algoritmos\Distancia\Haversine;
use App\Libraries\Algoritmos\Grafos\ArbolEntregas;
use App\Libraries\Algoritmos\Grafos\ArbolesDelDia;
use App\Models\PedidoModel;
use App\Models\RecorridoModel;
use App\Models\RepartidorModel;

class RecorridoController extends BaseController
{
    /** Pantalla con el mapa (los datos se piden aparte a arboles()). */
    public function index()
    {
        return $this->render('pedidos/recorrido/v_recorrido', [
            'titulo' => 'Recorrido del día',
        ], session('rol') === 'repartidor' ? 'entregas' : 'pedidos');
    }

    /** JSON con los árboles de hoy. Se recalcula en cada petición: siempre refleja los pedidos actuales. */
    public function arboles()
    {
        $miRepartidor = $this->repartidorDeLaSesion();   // null = ve todos; número = solo los suyos

        $pedidos = (new PedidoModel())->getPedidosEntregaHoy($miRepartidor !== null ? (string) $miRepartidor : null);
        $salidas = (new RecorridoModel())->salidasDeHoy();

        $resultado = (new ArbolesDelDia(new ArbolEntregas(new Haversine())))->calcular($pedidos, $salidas);

        return $this->response->setJSON([
            'puede_iniciar'  => $miRepartidor !== null && $miRepartidor > 0,
            'mi_repartidor'  => ($miRepartidor !== null && $miRepartidor > 0) ? $miRepartidor : null,
            'grupos'         => $resultado['grupos'],
            'sin_repartidor' => $resultado['sin_repartidor'],
        ]);
    }

    /** "Comenzar recorrido del día": guarda (o cambia) el punto de salida de hoy del repartidor. */
    public function iniciar()
    {
        $idRepartidor = $this->repartidorDeLaSesion();

        if ($idRepartidor === null || $idRepartidor < 1) {
            return $this->response->setStatusCode(403)
                ->setJSON(['ok' => false, 'error' => 'Tu cuenta no está vinculada a un repartidor.']);
        }

        $punto = ubicacion_parsear($this->request->getPost('salida'));
        if ($punto === null) {
            return $this->response->setStatusCode(422)
                ->setJSON(['ok' => false, 'error' => 'Selecciona el punto de salida en el mapa.']);
        }

        (new RecorridoModel())->guardarSalidaHoy($idRepartidor, $punto['lat'], $punto['lng']);

        return $this->response->setJSON(['ok' => true]);
    }

    /** Igual que en EntregaController: id del repartidor de la sesión, -1 si no está vinculado, null si no es repartidor. */
    private function repartidorDeLaSesion(): ?int
    {
        if (! in_array(session('rol'), ['repartidor', 'repartidor_avanzado'], true)) {
            return null;
        }

        return (new RepartidorModel())->idPorUsuario((int) session('id_usuario')) ?? -1;
    }
}