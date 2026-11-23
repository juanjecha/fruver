<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * QUÉ puede hacer cada rol (la matriz de roles pasada a código).
 * Formato de cada permiso:  modulo.accion   ->  'clientes.ver', 'pedidos.cancelar'
 * Comodín:                  'clientes.*'    ->  todas las acciones de ese módulo
 *
 * Para dar o quitar algo a un rol SOLO se edita este archivo.
 * El menú, las rutas (filtro "permiso:") y los botones de las vistas (puede()) leen de aquí.
 */
class Permisos extends BaseConfig
{
    public array $porRol = [

        'administrador' => [
            'clientes.*', 'repartidores.*', 'productos.*', 'entradas.*', 'merma.*',
            'usuarios.*',
            'existencias.ver',
            'pedidos.*', 'pedidos.pagos', 'pedidos.asignar_repartidor',
            'creditos.dar', 'creditos.registrar_bonos', 'creditos.ver_deudores',
            'notificaciones.ver',
            'entregas.ver_arboles', 
        ],

        'vendedor_avanzado' => [
            'productos.*', 'entradas.*', 'merma.*',
            'existencias.ver',
            'pedidos.*', 'pedidos.pagos', 'pedidos.asignar_repartidor',
            'creditos.dar', 'creditos.registrar_bonos',
            'notificaciones.ver',
            'entregas.ver_arboles', 
        ],

        'repartidor_avanzado' => [
            // Puede crear y consultar pedidos, pero no editar/eliminar pedidos ni gestionar pagos.
            // Los estados de entrega permitidos se validan también en PedidoController.
            'pedidos.buscar',
            'pedidos.ver', 'pedidos.cambiar_estado', 'pedidos.detalle',
            'entregas.ver_pendientes',
            'notificaciones.ver', 'privacidad.ver',
            'entregas.recorrido',  
        ],

        'repartidor' => [
            'pedidos.detalle_asignados',          // alcance: solo los asignados a él
            'pedidos.cambiar_estado',
            'entregas.ver_pendientes',
            'notificaciones.ver', 'privacidad.ver',
            'entregas.recorrido',  
        ],

        'cliente' => [
            'pedidos.ver_propios',                // alcance: solo los suyos
            'pedidos.detalle_propios',
            'pagos.ver_propios',
            'notificaciones.ver', 'privacidad.ver',
            // POR CONFIRMAR (no están en la matriz, pero tu flujo los necesita):
            // 'pedidos.solicitar', 'productos.catalogo',
        ],
    ];
}
