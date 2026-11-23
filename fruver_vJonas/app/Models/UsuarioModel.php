<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table         = 'usuario';
    protected $primaryKey    = 'id_usuario';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'correo', 'password', 'password_visible', 'rol', 'nombre', 'tel', 'activo',
        'intentos_fallidos', 'bloqueado_hasta', 'ultimo_acceso',
    ];

    /** Reglas de una cuenta nueva. Devuelve un código de error o null si todo está bien. */
    public function validarCuenta(string $correo, string $password): ?string
    {
        $correo   = strtolower(trim($correo));
        $password = trim($password);

        if ($correo === '' || $password === '') {
            return 'cuenta_incompleta';          // correo y contraseña van juntos
        }
        if (! filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 150) {
            return 'correo_invalido';
        }
        if (mb_strlen($password) < 10 || mb_strlen($password) > 72) {
            return 'password_invalida';
        }
        if ($this->where('correo', $correo)->first()) {
            return 'correo_repetido';
        }
        return null;
    }

    /** Crea la cuenta (hash para el login + copia cifrada para el admin). Devuelve su id_usuario. */
    public function crearCuenta(string $correo, string $password, string $rol, string $nombre, ?string $tel = null): int
    {
        $datos = [
            'correo'           => strtolower(trim($correo)),
            'password'         => password_hash($password, PASSWORD_DEFAULT),
            'password_visible' => $this->cifrar($password),
            'rol'              => $rol,
            'nombre'           => $nombre,
            'activo'           => 1,
        ];

        // El teléfono solo se guarda si se manda (clientes y repartidores no lo usan aquí)
        if ($tel !== null) {
            $datos['tel'] = $tel;
        }

        $this->insert($datos);

        return (int) $this->getInsertID();
    }

    /** Prepara el listado de las cuentas de un rol (ordenadas por nombre). Se usa con ->paginate(). */
    public function cuentasPorRol(string $rol): self
    {
        return $this->where('rol', $rol)->orderBy('nombre', 'ASC');
    }

    /** Datos de la cuenta para mostrarlos. La contraseña solo viene si $conPassword es true. */
    public function getCuenta(int $idUsuario, bool $conPassword = false): ?array
    {
        $u = $this->find($idUsuario);
        if (! $u) {
            return null;
        }

        return [
            'correo'   => $u['correo'],
            'rol'      => $u['rol'],
            'activo'   => (int) $u['activo'],
            'password' => $conPassword ? $this->descifrar($u['password_visible'] ?? null) : null,
        ];
    }

    // ───────────── internos ─────────────
    private function cifrar(string $texto): string
    {
        return base64_encode(service('encrypter')->encrypt($texto));
    }

    private function descifrar(?string $texto): ?string
    {
        if (! $texto) {
            return null;   // cuenta creada por consola o antes de este cambio
        }
        try {
            return service('encrypter')->decrypt(base64_decode($texto));
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Agrega 'password' (descifrada) a cada fila de un listado. null si no se puede descifrar. */
    public function conPassword(array $filas): array
    {
        foreach ($filas as &$f) {
            $f['password_texto'] = $this->descifrar($f['password_visible'] ?? null);
        }
        unset($f);

        return $filas;
    }
}