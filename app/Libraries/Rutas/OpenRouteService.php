<?php

namespace App\Libraries\Rutas;

class OpenRouteService
{
    private int $nuevas = 0;

    public function __construct(
        private string $apiKey,
        private string $perfil = 'driving-car',
        private int $maxNuevas = 30
    ) {
    }

    /**
     * Ruta por calles entre dos puntos.
     * @return array{puntos: list<array{0: float, 1: float}>, metros: float}|null  null = usar línea recta
     */
    public function ruta(array $a, array $b): ?array
    {
        if ($this->apiKey === '') {
            return null;
        }

        $clave = 'ors_' . md5($this->perfil . sprintf('%.5f,%.5f|%.5f,%.5f', $a['lat'], $a['lng'], $b['lat'], $b['lng']));

        try {
            $guardada = cache($clave);
        } catch (\Throwable $e) {
            log_message('error', 'ORS cache: ' . $e->getMessage());
            $guardada = null;
        }
        if ($guardada !== null) {
            return $guardada;
        }

        if ($this->nuevas >= $this->maxNuevas) {
            return null; // se completará en la siguiente recarga
        }
        $this->nuevas++;

        try {
            $resp = service('curlrequest')->post(
                'https://api.openrouteservice.org/v2/directions/' . $this->perfil . '/geojson',
                [
                    'headers'     => ['Authorization' => $this->apiKey, 'Content-Type' => 'application/json'],
                    'body'        => json_encode(['coordinates' => [[$a['lng'], $a['lat']], [$b['lng'], $b['lat']]]]),
                    'timeout'     => 8,
                    'http_errors' => false,
                ]
            );
        } catch (\Throwable $e) {
            log_message('error', 'ORS: ' . $e->getMessage());
            return null;
        }

        if ($resp->getStatusCode() !== 200) {
            log_message('error', 'ORS ' . $resp->getStatusCode() . ': ' . $resp->getBody());
            return null;
        }

        $json = json_decode($resp->getBody(), true);
        $f = $json['features'][0] ?? null;
        if (! $f) {
            return null;
        }

        // ORS devuelve [lng, lat]; Leaflet usa [lat, lng]
        $ruta = [
            'puntos' => array_map(static fn ($c) => [$c[1], $c[0]], $f['geometry']['coordinates']),
            'metros' => (float) ($f['properties']['summary']['distance'] ?? 0),
        ];

        try {
            cache()->save($clave, $ruta, 7 * DAY);
        } catch (\Throwable $e) {
            log_message('error', 'ORS cache: ' . $e->getMessage());
        }

        return $ruta;
    }
}