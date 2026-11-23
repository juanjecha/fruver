<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
class Recorrido extends BaseConfig {
    public float $salidaLat = 19.040100;
    public float $salidaLng = -98.206300;
    public string $salidaNombre = 'Almacén';

    // ---- Estimacion de combustible ----
    /** Rendimiento promedio en km por litro segun el vehículo. */
    public array $rendimientoKmPorLitro = ['moto' => 35.0, 'carro' => 12.0];
    public string $vehiculoPorDefecto = 'moto';
    /** Precio por litro (MXN). Combialo aqui cuando suba o baje la gasolina. */
    public float $precioGasolina = 24.50;
    /** El arbol usa lineas rectas; este factor lo acerca a la distancia real por calles (1.0 = sin ajuste). */
    public float $factorCalles = 1.3;
    /** Semaforo de consumo: hasta litrosBajo = verde, hasta litrosAlto = amarillo, más = rojo. */
    public float $litrosBajo = 1.5;
    public float $litrosAlto = 3.0;


    /** OpenRouteService: trazo por calles. Sin key se usan líneas rectas. */
    public string $orsApiKey = '';
    public string $orsPerfil = 'driving-car';
    public int $orsMaxNuevasPorPeticion = 30;
}