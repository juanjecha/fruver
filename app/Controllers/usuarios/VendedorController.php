<?php

namespace App\Controllers\Usuarios;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;

/**
 * Cuentas de vendedores avanzados (solo el administrador: permiso "usuarios.*").
 * Un vendedor avanzado no tiene tabla propia: es una cuenta de la tabla "usuario" con rol "vendedor_avanzado".
 */
class VendedorController extends BaseController
{
    private const ROL = 'vendedor_avanzado';

    protected $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    // Pantalla principal (con el menú lateral de Clientes)
    public function vendedor()
    {
        $vendedores = $this->usuarioModel->cuentasPorRol(self::ROL)->paginate(10);

        if (puede('usuarios.ver_password')) {
            $vendedores = $this->usuarioModel->conPassword($vendedores);
        }

        return $this->render('usuarios/vendedor/v_principal_vendedores', [
            'titulo'     => 'Cuentas de vendedores avanzados',
            'vendedores' => $vendedores,
            'pager'      => $this->usuarioModel->pager,
        ], 'clientes');
    }

    // Formulario (se abre en el modal)
    public function registrar_vendedores(): string
    {
        return view('usuarios/vendedor/v_alta_vendedores');
    }

    public function guardar_vendedor()
    {
        $nombre   = trim((string) $this->request->getPost('nombre'));
        $tel      = trim((string) $this->request->getPost('tel'));
        $correo   = trim((string) $this->request->getPost('correo'));
        $password = trim((string) $this->request->getPost('password'));

        if ($nombre === '' || mb_strlen($nombre) > 100 || ! preg_match('/^\d{10}$/', $tel)) {
            return $this->response->setBody('error');
        }

        // Correo no repetido, formato válido y contraseña de 10 a 72 caracteres
        $error = $this->usuarioModel->validarCuenta($correo, $password);
        if ($error) {
            return $this->response->setBody($error);
        }

        try {
            // El rol lo fija el servidor: el formulario no puede pedir otro
            $this->usuarioModel->crearCuenta($correo, $password, self::ROL, $nombre, $tel);
            return $this->response->setBody('ok');
        } catch (\Throwable $e) {
            log_message('error', 'guardar_vendedor: ' . $e->getMessage());
            return $this->response->setBody('error_guardar');
        }
    }
}
