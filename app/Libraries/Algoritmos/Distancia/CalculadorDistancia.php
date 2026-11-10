<?php

namespace App\Libraries\Algoritmos\Distancia;

interface CalculadorDistancia {
    /**
     * @param  array<int, array{lat: float, lng: float}> $puntos
     * @return array<int, array<int, float>> matriz[i][j] = distancia en metros de i a j
     */
    public function matriz(array $puntos): array;
}