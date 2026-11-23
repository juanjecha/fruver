<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Estructura del menú. Un solo lugar para:
 *  - las tarjetas del inicio (una por módulo),
 *  - el menú lateral de cada módulo (sus opciones + accesos rápidos a otros módulos).
 *
 * Reglas:
 *  - 'permisos' del módulo: si el rol no tiene NINGUNO, ni la tarjeta ni el módulo aparecen.
 *  - 'permisos' de cada opción: si el rol no tiene ninguno, esa opción no se muestra.
 *  - 'url': ruta a la que lleva la opción.
 *  - 'activo_en': otras rutas que también deben dejar esta opción resaltada
 *                 (alta, editar, créditos...). Admite * como comodín.
 */
class Menu extends BaseConfig
{
    public array $modulos = [

        'clientes' => [
            'titulo'   => 'Clientes',
            'icono'    => 'fa-users',
            'entrada'  => 'crud_cliente',
            'permisos' => ['clientes.ver'],
            'items'    => [
                ['etiqueta' => 'Clientes', 'icono' => 'fa-users', 'url' => 'crud_cliente',
                 'permisos' => ['clientes.ver'],
                 'activo_en' => ['clientes', 'alta_clientes', 'editarc/*', 'creditos', 'creditos/*']],
                ['etiqueta' => 'Seguimiento de pedidos', 'icono' => 'fa-truck-fast', 'url' => 'seguimiento_pedidos',
                 'permisos' => ['pedidos.ver', 'pedidos.ver_propios']],
                ['etiqueta' => 'Inventario', 'icono' => 'fa-boxes-stacked', 'url' => 'lista_productos',
                 'permisos' => ['productos.ver']],
                ['etiqueta' => 'Vendedores avanzados', 'icono' => 'fa-user-tie', 'url' => 'vendedores',
                 'permisos' => ['usuarios.ver'], 'activo_en' => ['alta_vendedores']],
            ],
        ],

        // Repartidores: solo administrar repartidores y dar seguimiento a pedidos
        'repartidores' => [
            'titulo'   => 'Repartidores',
            'icono'    => 'fa-motorcycle',
            'entrada'  => 'crud_repartidores',
            'permisos' => ['repartidores.ver'],
            'items'    => [
                ['etiqueta' => 'Repartidores', 'icono' => 'fa-motorcycle', 'url' => 'crud_repartidores',
                 'permisos' => ['repartidores.ver'], 'activo_en' => ['alta_repartidores', 'editarrep/*']],
                ['etiqueta' => 'Seguimiento de pedidos', 'icono' => 'fa-truck-fast', 'url' => 'seguimiento_pedidos',
                 'permisos' => ['pedidos.ver', 'pedidos.ver_propios']],
            ],
        ],

        'inventario' => [
            'titulo'   => 'Inventario',
            'icono'    => 'fa-boxes-stacked',
            'entrada'  => 'lista_productos',
            'permisos' => ['productos.ver'],
            'items'    => [
                ['etiqueta' => 'Productos', 'icono' => 'fa-leaf', 'url' => 'lista_productos',
                 'permisos' => ['productos.ver'], 'activo_en' => ['alta_productos', 'editar/*']],
                ['etiqueta' => 'Entradas', 'icono' => 'fa-box-open', 'url' => 'entradas',
                 'permisos' => ['entradas.ver'], 'activo_en' => ['alta_entrada', 'editaren/*']],
                ['etiqueta' => 'Existencias', 'icono' => 'fa-warehouse', 'url' => 'existencias',
                 'permisos' => ['existencias.ver']],
                ['etiqueta' => 'Merma', 'icono' => 'fa-trash-can', 'url' => 'merma',
                 'permisos' => ['merma.ver'], 'activo_en' => ['alta_merma', 'editar_merma/*']],
            ],
        ],

        'pedidos' => [
            'titulo'   => 'Pedidos',
            'icono'    => 'fa-box-open',
            'entrada'  => 'control_pedidos',
            'permisos' => ['pedidos.ver'],
            'items'    => [
                ['etiqueta' => 'Control de Pedidos', 'icono' => 'fa-box-open', 'url' => 'control_pedidos',
                 'permisos' => ['pedidos.ver'], 'activo_en' => ['alta_pedido', 'nuevo_pedido', 'editarpedido/*']],
                ['etiqueta' => 'Seguimiento de pedidos', 'icono' => 'fa-truck-fast', 'url' => 'seguimiento_pedidos',
                 'permisos' => ['pedidos.ver', 'pedidos.ver_propios']],
                 ['etiqueta' => 'Gestión de Pedidos', 'icono' => 'fa-truck', 'url' => 'gestion_entregas',
                'permisos' => ['entregas.ver_pendientes'], 'activo_en' => ['entregas', 'gestion_entregas']],
                ['etiqueta' => 'Recorrido del día', 'icono' => 'fa-route', 'url' => 'recorrido_dia',
                'permisos' => ['entregas.recorrido', 'entregas.ver_arboles'], 'activo_en' => ['recorrido_dia']],
                ['etiqueta' => 'Historial de Pedidos Entregados', 'icono' => 'fa-clock-rotate-left', 'url' => 'historial_entregas',
                 'permisos' => ['entregas.ver_historial'], 'activo_en' => ['historial_entregas']],
            ],
        ],

        // Menú del cliente. Se protege con permisos que SOLO tiene el rol cliente
        // ('pedidos.*' del administrador también cubriría 'pedidos.ver_propios').
        'cliente' => [
            'titulo'   => 'Mi cuenta',
            'icono'    => 'fa-user',
            'entrada'  => 'mis_pedidos',
            'permisos' => ['pagos.ver_propios'],
            'items'    => [
                ['etiqueta' => 'Pedidos', 'icono' => 'fa-motorcycle', 'url' => 'mis_pedidos',
                 'permisos' => ['pagos.ver_propios']],
                ['etiqueta' => 'Crédito', 'icono' => 'fa-credit-card', 'url' => 'mi_credito',
                 'permisos' => ['pagos.ver_propios']],
                ['etiqueta' => 'Mis datos', 'icono' => 'fa-circle-user', 'url' => 'mis_datos',
                 'permisos' => ['pagos.ver_propios']],
            ],
        ],

        'entregas' => [
            'titulo'   => 'Entregas',
            'icono'    => 'fa-truck',
            'entrada'  => 'gestion_entregas',
            'permisos' => ['entregas.ver_pendientes'],
            'items'    => [
                ['etiqueta' => 'Gestión de entregas', 'icono' => 'fa-truck', 'url' => 'gestion_entregas',
                'permisos' => ['entregas.ver_pendientes'], 'activo_en' => ['entregas']],
                ['etiqueta' => 'Recorrido del día', 'icono' => 'fa-route', 'url' => 'recorrido_dia',
                 'permisos' => ['entregas.recorrido', 'entregas.ver_arboles'], 'activo_en' => ['recorrido_dia']],
            ],
        ],
    ];
}
