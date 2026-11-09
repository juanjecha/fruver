<?php
namespace App\Models;

use CodeIgniter\Model;

class RepartidorModel extends Model
{
    protected $table      = 'repartidor';          // Nombre de la tabla en BD
    protected $primaryKey = 'id_repartidor';       // Llave primaria
    protected $allowedFields = ['nombre','ap_p','ap_m','tel','direccion','notas','id_usuario'];

    // Obtener un producto por su ID
    public function reparRepetido($nombre,$ap){
        return $this->where('nombre', $nombre)->where('ap_p',$ap)->findAll();
    }

    public function getRepart($idrepart)
    {
        return $this->where('id_repartidor', $idrepart)->first();
    }

    //BUSQUEDA
    public function busquedaCompleja($filtros)
        {
            $builder = $this->builder();

            if (!empty($filtros['q'])) {
                $builder->groupStart()
                    ->like('nombre',        $filtros['q'])
                    ->orLike('ap_p',   $filtros['q'])
                    ->orLike('ap_m', $filtros['q'])
                    ->orLike('tel', $filtros['q'])
                ->groupEnd();
            }

            return $this;
        }

        /** id_repartidor de una cuenta (null si esa cuenta no está unida a ningún repartidor). */
        public function idPorUsuario(int $idUsuario): ?int
        {
            $r = $this->where('id_usuario', $idUsuario)->first();
            return $r ? (int) $r['id_repartidor'] : null;
        }

        /** Crea el repartidor y, si viene $cuenta, también su cuenta ($rol: repartidor | repartidor_avanzado). */
        public function crearConCuenta(array $repartidor, ?array $cuenta, string $rol): int
        {
            $this->db->transStart();

            if ($cuenta) {
                $repartidor['id_usuario'] = (new UsuarioModel())->crearCuenta(
                    $cuenta['correo'], $cuenta['password'], $rol,
                    trim($repartidor['nombre'] . ' ' . $repartidor['ap_p'])
                );
            }

            $this->insert($repartidor);
            $id = (int) $this->getInsertID();

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('No se pudo guardar el repartidor');
            }
            return $id;
        }

        /**
         * Actualiza el repartidor. Si viene $cuenta (y aún no tenía) la crea y la une;
         * si ya tenía cuenta, solo actualiza su tipo ($rol).
         */
        public function actualizarConCuenta(int $id, array $datos, ?array $cuenta, string $rol): void
        {
            $actual   = $this->getRepart($id);
            $usuarios = new UsuarioModel();

            $this->db->transStart();

            if ($cuenta) {
                $datos['id_usuario'] = $usuarios->crearCuenta(
                    $cuenta['correo'], $cuenta['password'], $rol,
                    trim($datos['nombre'] . ' ' . $datos['ap_p'])
                );
            } elseif (! empty($actual['id_usuario'])) {
                $usuarios->update($actual['id_usuario'], ['rol' => $rol]);
            }

            $this->update($id, $datos);
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('No se pudo actualizar el repartidor');
            }
        }

        /** Borra el repartidor y también su cuenta (para que no quede un acceso huérfano). */
        public function eliminarConCuenta($id): void
        {
            $repartidor = $this->getRepart($id);

            $this->db->transStart();
            $this->delete($id);
            if (! empty($repartidor['id_usuario'])) {
                $this->db->table('usuario')->where('id_usuario', $repartidor['id_usuario'])->delete();
            }
            $this->db->transComplete();
        }

}
