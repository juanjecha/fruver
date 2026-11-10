<?php

use App\Libraries\Algoritmos\Distancia\Haversine;
use App\Libraries\Algoritmos\Grafos\ArbolEntregas;
use App\Libraries\Algoritmos\Grafos\Prim;
use PHPUnit\Framework\TestCase;

final class PrimTest extends TestCase
{
    /** Grafo clásico de 5 vértices; su MST pesa 16 (aristas 0-1:2, 1-2:3, 1-4:5, 0-3:6). */
    private function grafoEjemplo(): array
    {
        $x = null;
        return [
            [0, 2, $x, 6, $x],
            [2, 0, 3, 8, 5],
            [$x, 3, 0, $x, 7],
            [6, 8, $x, 0, 9],
            [$x, 5, 7, 9, 0],
        ];
    }

    public function testCostoMinimoDelGrafoEjemplo(): void
    {
        $r = Prim::arbol($this->grafoEjemplo(), 0);

        $this->assertSame(16.0, $r['costo']);
        $this->assertCount(4, $r['aristas']);           // un árbol de 5 vértices tiene 4 aristas
        $this->assertSame([], $r['desconectados']);
    }

    public function testElCostoNoDependeDeLaRaiz(): void
    {
        foreach (range(0, 4) as $raiz) {
            $this->assertSame(16.0, Prim::arbol($this->grafoEjemplo(), $raiz)['costo']);
        }
    }

    public function testUnSoloVerticeNoTieneAristas(): void
    {
        $r = Prim::arbol([[0]], 0);
        $this->assertSame([], $r['aristas']);
        $this->assertSame(0.0, $r['costo']);
    }

    public function testGrafoVacio(): void
    {
        $this->assertSame(['aristas' => [], 'costo' => 0.0, 'desconectados' => []], Prim::arbol([]));
    }

    public function testVerticesInalcanzablesSeReportan(): void
    {
        $x = null;
        $r = Prim::arbol([[0, 1, $x], [1, 0, $x], [$x, $x, 0]], 0);

        $this->assertSame(1.0, $r['costo']);
        $this->assertSame([2], $r['desconectados']);
    }

    public function testRaizInvalidaLanzaExcepcion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Prim::arbol([[0, 1], [1, 0]], 5);
    }

    public function testMatrizNoCuadradaLanzaExcepcion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Prim::arbol([[0, 1], [1]], 0);
    }

    public function testHaversineZocaloAlAngelEsAproximadamente(): void
    {
        // Zócalo de la CDMX -> Ángel de la Independencia: ~3.5 km en línea recta
        $m = (new Haversine())->entre(['lat' => 19.4326, 'lng' => -99.1332], ['lat' => 19.4270, 'lng' => -99.1677]);
        $this->assertEqualsWithDelta(3600, $m, 200);
    }

    public function testHaversineMismoPuntoEsCero(): void
    {
        $p = ['lat' => 19.04, 'lng' => -98.2];
        $this->assertSame(0.0, (new Haversine())->entre($p, $p));
    }

    public function testArbolEntregasConectaTodasLasParadas(): void
    {
        $salida  = ['lat' => 19.0414, 'lng' => -98.2063];
        $paradas = [
            ['id_pedido' => 10, 'lat' => 19.0450, 'lng' => -98.2000],
            ['id_pedido' => 11, 'lat' => 19.0500, 'lng' => -98.1900],
            ['id_pedido' => 12, 'lat' => 19.0300, 'lng' => -98.2200],
        ];

        $r = (new ArbolEntregas(new Haversine()))->calcular($salida, $paradas);

        $this->assertCount(4, $r['nodos']);              // salida + 3 pedidos
        $this->assertCount(3, $r['aristas']);
        $this->assertSame('salida', $r['nodos'][0]['tipo']);
        $this->assertSame([], $r['desconectados']);
        $this->assertGreaterThan(0, $r['costo_metros']);
    }

    public function testArbolEntregasSinPedidosSoloTieneLaSalida(): void
    {
        $r = (new ArbolEntregas(new Haversine()))->calcular(['lat' => 19.0, 'lng' => -98.0], []);
        $this->assertCount(1, $r['nodos']);
        $this->assertSame([], $r['aristas']);
    }
}