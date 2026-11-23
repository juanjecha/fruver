<?php
namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table      = 'producto';          // Nombre de la tabla en BD
    protected $primaryKey = 'id_producto';       // Llave primaria
    protected $allowedFields = ['codigo', 'nombre', 'categoria', 'descripcion', 'imagen', 'u_venta']; // Campos permitidos

    // Prefijo del código según la categoría: FRU-001, VER-001, HIE-001
    private const PREFIJOS = [
        'fruta'   => 'FRU',
        'verdura' => 'VER',
        'hierba'  => 'HIE',
    ];

    // Obtener un producto por su ID
    public function getProducto($idproducto)
    {
        return $this->where('id_producto', $idproducto)->first();
    }

    // Buscar productos por nombre (para evitar duplicados)
    public function buscaNombre($nombre)
    {
        return $this->where('nombre', $nombre)->findAll();
    }

    // Eliminar producto por ID
    public function eliminarProducto($id)
    {
        return $this->delete($id);
    }

    // CÓDIGO ÚNICO: toma el número más alto de esa categoría y le suma 1
    public function generarCodigo(string $categoria): string
    {
        $prefijo = self::PREFIJOS[$categoria] ?? 'PRO';

        $fila = $this->db->table('producto')
            ->select('MAX(CAST(SUBSTRING(codigo, 5) AS UNSIGNED)) AS ultimo', false)
            ->like('codigo', $prefijo . '-', 'after')
            ->get()
            ->getRowArray();

        $siguiente = (int) ($fila['ultimo'] ?? 0) + 1;

        return $prefijo . '-' . str_pad((string) $siguiente, 3, '0', STR_PAD_LEFT);
    }

        //BUSQUEDA
        public function busquedaCompleja($filtros)
    {
        $builder = $this->builder();

        if (!empty($filtros['q'])) {
            $builder->groupStart()
                ->like('codigo',        $filtros['q'])
                ->orLike('nombre',      $filtros['q'])
                ->orLike('categoria',   $filtros['q'])
                ->orLike('descripcion', $filtros['q'])
            ->groupEnd();
        }

        return $this;
    }



    public function tieneEntradas($idproducto)
    {
        $productos = $this->db->table('producto')
        ->select('producto*, COUNT(entrada.id_entrada) as total_entradas')
        ->join('entrada', 'entrada.id_producto2 = producto.id_producto', 'left')
        ->groupBy('producto.id_producto')
        ->get()
        ->getResultArray();
    }

    //FUNCIONES PARA PEDIDO
    /**
     * Productos para el formulario de pedido, con su existencia disponible para venta.
     * "disponible" = e_venta de la fila de existencias MÁS RECIENTE del producto
     * (es el mismo dato que se ve en la pantalla de Existencias).
     * Sin registro de existencias, o con valor negativo, se toma como 0.
     */
    public function getProductosLista()
    {
        $filas = $this->db->query("
            SELECT p.id_producto,
                   p.nombre,
                   p.u_venta,
                   COALESCE((
                       SELECT e.e_venta
                       FROM existencias e
                       WHERE e.id_producto = p.id_producto
                       ORDER BY e.fecha DESC, e.id_existencias DESC
                       LIMIT 1
                   ), 0) AS disponible
            FROM producto p
            ORDER BY p.id_producto ASC
        ")->getResultArray();

        foreach ($filas as &$f) {
            $f['disponible'] = max(0, (float) $f['disponible']);
        }
        unset($f);

        return $filas;
    }

    /** Existencia disponible de cada producto: [id_producto => disponible] */
    public function getDisponiblesPorProducto(): array
    {
        $mapa = [];
        foreach ($this->getProductosLista() as $f) {
            $mapa[(int) $f['id_producto']] = (float) $f['disponible'];
        }
        return $mapa;
    }

}