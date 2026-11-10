<?php

namespace App\Libraries\Algoritmos\Distancia;

use Override;

class Haversine implements CalculadorDistancia {
    private const RADIO_TIERRA_M = 6371000.0;

    //Distancia en metros entre dos puntos
    public function entre(array $a, array $b) : float
    {
        $lat1 = deg2rad($a['lat']);
        $lat2 = deg2rad($b['lat']);
        $dLat = $lat2 - $lat1;
        $dLng = deg2rad($b['lng'] - $a['lng']);

        $h = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng / 2) ** 2;

        return 2 * self::RADIO_TIERRA_M * asin(min(1.0, sqrt($h)));
    }

    #[Override]
    public function matriz(array $puntos): array
    {
        $n = count($puntos);
        $matriz = [];

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                //La distancia es simétrica: es la misma de ida y vuelta
                $matriz[$i][$j] = $j < $i ? $matriz[$j][$i]
                : ($i === $j ? 0.0 : $this->entre($puntos[$i], $puntos[$j]));
            }
        }

        return $matriz;
    }
}