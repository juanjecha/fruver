<?php

namespace App\Controllers\auth;
 use App\Controllers\BaseController;
use App\Models\UsuarioModel;

class AuthController extends BaseController
{
    // Mensajes permitidos en ?aviso=... (lista cerrada: nunca se imprime lo que venga en la URL)
    private const AVISOS = [
        'salio'      => ['success', 'Cerraste sesión correctamente.'],
        'expirada'   => ['warning', 'Tu sesión expiró. Inicia sesión de nuevo.'],
        'requiere'   => ['info', 'Inicia sesión para continuar.'],
        'registrado' => ['success', 'Cuenta creada. Ya puedes iniciar sesión.'],
    ];

    // ───────────── LOGIN ─────────────
    public function login()
    {
        $clave = (string) $this->request->getGet('aviso');

        return view('auth/v_login', [
            'aviso' => self::AVISOS[$clave] ?? null,
        ]);
    }

    public function autenticar()
    {
        $cfg = config('Auth');

        $valido = $this->validate([
            'correo'   => 'required|valid_email|max_length[150]',
            'password' => 'required|max_length[72]',
        ]);
        if (! $valido) {
            return $this->falla('Escribe un correo y una contraseña válidos.', (string) $this->request->getPost('correo'));
        }

        $correo   = strtolower(trim((string) $this->request->getPost('correo')));
        $password = (string) $this->request->getPost('password');

        $modelo  = new UsuarioModel();
        $usuario = $modelo->where('correo', $correo)->first();

        // Cuenta bloqueada temporalmente por intentos fallidos
        if ($usuario && $usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
            return $this->falla('Demasiados intentos fallidos. Intenta de nuevo en unos minutos.', $correo);
        }

        if (! $usuario) {
            // Gasta el mismo tiempo que una verificación real (no revela si el correo existe)
            password_hash($password, PASSWORD_DEFAULT);
            return $this->falla('Correo o contraseña incorrectos.', $correo);
        }

        $destino = $cfg->panelPorRol[$usuario['rol']] ?? null;

        if (! password_verify($password, $usuario['password']) || ! (int) $usuario['activo'] || ! $destino) {
            $intentos = (int) $usuario['intentos_fallidos'] + 1;
            $datos    = ['intentos_fallidos' => $intentos];

            if ($intentos >= $cfg->maxIntentos) {
                $datos['intentos_fallidos'] = 0;
                $datos['bloqueado_hasta']   = date('Y-m-d H:i:s', time() + $cfg->minutosBloqueo * 60);
            }
            $modelo->update($usuario['id_usuario'], $datos);

            return $this->falla('Correo o contraseña incorrectos.', $correo);
        }

        // ── Login correcto ──
        $datos = [
            'intentos_fallidos' => 0,
            'bloqueado_hasta'   => null,
            'ultimo_acceso'     => date('Y-m-d H:i:s'),
        ];
        // Si el algoritmo de hash mejoró desde que se creó la contraseña, la actualiza
        if (password_needs_rehash($usuario['password'], PASSWORD_DEFAULT)) {
            $datos['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $modelo->update($usuario['id_usuario'], $datos);

        $sesion = session();
        $sesion->regenerate(true); // nuevo ID de sesión: evita "session fixation"
        $sesion->set([
            'logged_in'     => true,
            'id_usuario'    => (int) $usuario['id_usuario'],
            'nombre'        => $usuario['nombre'],
            'correo'        => $usuario['correo'],
            'rol'           => $usuario['rol'],
            'login_at'      => time(),
            'last_activity' => time(),
        ]);

        return redirect()->to(base_url($destino));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login?aviso=salio'));
    }

    // ───────────── Apoyos ─────────────
    private function falla(string $mensaje, string $correo)
    {
        return redirect()->to(base_url('login'))
            ->with('error', $mensaje)
            ->with('correo_previo', $correo);
    }

    public function estado()
    {
        $cfg = config('Auth');
        $s = session();
        $t = time();

        $vigente = $s->get('logged_in')
            && ($t - (int) $s->get('last_activity')) <= $cfg->inactividad
            && ($t - (int) $s->get('login_at')) <= $cfg->duracionMaxima;

        return $this->response
        ->setHeader('Cache-Control', 'no-store')
        ->setJSON(['vigente' => (bool) $vigente]);
    }

}
