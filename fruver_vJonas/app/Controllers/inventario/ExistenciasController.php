<?php

namespace App\Controllers\inventario;
use App\Models\ExistenciasGraficaModel;
use App\Controllers\BaseController;

class ExistenciasController extends BaseController
{
    protected $graficaModel;

    public function __construct()
    {
        $this->graficaModel = new ExistenciasGraficaModel();
    }

    public function index()
    {
        // --- Datos para gráfica general ---
        $resumen = $this->graficaModel->getResumenPorFecha(); // Totales generales de unidades para venta y bloqueadas
        $mermas  = $this->graficaModel->getMermaPorFecha();// Total de unidades perdidas

        // // Datos para las tarjetas resumen y la lista de productos
        $resumenExistencias = $this->graficaModel->getResumen();


        //Array de todo lo que se va a mostrar en la vista cada clave del array del modelo se cnvierte en una var psrs la vista
        $datos = [

            /*son_enconde convierte un valor php a formato JSON string
            la vista lo imprime directamente en el js con const labels
            */
            'chart_labels'     => json_encode(['General']),
            'chart_ventas'     => json_encode([(float) $resumen['total_venta']]),
            'chart_bloqueadas' => json_encode([(float) $resumen['total_bloqueadas']]),
            'chart_merma'      => json_encode([(float) $mermas['total_merma']]),

            // Datos para tarjetas de resumen (números enteros, no necesitan json_encode)
            'total_productos'  => $resumenExistencias['total_productos'],
            'sin_stock'        => $resumenExistencias['sin_stock'],
            'stock_bajo'       => $resumenExistencias['stock_bajo'],

            // Lista completa de productos con sus existencias
            'productos'        => $resumenExistencias['productos'],

             // Lista de mermas agrupadas por producto para mostrar en las tarjetaas
            'mermas_lista'     => $this->graficaModel->getMermasPorProducto(),
        ];

        $datos['titulo'] = 'Existencias';

        return $this->render('inventario/existencias/v_principal_existencias', $datos, 'inventario');
    }
}