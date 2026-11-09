<?php 
namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model {
    protected $table      = 'pedido';
    protected $primaryKey = 'id_pedido'; // Asegúrate que coincida con tu DB
    protected $allowedFields = ['id_cliente', 'fecha', 'id_cliente1', 'id_repartidor','direccion_pedido','fecha_entrega']; 

    public function getPedidosCompletos()
    {
        return $this->db->query("
            SELECT 
                p.id_pedido,
                p.fecha,
                p.direccion_pedido,
                CONCAT_WS(' ', c.nombre, c.ap_p, c.ap_m) AS cliente,
                c.preferencial                             AS cliente_preferencial,
                CONCAT(r.nombre, ' ', r.ap_p)               AS repartidor,
                s.estado                                     AS status
            FROM pedido p
            JOIN cliente c         ON c.id_cliente    = p.id_cliente1
            LEFT JOIN repartidor r ON r.id_repartidor = p.id_repartidor
            LEFT JOIN status s     ON s.id_status = (
                SELECT MAX(id_status)
                FROM status
                WHERE id_pedido2 = p.id_pedido
            )
            ORDER BY p.id_pedido DESC
        ")->getResultArray();
    }

    public function getProductosDePedido($idPedido)
    {
        return $this->db->query("
            SELECT 
                pr.nombre,
                pp.cantidad,
                pp.unidad_venta,
                pp.precio_venta,
                pp.total
            FROM producto_pedido pp
            JOIN producto pr ON pr.id_producto = pp.id_producto3
            WHERE pp.id_pedido1 = ?
        ", [$idPedido])->getResultArray();
    }

    /**
 * Datos para REUTILIZAR un pedido: cliente, dirección y productos del original,
 * con fecha de entrega de HOY. No copia repartidor, estado ni pago.
 * Devuelve null si el pedido no existe.
 */
public function getPrecargaCopia($idPedido)
{
    $origen = $this->db->query("
        SELECT id_pedido, id_cliente1 AS id_cliente, direccion_pedido AS direccion
        FROM pedido
        WHERE id_pedido = ?
    ", [$idPedido])->getRowArray();

    if (!$origen) {
        return null;
    }

    $lineas = $this->getProductosParaEditar($idPedido);

    $productos = array_map(static fn($l) => [
        'id_producto'  => (int) $l['id_producto'],
        'cantidad'     => (float) $l['cantidad'],
        'unidad_venta' => $l['unidad_venta'],
        'precio_venta' => (float) $l['precio_venta'],
        'total'        => (float) $l['total'],
    ], $lineas);

    return [
        'id_origen'     => (int) $origen['id_pedido'],
        'id_cliente'    => (int) $origen['id_cliente'],
        'direccion'     => $origen['direccion'] ?? '',
        'fecha_entrega' => date('Y-m-d'),
        'productos'     => $productos,
        'total_general' => round(array_sum(array_column($productos, 'total')), 2),
    ];
}

/**
 * Crea un pedido completo (cabecera + estado + productos) en UNA transacción.
 * Total de cada producto: el que mande el usuario o, si no hay, cantidad × precio.
 * Si $totalGeneral no cuadra con la suma, el último producto absorbe la diferencia.
 */
    public function crearPedido(array $cabecera, array $productos, ?float $totalGeneral = null): int
    {
        $productos = array_values($productos); // el formulario puede traer huecos si se borraron filas

        if (empty($productos)) {
            throw new \RuntimeException('No hay productos');
        }

        $lineas = [];
        foreach ($productos as $prod) {
            $total = (isset($prod['total']) && is_numeric($prod['total']) && $prod['total'] >= 0)
                ? (float) $prod['total']
                : (float) $prod['cantidad'] * (float) $prod['precio_venta'];

            $lineas[] = [
                'cantidad'     => $prod['cantidad'],
                'precio_venta' => $prod['precio_venta'],
                'unidad_venta' => $prod['unidad_venta'],
                'id_producto3' => $prod['id_producto'],
                'total'        => round($total, 2),
            ];
        }

        if ($totalGeneral !== null) {
            $ultimo       = count($lineas) - 1;
            $sumaAnterior = array_sum(array_column(array_slice($lineas, 0, $ultimo), 'total'));
            $resto        = round($totalGeneral - $sumaAnterior, 2);

            if ($resto < 0) {
                throw new \RuntimeException('El total final no puede ser menor que la suma de los demás productos.');
            }
            $lineas[$ultimo]['total'] = $resto;
        }

        $this->db->transBegin();

        try {
            $this->db->table('pedido')->insert($cabecera);
            $idPedido = $this->db->insertID();

            if (!$idPedido) {
                throw new \RuntimeException('No se pudo insertar el pedido');
            }

            $this->db->table('status')->insert([
                'estado'     => 'solicitado',
                'fecha'      => date('Y-m-d H:i:s'),
                'id_pedido2' => $idPedido,
            ]);

            foreach ($lineas as $linea) {
                $linea['id_pedido1'] = $idPedido;
                $this->db->table('producto_pedido')->insert($linea);
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Error en transacción');
            }

            $this->db->transCommit();
            return (int) $idPedido;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function getRepartidores()
    {
        return $this->db->query("
            SELECT id_repartidor, CONCAT(nombre, ' ', ap_p) AS nombre_completo
            FROM repartidor
            ORDER BY nombre ASC
        ")->getResultArray();
    }



    //SEGUIMIENTO

    public function getResumenEstatus()
    {
        // Este siempre trae todos, sin filtros, para las tarjetas
        $pedidos = $this->getPedidosCompletos();

        $total       = count($pedidos);

        $solicitados = 0;
        $enProceso   = 0;
        $completados = 0;

        // Recorre cada pedido uno por uno
        foreach ($pedidos as $p) {
            $status = $p['status'] ?? 'solicitado'; // Si no tiene estado, por defecto lo toma como "solicitado"
            // Si el estado es "solicitado" o es null → suma a solicitados
            if (in_array($status, ['solicitado', null]))         
                $solicitados++;
            // Si el estado es "confirmado" o "en transito" → suma a en proceso
            elseif (in_array($status, ['confirmado','en transito'])) 
                $enProceso++;
            // Si el estado es "entregado" o "pagado" → suma a completados
            elseif (in_array($status, ['entregado', 'pagado']))  
                $completados++;
        }

        // Regresa un arreglo con los resultados
        return [
            'total'       => $total,
            'solicitados' => $solicitados,
            'enProceso'   => $enProceso,
            'completados' => $completados,
        ];
    }

    //filtrar por texto de búsqueda y por estado
    public function getPedidosParaSeguimiento($buscar = null, $status = null)
    {

        //datos completos del pedido
        $sql = "
        SELECT 
            p.id_pedido,
            p.fecha,
            p.fecha_entrega,
            p.direccion_pedido,
            p.pago,
            CONCAT_WS(' ', c.nombre, c.ap_p, c.ap_m) AS cliente,
            c.preferencial                             AS cliente_preferencial,
            CONCAT(r.nombre, ' ', r.ap_p)               AS repartidor,
            s.estado                                     AS status
        FROM pedido p
        JOIN cliente c         ON c.id_cliente    = p.id_cliente1
        LEFT JOIN repartidor r ON r.id_repartidor = p.id_repartidor
        LEFT JOIN status s ON s.id_status = (
            SELECT MAX(id_status)
            FROM status
            WHERE id_pedido2 = p.id_pedido
        )
        WHERE 1=1
        ";

        // Arreglo donde se guardan los valores de los filtros (para evitar SQL injection)
        $params = [];

        // Filtro búsqueda
        if (!empty($buscar)) { // Si el usuario escribió algo en buscar

            // Agrega condiciones para buscar en varios campos
            $sql .= " AND (
                CAST(p.id_pedido AS CHAR) LIKE ?
                OR CONCAT_WS(' ', c.nombre, c.ap_p, c.ap_m) LIKE ?
                OR CAST(p.fecha AS CHAR) LIKE ?
            )";

            //LIKE "?" placeholder (espacio reservado) que luego se reemplaza con un valor

            // Se agregan los valores con % para buscar coincidencias parciales, llena -> ?
            $params[] = "%$buscar%";
            $params[] = "%$buscar%";
            $params[] = "%$buscar%"; //%%-> cualquier cosa antes o despues
        }

        // Filtro estatus
        if (!empty($status)) { // Si se seleccionó un estado específico
            $sql .= " AND s.estado = ?"; // Filtra solo los pedidos con ese estado
            $params[] = $status; // Agrega el valor del estado al arreglo de parámetros
        }

        $sql .= " ORDER BY p.id_pedido DESC";

        // Ejecuta la consulta en la base de datos con los parámetros
        // y devuelve el resultado como arreglo
        return $this->db->query($sql, $params)->getResultArray();

    }

    public function getDetallePedido($idPedido)
    {
        $pedido = $this->db->query("
            SELECT 
                p.id_pedido,
                p.id_cliente1,
                p.fecha,
                p.fecha_entrega,
                p.pago,
                p.direccion_pedido,
                CONCAT_WS(' ', c.nombre, c.ap_p, c.ap_m) AS cliente,
                c.preferencial                             AS cliente_preferencial,
                c.tel                                        AS telefono,
                CONCAT(r.nombre, ' ', r.ap_p)               AS repartidor,
                s.estado                                     AS status,
                s.fecha                                      AS fecha_status,
                (SELECT SUM(pp.total)
                FROM producto_pedido pp
                WHERE pp.id_pedido1 = p.id_pedido)          AS total_general
            FROM pedido p
            JOIN cliente c         ON c.id_cliente    = p.id_cliente1
            LEFT JOIN repartidor r ON r.id_repartidor = p.id_repartidor
            LEFT JOIN status s ON s.id_status = (
                SELECT MAX(id_status)
                FROM status
                WHERE id_pedido2 = p.id_pedido
            )
            WHERE p.id_pedido = ?
        ", [$idPedido])->getRowArray();

        $productos = $this->getProductosDePedido($idPedido);

        return [
            'pedido'    => $pedido,
            'productos' => $productos,
        ];
    }

    public function getProductosParaEditar($idPedido) //Para productos que se seleccionaron en el pedido
    {
        return $this->db->query("
            SELECT 
                pp.id_producto_pedido,
                pp.id_producto3      AS id_producto,
                pp.cantidad,
                pp.unidad_venta,
                pp.precio_venta,
                pp.total,
                pr.nombre
            FROM producto_pedido pp
            JOIN producto pr ON pr.id_producto = pp.id_producto3
            WHERE pp.id_pedido1 = ?
        ", [$idPedido])->getResultArray();
    }

    public function getHistorialStatus($idPedido)
    {
        return $this->db->query("
            SELECT estado, fecha
            FROM status
            WHERE id_pedido2 = ?
            ORDER BY id_status ASC
        ", [$idPedido])->getResultArray();
    }

    ///FUNCIONES PARA -------------PORTAL DEL CLIENTE-----------

    /**
     * Todos los pedidos de UN cliente (el alcance lo pone el WHERE, no la pantalla), con su
     * estado actual, total, productos y banderas para clasificarlos:
     *   entregado    -> alguna vez llegó a "entregado"
     *   tuvo_credito -> alguna vez pasó por "credito"
     *   fecha_pagado -> fecha del último estado "pagado" (null si no ha pagado)
     */
    public function getPedidosDeCliente(int $idCliente): array
    {
        $pedidos = $this->db->query("
            SELECT
                p.id_pedido,
                p.fecha,
                p.fecha_entrega,
                p.pago,
                s.estado AS status,
                (SELECT COALESCE(SUM(pp.total), 0)
                   FROM producto_pedido pp
                  WHERE pp.id_pedido1 = p.id_pedido) AS total,
                EXISTS (SELECT 1 FROM status e
                         WHERE e.id_pedido2 = p.id_pedido AND e.estado = 'entregado') AS entregado,
                EXISTS (SELECT 1 FROM status c
                         WHERE c.id_pedido2 = p.id_pedido AND c.estado = 'credito')   AS tuvo_credito,
                (SELECT MAX(sp.fecha) FROM status sp
                  WHERE sp.id_pedido2 = p.id_pedido AND sp.estado = 'pagado')         AS fecha_pagado
            FROM pedido p
            LEFT JOIN status s ON s.id_status = (
                SELECT MAX(id_status) FROM status WHERE id_pedido2 = p.id_pedido
            )
            WHERE p.id_cliente1 = ?
            ORDER BY p.id_pedido DESC
        ", [$idCliente])->getResultArray();

        if (! $pedidos) {
            return [];
        }

        // Productos de todos los pedidos en UNA consulta (no una por pedido)
        $ids   = array_column($pedidos, 'id_pedido');
        $lineas = $this->db->query("
            SELECT pp.id_pedido1, pr.nombre, pp.cantidad, pp.unidad_venta, pp.total
            FROM producto_pedido pp
            JOIN producto pr ON pr.id_producto = pp.id_producto3
            WHERE pp.id_pedido1 IN ?
            ORDER BY pp.id_producto_pedido ASC
        ", [$ids])->getResultArray();

        $porPedido = [];
        foreach ($lineas as $l) {
            $porPedido[$l['id_pedido1']][] = $l;
        }

        foreach ($pedidos as &$p) {
            $p['entregado']    = (int) $p['entregado'] === 1;
            $p['tuvo_credito'] = (int) $p['tuvo_credito'] === 1;
            $p['total']        = (float) $p['total'];
            $p['productos']    = $porPedido[$p['id_pedido']] ?? [];
        }
        unset($p);

        return $pedidos;
    }

    /** ¿El pedido es de este cliente? (para el detalle: un cliente solo ve lo suyo) */
    public function pedidoEsDeCliente(int $idPedido, int $idCliente): bool
    {
        return $this->db->table('pedido')
            ->where('id_pedido', $idPedido)
            ->where('id_cliente1', $idCliente)
            ->countAllResults() > 0;
    }

    ///FUNCIONES PARA -------------GESTION DE ENTREGAS-----------

    public function getPedidosEntregaHoy($idRepartidor = null, $status = null)
    {
        $sql = "
        SELECT 
            p.id_pedido,
            p.fecha,
            p.fecha_entrega,
            p.direccion_pedido,
            p.pago,
            CONCAT_WS(' ', c.nombre, c.ap_p, c.ap_m) AS cliente,
            c.preferencial                             AS cliente_preferencial,
            c.tel                                        AS telefono,
            CONCAT(r.nombre, ' ', r.ap_p)               AS repartidor,
            r.id_repartidor,
            s.estado                                     AS status
        FROM pedido p
        JOIN cliente c         ON c.id_cliente    = p.id_cliente1
        LEFT JOIN repartidor r ON r.id_repartidor = p.id_repartidor
        LEFT JOIN status s ON s.id_status = (
            SELECT MAX(id_status)
            FROM status
            WHERE id_pedido2 = p.id_pedido
        )
        WHERE DATE(p.fecha_entrega) = CURDATE()
        AND (s.estado IS NULL OR s.estado NOT IN ('pagado', 'cancelado'))
        ";

        $params = [];

        if (!empty($status)) {
            if ($status === 'solicitado') {
                $sql .= " AND s.estado = 'solicitado'";
            } else {
                $sql .= " AND s.estado = ?";
                $params[] = $status;
            }
        }

        if (!empty($idRepartidor)) {
            $sql .= " AND p.id_repartidor = ?";
            $params[] = $idRepartidor;
        }

        $sql .= " ORDER BY p.id_pedido ASC";

        return $this->db->query($sql, $params)->getResultArray();
    }//filtrara solo pedidos del mismo dia, excluye pagados y cancelados

    /**
     * Historial de pedidos que ya llegaron al estado entregado.
     * Para repartidores limita la consulta a su propio id; otros roles autorizados
     * pueden consultar el historial completo.
     */
    public function getHistorialPedidosEntregados(?int $idRepartidor = null): array
    {
        $sql = "
            SELECT
                p.id_pedido,
                p.fecha,
                p.fecha_entrega,
                p.direccion_pedido,
                p.pago,
                CONCAT_WS(' ', c.nombre, c.ap_p, c.ap_m) AS cliente,
                CONCAT_WS(' ', r.nombre, r.ap_p, r.ap_m) AS repartidor,
                (SELECT st.estado
                   FROM status st
                  WHERE st.id_pedido2 = p.id_pedido
                  ORDER BY st.id_status DESC
                  LIMIT 1) AS status,
                (SELECT st.fecha
                   FROM status st
                  WHERE st.id_pedido2 = p.id_pedido
                    AND st.estado = 'entregado'
                  ORDER BY st.id_status DESC
                  LIMIT 1) AS fecha_entregado
            FROM pedido p
            INNER JOIN cliente c ON c.id_cliente = p.id_cliente1
            LEFT JOIN repartidor r ON r.id_repartidor = p.id_repartidor
            WHERE EXISTS (
                SELECT 1 FROM status entregas
                WHERE entregas.id_pedido2 = p.id_pedido
                  AND entregas.estado = 'entregado'
            )
        ";

        $params = [];
        if ($idRepartidor !== null) {
            $sql .= ' AND p.id_repartidor = ?';
            $params[] = $idRepartidor;
        }

        $sql .= ' ORDER BY fecha_entregado DESC, p.id_pedido DESC';
        return $this->db->query($sql, $params)->getResultArray();
    }

}