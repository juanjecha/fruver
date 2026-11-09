<?php

namespace App\Models;

use CodeIgniter\Model;

class ExistenciasGraficaModel extends Model
{
    protected $table      = 'existencias';
    protected $primaryKey = 'id_existencias';
    protected $allowedFields = ['e_total_dia', 'e_bloqueadas', 'e_venta', 'fecha', 'id_producto'];

    public function getResumenPorFecha(): array
    {
        $row = $this->db->table('existencias')
            ->select('
                SUM(e_venta)      AS total_venta,
                SUM(e_bloqueadas) AS total_bloqueadas
            ')
            ->get()
            ->getRowArray();

        return $row ? $row : ['total_venta' => 0, 'total_bloqueadas' => 0];
    }

    public function getMermaPorFecha(): array
    {
        $row = $this->db->table('merma m')
            ->select('SUM(m.cantidad * e.equivalente) AS total_merma')
            ->join('entrada e', 'e.id_entrada = m.id_entrada')
            ->get()
            ->getRowArray();

        return $row ? $row : ['total_merma' => 0];
    }

    public function getExistenciasPorProducto(): array
    {
        return $this->db->table('existencias e')
            ->select('
                p.id_producto,
                p.nombre,
                e.e_totalgen   AS total,
                e.e_bloqueadas AS bloqueadas,
                e.e_venta      AS para_venta
            ')
            ->join('producto p', 'p.id_producto = e.id_producto')
            ->where('e.fecha = (
                SELECT MAX(e2.fecha) 
                FROM existencias e2 
                WHERE e2.id_producto = e.id_producto
            )')
            ->orderBy('p.nombre', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getMermasPorProducto(): array
    {
        return $this->db->table('merma m')
            ->select('
                p.nombre                        AS label,
                SUM(m.cantidad * e.equivalente) AS total_merma
            ')
            ->join('entrada e', 'e.id_entrada = m.id_entrada')
            ->join('producto p', 'p.id_producto = e.id_producto2')
            ->groupBy('p.id_producto')
            ->orderBy('p.nombre', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getResumen(): array
    {
        $productos = $this->getExistenciasPorProducto();

        $total      = count($productos);
        $sin_stock  = 0;
        $stock_bajo = 0;

        foreach ($productos as $p) {
            if ($p['para_venta'] <= 0) {
                $sin_stock++;
            } elseif ($p['para_venta'] < 5) {
                $stock_bajo++;
            }
        }

        return [
            'total_productos' => $total,
            'sin_stock'       => $sin_stock,
            'stock_bajo'      => $stock_bajo,
            'productos'       => $productos,
        ];
    }
}