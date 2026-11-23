<?php

namespace App\Controllers\Usuarios;
//enlazar con el modelo
use App\Models\RepartidorModel;
use App\Models\UsuarioModel;
use App\Controllers\BaseController;

class RepartidorController extends BaseController
{
    //definición de objetos de los modelos
    protected $repartidorModel;

    public function __construct()
    {
        $this->repartidorModel = new RepartidorModel();
    }

    //CRUD repartidores

    // Pantalla principal del módulo (con el menú lateral de Repartidores)
    public function repartidor()
    {
        $data = $this->datosListado();
        $data['titulo'] = 'Gestión de repartidores';

        return $this->render('usuarios/repartidor/v_principal_repartidores', $data, 'repartidores');
    }

    // Solo la tabla (por si se quiere recargar sin refrescar toda la página)
    public function tabla_rep()
    {
        return view('usuarios/repartidor/v_tabla_repartidores', $this->datosListado());
    }

    public function registrar_repartidores(): string
    {
        return view('usuarios/repartidor/v_alta_repartidores');
    }

    public function guardar_repart()
    {
        $datos_repar = $this->datosDelFormulario();

        if (! $this->datosValidos($datos_repar)) {
            echo "error";
            exit;
        }

        if (! empty($this->repartidorModel->reparRepetido($datos_repar['nombre'], $datos_repar['ap_p']))) {
            echo "duplicado";
            exit;
        }

        // Cuenta opcional: si se captura correo o contraseña, se valida y se crea junto con el repartidor
        [$cuenta, $error] = $this->leerCuenta();
        if ($error) {
            echo $error;
            exit;
        }

        try {
            $this->repartidorModel->crearConCuenta($datos_repar, $cuenta, $this->tipoCuenta());
            echo "ok";
        } catch (\Throwable $e) {
            log_message('error', 'guardar_repart: ' . $e->getMessage());
            echo "error_guardar";
        }
        exit;
    }

    public function form_editr($id_repartidor = null)
    {
        $repartidor = $this->repartidorModel->getRepart($id_repartidor);

        if (! $repartidor) {
            return 'Repartidor no encontrado.';
        }

        $cuenta = null;
        if (! empty($repartidor['id_usuario'])) {
            // La contraseña solo se descifra si el rol tiene el permiso
            $cuenta = (new UsuarioModel())->getCuenta((int) $repartidor['id_usuario'], puede('usuarios.ver_password'));
        }

        return view('usuarios/repartidor/v_editar_repartidores', [
            'datos_repartidor' => $repartidor,
            'cuenta'           => $cuenta,
        ]);
    }

    public function actualizarrep()
    {
        $id        = $this->request->getPost('id_repartidor');
        $datos_mod = $this->datosDelFormulario();
        $actual    = $id ? $this->repartidorModel->getRepart($id) : null;

        if (! $actual || ! $this->datosValidos($datos_mod)) {
            echo "error";
            exit;
        }

        // Solo se puede crear la cuenta si todavía no tiene una
        $cuenta = null;
        if (empty($actual['id_usuario'])) {
            [$cuenta, $error] = $this->leerCuenta();
            if ($error) {
                echo $error;
                exit;
            }
        }

        try {
            $this->repartidorModel->actualizarConCuenta((int) $id, $datos_mod, $cuenta, $this->tipoCuenta());
            echo "ok";
        } catch (\Throwable $e) {
            log_message('error', 'actualizarrep: ' . $e->getMessage());
            echo "error_guardar";
        }
        exit;
    }

    public function borrarrep($id = null)
    {
        $this->repartidorModel->eliminarConCuenta($id);

        return $this->response->setJSON([
            'success' => 'Repartidor eliminado correctamente.'
        ]);
    }

    public function verificarRepartidor($id = null)
    {
        $pedidoModel = new \App\Models\PedidoModel();
        $pedidos = $pedidoModel->where('id_repartidor', $id)->countAllResults();

        if ($pedidos > 0) {
            return $this->response->setJSON([
                'puede_eliminar' => false,
                'mensaje' => "Este repartidor tiene {$pedidos} pedido(s) asignado(s) y no puede ser eliminado."
            ]);
        }

        return $this->response->setJSON([
            'puede_eliminar' => true
        ]);
    }


    // ───────────── Apoyos ─────────────

    // Lo que necesita la tabla: repartidores de la página actual, paginación y búsqueda
    private function datosListado(): array
    {
        $filtros = [
            'q' => trim((string) ($this->request->getGet('q') ?? '')),
        ];

        return [
            'repartidores' => $this->repartidorModel->busquedaCompleja($filtros)->paginate(5),
            'pager'        => $this->repartidorModel->pager,
            'filtros'      => $filtros,
        ];
    }

    // Lee los campos del formulario (alta y edición usan los mismos)
    private function datosDelFormulario(): array
    {
        return [
            'nombre'    => trim((string) $this->request->getPost('nombre')),
            'ap_p'      => trim((string) $this->request->getPost('ap_p')),
            'ap_m'      => trim((string) $this->request->getPost('ap_m')),
            'tel'       => trim((string) $this->request->getPost('tel')),
            'direccion' => trim((string) $this->request->getPost('direccion')),
            'notas'     => trim((string) $this->request->getPost('notas')),
        ];
    }

    // Obligatorios: nombre, apellido paterno, teléfono de 10 dígitos y dirección
    private function datosValidos(array $d): bool
    {
        return $d['nombre'] !== ''
            && $d['ap_p'] !== ''
            && preg_match('/^\d{10}$/', $d['tel'])
            && $d['direccion'] !== '';
    }

    //___________
    // Lee los campos opcionales de cuenta. Devuelve [cuenta|null, códigoDeError|null]
    private function leerCuenta(): array
    {
        $correo   = trim((string) $this->request->getPost('correo'));
        $password = trim((string) $this->request->getPost('password'));

        if ($correo === '' && $password === '') {
            return [null, null];                       // sin cuenta: se guarda solo el repartidor
        }

        $error = (new UsuarioModel())->validarCuenta($correo, $password);

        return $error ? [null, $error] : [['correo' => $correo, 'password' => $password], null];
    }

    // Tipo de cuenta elegido en el formulario (lista cerrada: nunca se acepta otro rol)
    private function tipoCuenta(): string
    {
        $tipo = (string) $this->request->getPost('tipo_cuenta');

        return in_array($tipo, ['repartidor', 'repartidor_avanzado'], true) ? $tipo : 'repartidor';
    }
}
