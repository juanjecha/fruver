<?php
namespace App\Models;

use CodeIgniter\Model;

class EntradaModel extends Model
{
    protected $table      = 'entrada';          // Nombre de la tabla en BD
    protected $primaryKey = 'id_entrada';       // Llave primaria
    protected $allowedFields = ['fecha','fecha_caducidad', 'cantidad', 'u_compra','u_venta','precio_compra','id_producto2','equivalente','conversion','cantidad_vendida']; // Campos permitidos


    // Sobrescribe la query base para incluir el JOIN con producto
    protected function initialize()
    {
        // Hace que findAll() y paginate() traigan el nombre del producto
        $this->builder = $this->db->table('entrada')
            ->select('entrada.*, producto.nombre AS nombre_producto')
            ->join('producto', 'producto.id_producto = entrada.id_producto2', 'left');
    }

    public function getEntrada($identrada)
    {
        return $this->where('id_entrada', $identrada)->first();
    }

    /*public function getEntradasConProducto()
    {
        return $this->db->table('entrada')
            ->select('
                entrada.id_entrada, 
                entrada.fecha, 
                entrada.equivalente,    
                entrada.cantidad, 
                entrada.u_compra,
                entrada.u_venta,
                entrada.conversion,    
                producto.nombre
            ')
            ->join('producto', 'producto.id_producto = entrada.id_producto2')
            ->orderBy('entrada.fecha', 'DESC')
            ->get()
            ->getResultArray();
    }*/
    /*
    public function getEntradasConProducto(): array
    {
        return $this->db->table('entrada e')
            ->select('e.id_entrada, e.fecha, e.cantidad, e.u_compra, e.u_venta, e.equivalente,
                    p.nombre,
                    COALESCE(SUM(m.cantidad), 0) AS merma_acumulada,
                    (e.cantidad - COALESCE(SUM(m.cantidad), 0)) AS cantidad_disponible')
            ->join('producto p', 'p.id_producto = e.id_producto2')
            ->join('merma m', 'm.id_entrada = e.id_entrada', 'left') // left para entradas sin merma
            ->groupBy('e.id_entrada')
            ->having('cantidad_disponible >', 0) // oculta entradas agotadas
            ->get()
            ->getResultArray();
    }*/
    public function getEntradasConProducto(): array
    {
        return $this->db->table('entrada e')
            ->select('
                e.id_entrada,
                e.fecha,
                e.cantidad,
                e.u_compra,
                e.u_venta,
                e.equivalente,
                p.nombre,
                (e.cantidad - COALESCE(SUM(m.cantidad), 0)) AS cantidad_disponible
            ')
            ->join('producto p', 'p.id_producto = e.id_producto2')
            ->join('merma m', 'm.id_entrada = e.id_entrada', 'left')
            ->groupBy('e.id_entrada')
            ->having('cantidad_disponible >', 0)
            ->orderby('e.fecha','DESC')
            ->get()
            ->getResultArray();
    }
  
   //BUSQUEDA
    public function busquedaCompleja($filtros)
        {
            $builder = $this->builder();

            if (!empty($filtros['q'])) {
                $builder->groupStart()
                    //->like('producto',        $filtros['q'])
                    ->Like('fecha',   $filtros['q'])
                    ->orLike('nombre', $filtros['q'])
                ->groupEnd();
            }

            $builder->orderBy('entrada.fecha', 'DESC')
            ->orderBy('entrada.id_entrada', 'DESC');

            return $this;
        }

    //HISTORIAL DE ENTRADA

    public function getEntradasPorProducto(int $id_producto): array
    {
        return $this->db->table('entrada')
            ->where('id_producto2', $id_producto)
            ->orderBy('fecha', 'DESC')
            ->get()
            ->getResultArray();
    }
}