<?php
namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model{
    protected $table = 'cliente'; //creación de la tabla 
    protected $primaryKey = 'id_cliente'; //definir llave primaria
    protected $allowedFields = ['nombre','ap_p','ap_m','tel','preferencial','id_usuario'];
    

    public function ClienteRepetido($nombre,$ap){
        return $this->where('nombre', $nombre)->where('ap_p',$ap)->findAll();
    }

    public function getCliente($idcliente)
    {
        return $this->where('id_cliente', $idcliente)->first();
    }

    /** Cliente de una cuenta (null si esa cuenta no está unida a ningún cliente). */
    public function getPorUsuario(int $idUsuario): ?array
    {
        return $this->where('id_usuario', $idUsuario)->first();
    }


    public function busquedaCompleja($filtros) {
    if (!empty($filtros['q'])) {
        $this->groupStart()
             ->like('nombre', $filtros['q'])
             ->orLike('ap_p', $filtros['q'])
             ->orLike('ap_m', $filtros['q'])
             ->orLike('tel', $filtros['q'])
             ->groupEnd();
    }

    return $this; 
    }

    public function getClientesLista() //FUNCION PARA REGISTRO DE PEDIDOS
    {
        return $this->select('id_cliente, preferencial, CONCAT_WS(" ", nombre, ap_p, ap_m) AS nombre_completo')
        ->findAll();
    }

    public function contarDirecciones($idCliente)
    {
        return $this->db->table('cliente')
            ->select('COUNT(cliente.id_cliente) as total')
            ->join('direccion', 'cliente.id_cliente = direccion.id_cliente')
            ->where('cliente.id_cliente', $idCliente)
            ->get()
            ->getRow()
            ->total;
    }

    public function eliminarCliente($idcliente)
    {
        $cliente = $this->getCliente($idcliente);

        $this->db->transStart();

        $this->db->table('direccion')->where('id_cliente', $idcliente)->delete();
        $this->db->table('cliente')->where('id_cliente', $idcliente)->delete();

        if (! empty($cliente['id_usuario'])) {
            $this->db->table('usuario')->where('id_usuario', $cliente['id_usuario'])->delete();
        }

        $this->db->transComplete();
    }

    /** Crea el cliente y, si viene $cuenta, también su cuenta de acceso. */
    public function crearConCuenta(array $cliente, ?array $cuenta = null): int
    {
        $this->db->transStart();

        if ($cuenta) {
            $cliente['id_usuario'] = (new UsuarioModel())->crearCuenta(
                $cuenta['correo'], $cuenta['password'], 'cliente',
                trim($cliente['nombre'] . ' ' . $cliente['ap_p'])
            );
        }

        $this->insert($cliente);
        $id = (int) $this->getInsertID();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('No se pudo guardar el cliente');
        }
        return $id;
    }

    /** Actualiza el cliente; si viene $cuenta (y aún no tenía), la crea y la une. */
    public function actualizarConCuenta(int $id, array $datos, ?array $cuenta = null): void
    {
        $this->db->transStart();

        if ($cuenta) {
            $datos['id_usuario'] = (new UsuarioModel())->crearCuenta(
                $cuenta['correo'], $cuenta['password'], 'cliente',
                trim($datos['nombre'] . ' ' . $datos['ap_p'])
            );
        }

        $this->update($id, $datos);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('No se pudo actualizar el cliente');
        }
    }
        
}