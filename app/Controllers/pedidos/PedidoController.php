<?php

namespace App\Controllers\pedidos;
//enlazar con el modelo
use App\Controllers\BaseController;
use App\Models\PedidoModel;
use App\Models\ClienteModel;
use App\Models\ProductoModel;
use Config\View;

class PedidoController extends BaseController  
{
    public function listar_pedidos(){

        $pedidoModel = new \App\Models\PedidoModel();
        $pedidos     = $pedidoModel->getPedidosCompletos();

        // Agregar productos a cada pedido
        foreach ($pedidos as &$pedido) {
            $pedido['productos'] = $pedidoModel->getProductosDePedido($pedido['id_pedido']);
        }

    
        return $this->render('pedidos/pedido/v_principal_pedidos', [
            'titulo'       => 'Control de pedidos',
            'pedidos'      => $pedidos,
            'repartidores' => $pedidoModel->getRepartidores(),
        ], 'pedidos');
    }

    public function registra_pedido()
    {
        $clienteModel = new ClienteModel();
        $productoModel = new ProductoModel();

        $data = [
            'clientes' => $clienteModel->getClientesLista(),
            'productos' => $productoModel->getProductosLista(),
        ];

        return view('pedidos/pedido/v_alta_pedidos',$data);
    }

    /**
     * "Nuevo pedido" como PÁGINA COMPLETA con menú lateral (ruta: nuevo_pedido).
     * Es lo que abre el botón "Nuevo pedido" del menú lateral.
     *
     * Mismo patrón que ClienteController::cliente():
     *   $this->render('vista', $datos, $modulo)
     * Pasamos $modulo = null a propósito: "Nuevo pedido" vive en varios módulos
     * (Clientes, Repartidores, Pedidos) y así el menú NO cambia: se queda en el
     * módulo desde el que el usuario venía (lo recuerda MenuLateral en sesión).
     */
    public function nuevo_pedido()
    {
        $clienteModel  = new ClienteModel();
        $productoModel = new ProductoModel();

        $data = [
            'titulo'    => 'Nuevo pedido',
            'clientes'  => $clienteModel->getClientesLista(),
            'productos' => $productoModel->getProductosLista(),
        ];

        return $this->render('pedidos/pedido/v_nuevo_pedido', $data);
    }

    /**
     * Reutilizar un pedido: abre el formulario de alta ya relleno.
     * Al guardar se crea un pedido NUEVO; el original no se toca.
     */
    public function copiar_pedido($idPedido)
    {
        $precarga = (new PedidoModel())->getPrecargaCopia($idPedido);

        if (!$precarga) {
            return $this->response->setStatusCode(404)->setBody('Pedido no encontrado.');
        }

        return view('pedidos/pedido/v_alta_pedidos', [
            'clientes'  => (new ClienteModel())->getClientesLista(),
            'productos' => (new ProductoModel())->getProductosLista(),
            'precarga'  => $precarga,
        ]);
    }

    public function guarda_pedido()
    {
        // La ubicación llega como "lat,lng" desde el mapa
        $punto = ubicacion_parsear($this->request->getPost('direccion'));
        if ($punto === null) {
            echo "error_validacion: Selecciona la ubicación de entrega";
            return;
        }

        $rules = [
            'id_cliente'               => 'required|is_natural_no_zero',
            'fecha_entrega'            => 'required|valid_date',
            'total_general'            => 'permit_empty|decimal|greater_than_equal_to[0]',
            'productos.*.id_producto'  => 'required|is_natural_no_zero',
            'productos.*.cantidad'     => 'required|decimal|greater_than[0]',
            'productos.*.precio_venta' => 'required|decimal|greater_than_equal_to[0]',
            'productos.*.total'        => 'permit_empty|decimal|greater_than_equal_to[0]',
            'productos.*.unidad_venta' => 'required|in_list[caja,mazo,arpilla,kilos,ramos]',
        ];

        if (!$this->validate($rules)) {
            echo "error_validacion: " . json_encode($this->validator->getErrors());
            return;
        }

        //La cantidad pedida de cada producto no puede superar la existencia disponible
        $errorExistencias = $this->validarExistencias($this->request->getPost('producto') ?? []);
        if ($errorExistencias !== null) {
            echo $errorExistencias;
            return;
        }

        $totalGeneral = $this->request->getPost('total_general');
        $totalGeneral = (is_numeric($totalGeneral) && $totalGeneral > 0) ? (float) $totalGeneral : null;

        try {
            (new PedidoModel())->crearPedido(
                [
                    'fecha_entrega'    => $this->request->getPost('fecha_entrega'),
                    'id_cliente1'      => $this->request->getPost('id_cliente'),
                    'direccion_pedido' => ubicacion_fomatear($punto['lat'], $punto['lng'])
                ],
                $this->request->getPost('productos') ?? [],
                $totalGeneral
            );

            echo "ok";
        } catch (\Throwable $e) {
            log_message('error', 'guardar_pedido: ' . $e->getMessage());
            echo "ERROR: " . $e->getMessage();
        }
    }

    //compara la cantidad pedida con la cantidad disponible
    private function validarExistencias(array $productos): ?string
    {
        $pedido = [];
        foreach ($productos as $linea) {
            $id = (int) ($linea['id_producto'] ?? 0);
            $pedido[$id] = ($pedido[$id] ?? 0 + (float) ($linea['cantidad'] ?? 0));
        }

        $productoModel = new ProductoModel();
        $disponibles = $productoModel->getDisponiblesPorProducto();
        $nombres = array_column($productoModel->getProductosLista(), 'nombre', 'id_producto');

        $errores = [];
        foreach ($pedido as $id => $cantidad) {
            if (!array_key_exists($id, $disponibles)) {
                $errores[] = "- Producto #$id no existe";
                continue;
            }

            //rounf sirve para evitar errores con deicmales
            if (round($cantidad, 2) > round($disponibles[$id], 2)) {
                $nombre = $nombres[$id] ?? "Producto #$id";
                $errores[] = sprintf(
                    '- %s: pediste %s y solo hay %s disponible',
                    $nombre,
                    rtrim(rtrim(number_format($cantidad, 2, '.', ''), '0'), '.'),
                    rtrim(rtrim(number_format($disponibles[$id], 2, '.', ''), '0'), '.')
                );
            }
        }

        return $errores ? "Existencias insuficientes: \n" . implode("\n", $errores) : null;
    }

    public function editar_pedido($id_pedido){

        $pedidoModel   = new PedidoModel();
        $clienteModel  = new ClienteModel();
        $productoModel = new ProductoModel();

        $detalle = $pedidoModel->getDetallePedido($id_pedido);
 
        if (!$detalle['pedido']) {
            return redirect()->back()->with('error', 'Pedido no encontrado.');
        }
 
        $data = [
            'pedido'         => $detalle['pedido'],
            'detalle_pedido' => $pedidoModel->getProductosParaEditar($id_pedido), // líneas del pedido
            'clientes'       => $clienteModel->getClientesLista(),
            'productos'      => $productoModel->findAll(),
        ];

        return view('pedidos/pedido/v_editar_pedidos', $data);
    }

     public function actualizar()
    {
        $db           = \Config\Database::connect();
        $pedidoModel  = new PedidoModel();
 
        $id_pedido = $this->request->getPost('id_pedido');

        //validación 
        $punto = ubicacion_parsear($this->request->getPost('direccion'));
        if ($punto === null) {
            echo "error_validacion: Selcciona la ubicación de entrega";
            return;
        }
 
        //Solo los campos generales del pedido (sin tocar los productos todavía)
        $datosPedido = [
            'id_cliente1'      => $this->request->getPost('id_cliente'),
            'fecha_entrega'    => $this->request->getPost('fecha_entrega'),
            'direccion_pedido' => ubicacion_fomatear($punto['lat'], $punto['lng']),
        ];
 
        $pedidoModel->update($id_pedido, $datosPedido);
 
        // ── SINCRONIZAR LÍNEAS DE PRODUCTOS ──

        //Si no viene ningún producto en el POST, usa array vacío para
        //evitar errores al iterar
        $productosPost = $this->request->getPost('productos') ?? [];//Lee el array de productos enviado desde el formulario
 
        /*
        Obtiene los id_detalle de las líneas que AÚN están en el formulario
        Estas son las que NO deben eliminarse de la base de datos.

        array_column($productosPost, 'id_detalle') Extrae solo la columna 'id_detalle' del array de productos

        array_filter(..., fn($v) => $v !== '' && $v !== null) Elimina del array los id_detalle vacíos (filas nuevas que aún no existen en bd)

        array_values() Reindexa el array desde 0 después del filter
        */
        $idsRecibidos = array_values(array_filter(
            array_column($productosPost, 'id_detalle'),
            fn($v) => $v !== '' && $v !== null
        ));
 
        // Eliminar líneas que el usuario quitó

        /*
        implode(',', array_fill(0, count($idsRecibidos), '?'))
            Genera los placeholders para el IN() de la query.
    
        array_fill(0, N, '?')
            Crea un array con N elementos iguales a '?'. Ej: ['?','?','?']
            
        implode(',', [...])
            Une el array con comas. Ej: ['?','?','?'] → "?,?,?"
            
        Resultado final en la query: ... NOT IN (?,?,?)
            Esto evita hardcodear los ids y previene SQL injection.
        */
        if (!empty($idsRecibidos)) {
            $placeholders = implode(',', array_fill(0, count($idsRecibidos), '?'));
            $db->query(
                "DELETE FROM producto_pedido 
                 WHERE id_pedido1 = ? 
                 AND id_producto_pedido NOT IN ($placeholders)",
                array_merge([$id_pedido], $idsRecibidos)
            );
        } else {
            // Elimina solo las líneas del pedido que YA NO están en el formulario
            $db->query(
                "DELETE FROM producto_pedido WHERE id_pedido1 = ?",
                [$id_pedido]
            );
        }
 
        // Insertar o actualizar cada línea
        foreach ($productosPost as $linea) {
            $subtotal = round($linea['cantidad'] * $linea['precio_venta'], 2); //redondea a 2
 
            if (!empty($linea['id_detalle'])) {
                // Actualizar línea existente
                $db->query(
                    "UPDATE producto_pedido 
                     SET id_producto3  = ?,
                         cantidad      = ?,
                         unidad_venta  = ?,
                         precio_venta  = ?,
                         total         = ?
                     WHERE id_producto_pedido = ?",
                    [
                        $linea['id_producto'],
                        $linea['cantidad'],
                        $linea['unidad_venta'],
                        $linea['precio_venta'],
                        $subtotal,
                        $linea['id_detalle'],
                    ]
                );
            } else {
                // Nueva línea agregada en la edición
                $db->query(
                    "INSERT INTO producto_pedido 
                        (id_pedido1, id_producto3, cantidad, unidad_venta, precio_venta, total)
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $id_pedido,
                        $linea['id_producto'],
                        $linea['cantidad'],
                        $linea['unidad_venta'],
                        $linea['precio_venta'],
                        $subtotal,
                    ]
                );
            }
        }
 
        return $this->response->setBody('ok');

    }

    public function actualizar_status()
    {
        $idPedido = $this->request->getPost('id_pedido');
        $status   = $this->request->getPost('status');

        $rol = (string) session()->get('rol');

        $esRepartidor = in_array($rol, ['repartidor', 'repartidor_avanzado'], true);

        // Un repartidor avanzado solo puede actualizar estados propios de la entrega.
        // Los estados de pago, cancelación y confirmación administrativa quedan bloqueados.
        if ($esRepartidor) {
            $permitidos = ['en transito', 'entregado'];
        } else {
            $permitidos = ['confirmado', 'en transito', 'entregado', 'pagado', 'cancelado', 'credito'];
        }

        if (!$idPedido || !in_array($status, $permitidos, true)) {
            return $this->response->setStatusCode(403)->setBody('error');
        }

        $db = \Config\Database::connect();

        // Seguridad adicional: el repartidor avanzado solo puede actualizar
        // pedidos que estén asignados a su cuenta y tengan fecha de entrega hoy.
        if ($esRepartidor) {
            $idRepartidor = (new \App\Models\RepartidorModel())
                ->idPorUsuario((int) session('id_usuario'));

            if ($idRepartidor === null) {
                return $this->response->setStatusCode(403)->setBody('error');
            }

            $pedidoAsignado = $db->query("
                SELECT p.id_pedido,
                       (SELECT st.estado
                          FROM status st
                         WHERE st.id_pedido2 = p.id_pedido
                         ORDER BY st.id_status DESC
                         LIMIT 1) AS estado_actual
                FROM pedido p
                WHERE p.id_pedido = ?
                  AND p.id_repartidor = ?
                  AND p.fecha_entrega = CURDATE()
                LIMIT 1
            ", [(int) $idPedido, (int) $idRepartidor])->getRowArray();

            if (!$pedidoAsignado) {
                return $this->response->setStatusCode(403)->setBody('error');
            }

            $estadoActual = $pedidoAsignado['estado_actual'] ?? 'solicitado';
            $transicionValida = ($status === 'en transito' && $estadoActual === 'confirmado')
                || ($status === 'entregado' && $estadoActual === 'en transito');

            if (!$transicionValida) {
                return $this->response->setStatusCode(403)->setBody('error');
            }
        }

        $db->table('status')->insert([
            'estado'     => $status,
            'fecha'      => date('Y-m-d H:i:s'),
            'id_pedido2' => (int) $idPedido
        ]);

        echo $db->affectedRows() > 0 ? "ok" : "error";

        //Retorna cuántas filas fueron modificadas por el último INSERT, UPDATE o DELETE ejecutado, 
    }
}
