<?php

use App\Libraries\Algoritmos\Distancia\Haversine;
use App\Libraries\Algoritmos\Grafos\ArbolEntregas;
use App\Libraries\Algoritmos\Grafos\ArbolesDelDia;
use PHPUnit\Framework\TestCase;

final class ArbolesDelDiaTest extends TestCase
{
    private function servicio(): ArbolesDelDia
    {
        return new ArbolesDelDia(new ArbolEntregas(new Haversine()));
    }

    private function pedido(int $id, ?int $repartidor, string $dir, string $status = 'confirmado'): array
    {
        return [
            'id_pedido' => $id, 'id_repartidor' => $repartidor, 'repartidor' => $repartidor ? "Rep $repartidor" : null,
            'cliente' => "Cliente $id", 'status' => $status, 'direccion_pedido' => $dir,
        ];
    }

    public function testCadaRepartidorTieneSuPropioArbol(): void
    {
        $pedidos = [
            $this->pedido(1, 7, '19.0450,-98.2000'),
            $this->pedido(2, 7, '19.0500,-98.1900'),
            $this->pedido(3, 8, '19.0300,-98.2200'),
        ];
        $salidas = [7 => ['lat' => 19.0414, 'lng' => -98.2063], 8 => ['lat' => 19.0400, 'lng' => -98.2100]];

        $r = $this->servicio()->calcular($pedidos, $salidas);

        $this->assertCount(2, $r['grupos']);
        $this->assertCount(2, $r['grupos'][0]['arbol']['aristas']);   // salida + 2 pedidos -> 2 aristas
        $this->assertCount(1, $r['grupos'][1]['arbol']['aristas']);   // salida + 1 pedido  -> 1 arista
    }

    public function testSinSalidaNoHayArbol(): void
    {
        $r = $this->servicio()->calcular([$this->pedido(1, 7, '19.0450,-98.2000')], []);

        $this->assertNull($r['grupos'][0]['salida']);
        $this->assertNull($r['grupos'][0]['arbol']);
        $this->assertCount(1, $r['grupos'][0]['paradas']);
    }

    public function testPedidosConDireccionViejaEnTextoNoEntranAlArbol(): void
    {
        $pedidos = [$this->pedido(1, 7, '19.0450,-98.2000'), $this->pedido(2, 7, 'Calle Tlaloc 2107')];
        $r = $this->servicio()->calcular($pedidos, [7 => ['lat' => 19.04, 'lng' => -98.2]]);

        $this->assertCount(1, $r['grupos'][0]['paradas']);
        $this->assertSame(2, $r['grupos'][0]['sin_ubicacion'][0]['id_pedido']);
    }

    public function testEntregadosYSinRepartidorSeExcluyen(): void
    {
        $pedidos = [
            $this->pedido(1, 7, '19.0450,-98.2000', 'entregado'),
            $this->pedido(2, null, '19.0500,-98.1900'),
        ];
        $r = $this->servicio()->calcular($pedidos, []);

        $this->assertSame([], $r['grupos']);
        $this->assertSame(1, $r['sin_repartidor']);
    }
}