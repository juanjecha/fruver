<?php

namespace App\Libraries\Algoritmos\Grafos;

use App\Libraries\Algoritmos\Distancia\CalculadorDistancia;

class ArbolEntregas
{
    public function __construct(private CalculadorDistancia $calculador)
    {
    }

    /**
     * @param array{lat: float, lng: float} $salida punto desde el que sale el repartidor
     * @param list<array{id_pedido: int, lat: float, lng: float}> $paradas
     * @return array{
     *     nodos: list<array{indice:int, tipo:string, id_pedido:?int, lat:float, lng:float}>,
     *     aristas: list<array{desde:int, hasta:int, metros:float}>,
     *     costo_metros: float,
     *     desconectados: list<int>
     * }
     */
    public function calcular(array $salida, array $paradas): array
    {
        $nodos = [['indice' => 0, 'tipo' => 'salida', 'id_pedido' => null, 'lat' => $salida['lat'], 'lng' => $salida['lng']]];

        foreach (array_values($paradas) as $i => $p) {
            $nodos[] = [
                'indice'    => $i + 1,
                'tipo'      => 'pedido',
                'id_pedido' => (int) $p['id_pedido'],
                'lat'       => $p['lat'],
                'lng'       => $p['lng'],
            ];
        }

        $matriz = $this->calculador->matriz($nodos);
        $prim   = Prim::arbol($matriz, 0);

        $aristas = array_map(
            static fn (array $a) => ['desde' => $a['desde'], 'hasta' => $a['hasta'], 'metros' => $a['peso']],
            $prim['aristas']
        );

        return [
            'nodos'         => $nodos,
            'aristas'       => $aristas,
            'costo_metros'  => $prim['costo'],
            'desconectados' => $prim['desconectados'],
        ];
    }
}