<?php

namespace App\Libraries\Algoritmos\Grafos;

class ArbolesDelDia {
    public function __construct(private ArbolEntregas $arbol)
    {
    }

    /**
     * @param list<array> $pedidos filas de PedidoMOdel::getPedidosEntregaHoy()
     * @param array<int, array{lat: float, lng: float}> $salidas punto de salida de hoy por id_repartidor
     * @return array{grupos: list<array>, sin_repartidor: int}
     */
    public function calcular(array $pedidos, array $salidas, ?array $salidaPorDefecto = null): array
    {
        helper('ubicacion');

        $grupos = [];
        $sinRepartidor = 0;

        //Agrupar pedidos pendientes por repartidor
        foreach ($pedidos as $p) {
            if (($p['status'] ?? '') === 'entregado') {
                continue;       //si ya se entregó entonces no se agrega a la lista de visitas pendientes
            }
            if(empty($p['id_repartidor'])) {
                $sinRepartidor++;
                continue;
            }

            $id = (int) $p['id_repartidor'];
            if (!isset($grupos[$id])) {
                $nombre = trim((string) ($p['repartidor'] ?? ''));
                $grupos[$id] = [
                    'id_repartidor' => $id,
                    'repartidor' => $nombre !== '' ? $nombre : 'Repartidor ' . $id,
                    'salida' => null,
                    'salida_por_defecto' => false,
                    'paradas' => [],
                    'sin_ubicacion' => [],
                    'arbol' => null,
                ];
            }

            $cliente = (string) ($p['cliente'] ?? '');
            $punto = ubicacion_parsear($p['direccion_pedido'] ?? null);

            if ($punto === null) {
                $grupos[$id]['sin_ubicacion'][] = ['id_pedido' => (int) $p['id_pedido'], 'cliente' => $cliente];
                continue;
            }

            $grupos[$id]['paradas'][] = [
                'id_pedido' => (int) $p['id_pedido'],
                'cliente' => $cliente,
                'status' => (string) ($p['status'] ?? 'solicitado'),
                'lat' => $punto['lat'],
                'lng' => $punto['lng'],
            ];
        }

        //cuando el repartidor elija su salida, se genera el árbol
        foreach (array_keys($grupos) as $id) {
            $salida = $salidas[$id] ?? null;
            $porDefecto = false;

            if ($salida === null && $salidaPorDefecto !== null) {
                $salida = $salidaPorDefecto;
                $porDefecto = true;
            }

            $grupos[$id]['salida'] = $salida;
            $grupos[$id]['salida_por_defecto'] = $porDefecto;

            if ($salida === null || $grupos[$id]['paradas'] === []) {
                continue;
            }

            $r = $this->arbol->calcular($salida, $grupos[$id]['paradas']);

            $grupos[$id]['arbol'] = [
                'nodos' => $r['nodos'],
                'aristas' => array_map(
                    static fn (array $a) => ['desde' => $a['desde'], 'hasta' => $a['hasta'], 'metros' => round($a['metros'], 1)],
                    $r['aristas']
                ),
                'costo_metros' => round($r['costo_metros'], 1),
            ];
        }

        ksort($grupos);

        return ['grupos' => array_values($grupos), 'sin_repartidor' => $sinRepartidor];
    }
}