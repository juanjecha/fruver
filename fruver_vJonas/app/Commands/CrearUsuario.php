<?php

namespace App\Commands;

use App\Models\UsuarioModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Crea un usuario de acceso con la contraseña ya hasheada.
 *   php spark usuario:crear admin@fruver.com administrador "Abril"
 */
class CrearUsuario extends BaseCommand
{
    protected $group       = 'Fruver';
    protected $name        = 'usuario:crear';
    protected $description = 'Crea un usuario de acceso (la contraseña se guarda con hash).';
    protected $usage       = 'usuario:crear [correo] [rol] [nombre]';
    protected $arguments   = [
        'correo' => 'Correo del usuario',
        'rol'    => 'administrador | vendedor_avanzado | repartidor_avanzado | repartidor | cliente',
        'nombre' => 'Nombre para mostrar',
    ];

    public function run(array $params)
    {
        $roles = ['administrador', 'vendedor_avanzado', 'repartidor_avanzado', 'repartidor', 'cliente'];

        $correo = strtolower(trim($params[0] ?? CLI::prompt('Correo', null, 'required|valid_email')));
        $rol    = $params[1] ?? CLI::prompt('Rol', $roles, 'required');
        $nombre = $params[2] ?? CLI::prompt('Nombre', null, 'required');

        if (! in_array($rol, $roles, true)) {
            CLI::error('Rol no válido. Usa: ' . implode(', ', $roles));
            return;
        }

        $password = CLI::prompt('Contraseña (mínimo 10 caracteres)', null, 'required|min_length[10]');

        $modelo = new UsuarioModel();
        if ($modelo->where('correo', $correo)->first()) {
            CLI::error('Ya existe un usuario con ese correo.');
            return;
        }

        $modelo->insert([
            'correo'   => $correo,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'rol'      => $rol,
            'nombre'   => $nombre,
        ]);

        CLI::write("Usuario {$correo} creado con rol {$rol}.", 'green');
    }
}
