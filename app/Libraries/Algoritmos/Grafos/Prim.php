<?php

namespace App\Libraries\Algoritmos\Grafos;

class Prim {
    /**
     * @param array<int, array<int, float|null>> $matriz matriz[i][j] = peso de la arista i-j
     *        null (o INF) significa "no hay camino directo"
     * @param int $raiz vertice con el que empieza el arbol
     * @return array{
     * aristas: lis<array{desde:int, hasta:int, peso:float}>,
     * costo:float,
     * desconectados: list<int>
     * }
     */
    public static function arbol(array $matriz, int $raiz = 0): array
    {
        $n = count($matriz);

        if ($n === 0) {
            return ['aristas' => [], 'costo' => 0.0, 'desconectados' => []];
        }
        if ($raiz < 0 || $raiz >= $n) {
            throw new \InvalidArgumentException("La raíz $raiz no existe ()hay $n vértices");
        }
        foreach ($matriz as $fila) {
            if (count($fila) !== $n) {
                throw new \InvalidArgumentException('La matriz debe ser cuadrada');
            }
        }

        $enArbol = array_fill(0, $n, false);
        $costo = array_fill(0, $n, INF);
        $padre = array_fill(0, $n, -1);

        $costo[$raiz] = 0.0;
        $aristas = [];
        $total = 0.0;

        for ($paso = 0; $paso < $n; $paso++) {
            // Elegir el vértice de menor costo que no esté incluido en el árbol
            $u = -1;
            for ($v = 0; $v < $n; $v++) {
                if (! $enArbol[$v] && ($u === -1 || $costo[$v] < $costo[$u])) {
                    $u = $v;
                }
            }

            //Si lo mejor que queda es INF, los demás no se pueden alcanzar
            if ($u === -1 || $costo[$u] === INF) {
                break;
            }

            //Guardar arista que conecta
            $enArbol[$u] = true;
            if ($padre[$u] !== -1) {
                $aristas[] = ['desde' => $padre[$u], 'hasta' => $u, 'peso' => $costo[$u]];
                $total += $costo[$u];
            }

            //revisar otra vez con el vértice recien incluido
            for ($v = 0; $v < $n; $v++) {
                $peso = $matriz[$u][$v];
                if (!$enArbol[$v] && $peso !== null && $peso < $costo[$v]) {
                    $costo[$v] = $peso;
                    $padre[$v] = $u;
                }
            }
        }
        $desconectados = [];
        for ($v = 0; $v < $n; $v++) {
            if (! $enArbol[$v]) {
                $desconectados[] = $v;
            }
        }

        return ['aristas' => $aristas, 'costo' => $total, 'desconectados' => $desconectados];
    }
}