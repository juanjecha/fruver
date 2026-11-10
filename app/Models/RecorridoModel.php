<?php

use CodeIgniter\Model;

class RecorridoModel extends Model {
    protected $table = 'recorrido';
    protected $primaryKey = 'id_recorrido';
    protected $returnType = 'array';
    protected $allowedFields = ['id_repartidor', 'fecha', 'lat', 'lng'];

    //salidas para todos los repartidores en la fecha de hoy
    public function salidasDeHoy(): array
    {
        $filas = $this->db->query(
            "SELECT id_repartidor, lat,lng FROM recorrido WHERE fecha = CURDATE()"
        )->getResultArray();

        $salidas = [];
        foreach ($filas as $f) {
            $salidas[(int) $f['id_repartidor']] = ['lat' => (float) $f['lat'], 'lng' => (float) $f['lng']];
        }

        return $salidas;
    }

    public function guardarSalidaHoy(int $idRepartidor, float $lat, float $lng): bool
    {
        return (bool) $this->db->query(
            "INSERT INTO recorrido (id_repartidor, fecha, lat, lng)
            VALUES (?, CURDATE(), ?, ?)
            ON DUPLICATE KEY UPDATE lat = VALUES(lat), lng = VALUES(lng), iniciando_en = CURRENT_TIMESTAMP",
            [$idRepartidor, $lat, $lng]
        );
    }
}