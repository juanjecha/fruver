<?php

if (!function_exists('ubicacion_parsear')) {
    /**
     * Convierte "lat,lng" en ['lat' => float, 'lng' => float].
     * Devuelve null si no es una coordenada válida
     */
    function ubicacion_parsear(?string $valor): ?array
    {
        $valor = trim((string) $valor);

        if (! preg_match('/^(-?\d{1,2}(?:\.\d+)?),\s*(-?\d{1,3}(?:\.\d+)?)$/', $valor, $m)) {
            return null;
        }

        $lat = (float) $m[1];
        $lng = (float) $m[2];

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }
}

if (!function_exists('ubicacion_formatear')) {
    // Arma el par que se guarda en la base de datos
    function ubicacion_formatear(float $lat, float $lng) : string
    {
        return number_format($lat, 6, '.', '') . ',' . number_format($lng, 6, '.', '');
    }
}

if (!function_exists('ubicacion_mostrar')) {
    //coordenadas con enlace a google maps
    function ubicacion_mostrar(?string $valor) : string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return "No específicada";
        }

        $punto = ubicacion_parsear($valor);

        if ($punto === null) {
            return esc($valor) . ' <span class="badge bg-warning text-dark"> sin coordenadas </span>';
        }

        $texto = ubicacion_formatear($punto['lat'], $punto['lng']);
        $url = "https://www.google.com/maps/search/?api=1&query=" . rawurlencode($texto);

        return '<a href ="' . esc($url, 'attr') . '" target="_blank" rel="noopener"> ' . esc($texto). '</a>';
    }
}