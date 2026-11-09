<?php
namespace App\Models;

use CodeIgniter\Model;

class MermaModel extends Model
{
    protected $table      = 'merma';          // Nombre de la tabla en BD
    protected $primaryKey = 'id_merma';       // Llave primaria
    protected $allowedFields = ['id_merma', 'cantidad','fecha','notas','id_entrada']; // Campos permitidos

    // Obtener un producto por su ID
    public function getMerma($idmerma)
    {
        return $this->where('id_merma', $idmerma)->first();
    }

    // Buscar productos por nombre (para evitar duplicados)
        //return $this->where('id_merma', $idmerma)->findAll();



    // Eliminar producto por ID
    public function eliminarMerma($id)
    {
        return $this->delete($id);
    }

    public function getMermaAcumulada(int $id_entrada): float
    {
        $row = $this->db->table('merma')
            ->selectSum('cantidad', 'total')
            ->where('id_entrada', $id_entrada)
            ->get()
            ->getRowArray();

        return (float) ($row['total'] ?? 0);
    }

  


    //BUSQUEDA
    public function busquedaCompleja($filtros)
    {
        /*if (!empty($filtros['q'])) {
            $this->groupStart()
                ->like('cantidad',   $filtros['q'])
                ->orLike('fecha',    $filtros['q'])
                ->orLike('notas',    $filtros['q'])
                ->orLike('id_entrada', $filtros['q'])
            ->groupEnd();
        }

        return $this;*/

            // Trae todas las columnas necesarias con los JOINs
        $this->select('merma.id_merma, merma.cantidad, merma.fecha, merma.notas,
            entrada.id_entrada, entrada.fecha as fecha_entrada, entrada.u_compra, entrada.u_venta,
            entrada.equivalente,
            (merma.cantidad * entrada.equivalente) AS cantidad_venta,
                    producto.nombre')
            ->join('entrada',  'entrada.id_entrada = merma.id_entrada')
            ->join('producto', 'producto.id_producto = entrada.id_producto2')
            ->orderBy('merma.id_merma', 'DESC');

        // Si hay término de búsqueda, filtra en TODAS las columnas relevantes
        if (!empty($filtros['q'])) {
            $this->groupStart()
                    ->like('producto.nombre',      $filtros['q'])  // busca por nombre del producto
                    //->orLike('entrada.u_compra',    $filtros['q'])  // busca por unidad
                    //->orLike('entrada.fecha',      $filtros['q'])  // busca por fecha entrada
                    //->orLike('merma.cantidad',     $filtros['q'])  // busca por cantidad
                    ->orLike('merma.fecha',        $filtros['q'])  // busca por fecha merma
                    ->orLike('merma.notas',        $filtros['q'])  // busca por notas
                    
                ->groupEnd();
        }
        
        $this->orderBy('merma.fecha', 'DESC');
        return $this;
    }

    // traer mermas con los datos de su entrada y el producto

    /*public function getMermasConEntrada($filtro = '')
    {
        $this->select('merma.id_merma, merma.cantidad, merma.fecha, merma.notas,
                    entrada.id_entrada, entrada.fecha as fecha_entrada, entrada.u_venta,
                    producto.nombre')
            ->join('entrada', 'entrada.id_entrada = merma.id_entrada')
            ->join('producto', 'producto.id_producto = entrada.id_producto2')
            ->orderBy('merma.id_merma', 'DESC');

        if (!empty($filtro)) {
        $builder->like('producto.nombre', $filtro);
        }

        return $this;
    }*/

    //HISTORIAL
    /*public function getMermasPorEntrada(int $id_entrada): array
    {
        return $this->db->table('merma')
            ->where('id_entrada', $id_entrada)
            ->orderBy('fecha', 'DESC')
            ->get()
            ->getResultArray();
    }*/
    public function getMermasPorEntrada(int $id_entrada): array
    {
        return $this->db->table('merma m')
            ->select('m.fecha, m.cantidad, m.notas,
                    e.u_compra, e.u_venta, e.equivalente,
                    (m.cantidad * e.equivalente) AS cantidad_venta')
            ->join('entrada e', 'e.id_entrada = m.id_entrada')
            ->where('m.id_entrada', $id_entrada)
            ->orderBy('m.fecha', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getMermaConEquivalente(int $id_merma): array
    {
        $row = $this->db->table('merma m')
            ->select('m.*, e.equivalente, e.u_venta')
            ->join('entrada e', 'e.id_entrada = m.id_entrada')
            ->where('m.id_merma', $id_merma)
            ->get()
            ->getRowArray();

        return $row ?? [];
    }
    //funcion para tarer el equivalente y muestre en u_venta 

  
   
}
