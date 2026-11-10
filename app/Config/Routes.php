<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// ───────── LOGIN / REGISTRO / SESIÓN ─────────
$routes->get('/', 'auth\AuthController::login', ['filter' => 'guest']);
$routes->get('login', 'auth\AuthController::login', ['filter' => 'guest']);
$routes->post('login', 'auth\AuthController::autenticar', ['filter' => 'csrf']);
$routes->post('logout', 'auth\AuthController::logout', ['filter' => 'csrf']);

// ───────── PANELES DE INICIO (uno por rol) ─────────
$routes->get('panel/administrador', 'auth\PanelController::administrador', ['filter' => 'rol:administrador']);
$routes->get('panel/vendedor-avanzado', 'auth\PanelController::vendedorAvanzado', ['filter' => 'rol:vendedor_avanzado']);
$routes->get('panel/repartidor-avanzado', 'auth\PanelController::repartidorAvanzado', ['filter' => 'rol:repartidor_avanzado']);
$routes->get('panel/repartidor', 'auth\PanelController::repartidor', ['filter' => 'rol:repartidor']);
$routes->get('panel/cliente', 'auth\PanelController::cliente', ['filter' => 'rol:cliente']);
$routes->get('conocenos', 'auth\PanelController::conocenos');

// Portal del cliente (solo el rol "cliente"; cada pantalla consulta únicamente SUS datos)
$routes->get('mis_pedidos', 'Usuarios\PortalClienteController::pedidos', ['filter' => 'rol:cliente']);
$routes->get('mi_credito',  'Usuarios\PortalClienteController::credito', ['filter' => 'rol:cliente']);
$routes->get('mis_datos',   'Usuarios\PortalClienteController::datos',   ['filter' => 'rol:cliente']);

// Antigua pantalla principal (antes era '/')
$routes->get('inicio', 'InicioController::index');
// ── CLIENTES: cada grupo exige el permiso de su acción (ver Config/Permisos.php) ──
$routes->group('', ['filter' => 'permiso:clientes.ver'], function ($routes) {
    $routes->get('clientes',       'Usuarios\ClienteController::cliente');
    $routes->get('crud_cliente',   'Usuarios\ClienteController::cliente');
    $routes->get('tabla_clientes', 'Usuarios\ClienteController::tabla_client');
});
$routes->group('', ['filter' => 'permiso:clientes.crear'], function ($routes) {
    $routes->get('alta_clientes',   'Usuarios\ClienteController::registrar_clientes');
    $routes->post('guarda_cliente', 'Usuarios\ClienteController::guardar_cliente');
});
$routes->group('', ['filter' => 'permiso:clientes.editar'], function ($routes) {
    $routes->get('editarc/(:num)', 'Usuarios\ClienteController::form_editc/$1');
    $routes->post('actualizac',    'Usuarios\ClienteController::actualizarc');
});
$routes->group('', ['filter' => 'permiso:clientes.eliminar'], function ($routes) {
    $routes->get('verificar_cliente/(:num)', 'Usuarios\ClienteController::verificarCliente/$1');
    $routes->get('eliminarc/(:num)',         'Usuarios\ClienteController::borrarc/$1');
});
$routes->group('', ['filter' => 'permiso:usuarios.ver'], function ($routes) {
    $routes->get('vendedores', 'Usuarios\VendedorController::vendedor');
});
$routes->group('', ['filter' => 'permiso:usuarios.crear'], function ($routes) {
    $routes->get('alta_vendedores',   'Usuarios\VendedorController::registrar_vendedores');
    $routes->post('guardar_vendedor', 'Usuarios\VendedorController::guardar_vendedor');
});
$routes->get("/entradas","Inventario\EntradaController::entrada");


//CLIENTES




//PRODUCTOS
$routes->group('', ['filter' => 'permiso:productos.ver'], function ($routes) {
    $routes->get("lista_productos","Inventario\ProductoController::listar_productos");
    $routes->get("alta_productos","Inventario\ProductoController::registrar_productos");

    $routes -> post("guarda_producto","Inventario\ProductoController::guardar_producto");


    $routes -> get("/eliminar/(:num)", "Inventario\ProductoController::borrar/$1");
    $routes->get('verificar_producto/(:num)', 'Inventario\ProductoController::verificarProducto/$1');


    $routes -> get("editar/(:num)","Inventario\ProductoController::recupera/$1");
    $routes -> post('/actualiza','Inventario\ProductoController::actualizar');

    $routes->get('tabla_productos', 'Inventario\ProductoController::tabla_productos');
    $routes->post("guardar_producto","Inventario\ProductoController::guardar_producto");

    $routes->get('historial_entradas/(:num)', 'Inventario\ProductoController::historialEntradas/$1');
});


//entradas
$routes->group('', ['filter' => 'permiso:entradas.ver'], function ($routes) {
    $routes->get('/alta_entrada', 'Inventario\EntradaController::registrar_entradas');
    $routes->post("guardar_entrada","Inventario\EntradaController::guardar_entrada");
    $routes->get('/tabla_entradas', 'Inventario\EntradaController::tabla_entradas');
    $routes -> get("editaren/(:num)","Inventario\EntradaController::form_editent/$1");
    $routes -> post('/actualizarentrada','Inventario\EntradaController::actualizarent');

    $routes->get('verificar_entrada/(:num)', 'Inventario\EntradaController::verificarEntrada/$1');
    $routes -> get("/eliminare/(:num)", "Inventario\EntradaController::borrarEn/$1");

    $routes->get('historial_mermas/(:num)', 'Inventario\EntradaController::historialMermas/$1');
});

// ── REPARTIDORES: cada grupo exige el permiso de su acción (ver Config/Permisos.php) ──
$routes->group('', ['filter' => 'permiso:repartidores.ver'], function ($routes) {
    $routes->get('crud_repartidores',  'Usuarios\RepartidorController::repartidor');
    $routes->get('tabla_repartidores', 'Usuarios\RepartidorController::tabla_rep');
});
$routes->group('', ['filter' => 'permiso:repartidores.crear'], function ($routes) {
    $routes->get('alta_repartidores', 'Usuarios\RepartidorController::registrar_repartidores');
    $routes->post('guardar_repar',    'Usuarios\RepartidorController::guardar_repart');
});
$routes->group('', ['filter' => 'permiso:repartidores.editar'], function ($routes) {
    $routes->get('editarrep/(:num)', 'Usuarios\RepartidorController::form_editr/$1');
    $routes->post('actualizarepart', 'Usuarios\RepartidorController::actualizarrep');
});
$routes->group('', ['filter' => 'permiso:repartidores.eliminar'], function ($routes) {
    $routes->get('verificar_repartidor/(:num)', 'Usuarios\RepartidorController::verificarRepartidor/$1');
    $routes->get('eliminarrep/(:num)',          'Usuarios\RepartidorController::borrarrep/$1');
});



/** merma */
$routes->group('', ['filter' => 'permiso:merma.ver'], function ($routes) {
    $routes -> get("merma","Inventario\MermaController::listar_merma");
    $routes -> get("alta_merma","Inventario\MermaController::registrar_merma");

    $routes->post("guardar_merma", "Inventario\MermaController::guarda_merma");

    $routes -> get("/eliminar_merma/(:num)", "Inventario\MermaController::borrar_merma/$1");

    $routes -> get("editar_merma/(:num)","Inventario\MermaController::recupera_merma/$1");
    $routes -> post('/actualizar_merma','Inventario\MermaController::actualiza_merma');

    $routes->get('tabla_merma', 'Inventario\MermaController::tabla_merma');
});

//EXISTENCIAS
$routes->group('', ['filter' => 'permiso:existencias.ver'], function ($routes) {
    $routes->get('existencias', 'Inventario\ExistenciasController::index');
    $routes -> get("alta_existencias","Inventario\MermaController::registrar_existencias");
});

//CONTROL DE PEDIDOS
$routes->get('control_pedidos', 'Pedidos\PedidoController::listar_pedidos', ['filter' => 'permiso:pedidos.ver']);
$routes->get("alta_pedido",'Pedidos\PedidoController::registra_pedido', ['filter' => 'permiso:pedidos.crear']);
// Nuevo pedido como página completa (con menú lateral). Exige el permiso de crear pedidos.
$routes->get('nuevo_pedido', 'Pedidos\PedidoController::nuevo_pedido', ['filter' => 'permiso:pedidos.crear']);
$routes->post("guardar_pedido","Pedidos\PedidoController::guarda_pedido", ['filter' => 'permiso:pedidos.crear']);
$routes->post('actualizar_status', 'Pedidos\PedidoController::actualizar_status', ['filter' => 'permiso:pedidos.cambiar_estado']);

$routes->get("editarpedido/(:num)","Pedidos\PedidoController::editar_pedido/$1", ['filter' => 'permiso:pedidos.editar']);
$routes->post('actualizar_pedido',   'Pedidos\PedidoController::actualizar', ['filter' => 'permiso:pedidos.editar']);

$routes->get('copiar_pedido/(:num)', 'Pedidos\PedidoController::copiar_pedido/$1', ['filter' => 'permiso:pedidos.crear']);

//SEGUIMIENTO DE PEDIDOS
$routes->get('seguimiento_pedidos', 'Pedidos\SeguimientoController::seguimiento',
    ['filter' => 'permiso:pedidos.ver,pedidos.ver_propios']);

$routes->post('confirmar_pedido', 'Pedidos\SeguimientoController::confirmar_pedido', ['filter' => 'permiso:pedidos.asignar_repartidor']);
$routes->post('marcar_pagado',    'Pedidos\SeguimientoController::marcar_pagado',    ['filter' => 'permiso:pedidos.pagos']);

$routes->post('detalle_pedido', 'Pedidos\SeguimientoController::detalle_pedido',
    ['filter' => 'permiso:pedidos.detalle,pedidos.detalle_asignados,pedidos.detalle_propios']);

//GESTIÓN DE ENTREGAS
$routes->get('gestion_entregas', 'Pedidos\EntregaController::entregas', ['filter' => 'permiso:entregas.ver_pendientes']);
$routes->get('entregas',        'Pedidos\EntregaController::entregas', ['filter' => 'permiso:entregas.ver_pendientes']);
$routes->get('historial_entregas', 'Pedidos\EntregaController::historial', ['filter' => 'permiso:entregas.ver_historial']);
$routes->post('asignar_repartidor', 'Pedidos\EntregaController::asignar_repartidor', ['filter' => 'permiso:pedidos.editar']);

//ruta sin filtro para el saber si sigue la sesión o no
$routes->get('sesion/estado', 'auth\AuthController::estado');

//recorrido del día
$routes->get('recorrido_dia', 'Pedidos\RecorridoController::index', ['filter' => 'permiso:entregas.recorrido, entregas.ver_arboles']);
$routes->get('recorrido_arboles', 'Pedidos\RecorridoController::arboles', ['filter' => 'permiso:entregas.recorrido,entregas.ver_arbol']);
$routes->post('recorrido_iniciar', 'Pedidos\RecorridoController::iniciar', ['filter' => 'permiso:entregas.recorrido']);