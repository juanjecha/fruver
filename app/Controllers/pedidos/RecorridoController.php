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


        $ajustes = config('Recorrido');
        $salidaPorDefecto = ['lat' => (float) $ajustes->salidaLat, 'lng' => (float) $ajustes->salidaLng];

        $resultado = (new ArbolesDelDia(new ArbolEntregas(new Haversine())))->calcular($pedidos, $salidas, $salidaPorDefecto);

        $ors = new \App\Libraries\Rutas\OpenRouteService($ajustes->orsApiKey, $ajustes->orsPerfil, $ajustes->orsMaxNuevasPorPeticion);

        foreach ($resultado['grupos'] as &$g) {
            if (! $g['arbol']) {
                continue;
            }
            $totalCalles = 0.0;
            $completo = true;

            foreach ($g['arbol']['aristas'] as &$a) {
                $ruta = $ors->ruta($g['arbol']['nodos'][$a['desde']], $g['arbol']['nodos'][$a['hasta']]);
                if ($ruta) {
                    $a['trazo'] = $ruta['puntos'];
                    $a['metros_calle'] = round($ruta['metros'], 1);
                    $totalCalles += $ruta['metros'];
                } else {
                    $completo = false;
                }
            }
            unset($a);

            //solo si todas las aristas tienen trazo real; si no, el JS usa factor_calles
            $g['arbol']['costo_metros_calles'] = $completo ? round($totalCalles, 1) : null;
        }    
        unset($g);
        

        return $this->response->setJSON([
            'puede_iniciar'  => $miRepartidor !== null && $miRepartidor > 0,
            'mi_repartidor'  => ($miRepartidor !== null && $miRepartidor > 0) ? $miRepartidor : null,
            'grupos'         => $resultado['grupos'],
            'sin_repartidor' => $resultado['sin_repartidor'],
            'salida_defecto' => $salidaPorDefecto + ['nombre' => $ajustes->salidaNombre],
            'combustible'    => [
                'rendimiento'      => $ajustes->rendimientoKmPorLitro,
                'vehiculo_defecto' => $ajustes->vehiculoPorDefecto,
                'precio_litro'     => $ajustes->precioGasolina,
                'factor_calles'    => $ajustes->factorCalles,
                'litros_bajo'      => $ajustes->litrosBajo,
                'litros_alto'      => $ajustes->litrosAlto,
            ],
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

    /** Igual que en EntregaController: id del repartidor de la sesion, -1 si no está vinculado, null si no es repartidor. */
    private function repartidorDeLaSesion(): ?int
    {
        if (! in_array(session('rol'), ['repartidor', 'repartidor_avanzado'], true)) {
            return null;
        }

        return (new RepartidorModel())->idPorUsuario((int) session('id_usuario')) ?? -1;
    }
}