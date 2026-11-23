<?php

namespace App\Controllers\Usuarios;
//enlazar con el modelo
use App\Models\ClienteModel;
use App\Models\UsuarioModel;
use Config\View;
use App\Controllers\BaseController;

class ClienteController extends BaseController
{

    //definición de objetos de los modelos
    protected $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
    }

    public function cliente()
    {
        $filtros = [
            'q' => $this->request->getGet('q') ?? ''
        ];

        $datosc = [
            'datos_cliente' => $this->clienteModel->busquedaCompleja($filtros)->paginate(5),
            'pager'          => $this->clienteModel->pager,
            'filtros'        => $filtros,

            'url_tabla' => base_url('tabla_clientes')
        ];

        $datosc['titulo'] = 'Gestión de clientes';

        return $this->render('usuarios/cliente/v_principal_clientes', $datosc, 'clientes');
    }

    public function registrar_clientes()
    {
        return view('usuarios/cliente/v_alta_clientes');
    }

    public function guardar_cliente()
    {
        $mcliente = $this->clienteModel;

        $datos_cliente = [
            "nombre" => $this->request->getPost("nombre"),
            "ap_p" => $this->request->getPost("ap_p"),
            "ap_m" => $this->request->getPost("ap_m"),
            "tel" => $this->request->getPost("tel"),
            "preferencial" => $this->request->getPost("preferencial") ? 1 : 0
        ];

        if (empty($datos_cliente["nombre"]) || empty($datos_cliente["ap_p"]) || empty($datos_cliente["tel"])) {
            echo "error";
            exit;
        }

        if (! empty($mcliente->ClienteRepetido($datos_cliente['nombre'], $datos_cliente['ap_p']))) {
            echo "duplicado";
            exit;
        }

        // Cuenta opcional: si se captura correo o contraseña, se valida y se crea junto con el cliente
        $correo   = trim((string) $this->request->getPost('correo'));
        $password = trim((string) $this->request->getPost('password'));
        $cuenta   = null;

        if ($correo !== '' || $password !== '') {
            $error = (new UsuarioModel())->validarCuenta($correo, $password);
            if ($error) {
                echo $error;
                exit;
            }
            $cuenta = ['correo' => $correo, 'password' => $password];
        }

        try {
            $mcliente->crearConCuenta($datos_cliente, $cuenta);
            echo "ok";
        } catch (\Throwable $e) {
            log_message('error', 'guardar_cliente: ' . $e->getMessage());
            echo "error_guardar";
        }
        exit;
    }

    public function form_editc($id_cliente = null)
    {
        if ($id_cliente == null) {
            return;
        }

        $cliente = $this->clienteModel->getCliente($id_cliente);

        $cuenta = null;
        if (! empty($cliente['id_usuario'])) {
            // La contraseña solo se descifra si el rol tiene el permiso
            $cuenta = (new UsuarioModel())->getCuenta((int) $cliente['id_usuario'], puede('usuarios.ver_password'));
        }

        return view("usuarios/cliente/v_editar_clientes", [
            'datos_cliente' => $cliente,
            'cuenta'        => $cuenta,
        ]);
    }

    public function actualizarc()
    {
        $id       = $this->request->getPost("id_cliente");
        $mcliente = $this->clienteModel;
        $cliente  = $mcliente->getCliente($id);

        if (! $cliente) {
            echo "error";
            exit;
        }

        $datos_mod = [
            "nombre" => $this->request->getPost("nombre"),
            "ap_p" => $this->request->getPost("ap_p"),
            "ap_m" => $this->request->getPost("ap_m"),
            "tel" => $this->request->getPost("tel"),
            "preferencial" => $this->request->getPost("preferencial") ? 1 : 0
        ];

        // Solo se puede crear la cuenta si todavía no tiene una
        $cuenta   = null;
        $correo   = trim((string) $this->request->getPost('correo'));
        $password = trim((string) $this->request->getPost('password'));

        if (empty($cliente['id_usuario']) && ($correo !== '' || $password !== '')) {
            $error = (new UsuarioModel())->validarCuenta($correo, $password);
            if ($error) {
                echo $error;
                exit;
            }
            $cuenta = ['correo' => $correo, 'password' => $password];
        }

        try {
            $mcliente->actualizarConCuenta((int) $id, $datos_mod, $cuenta);
            echo "ok";
        } catch (\Throwable $e) {
            log_message('error', 'actualizarc: ' . $e->getMessage());
            echo "error_guardar";
        }
        exit;
    }

    // Primero verifica si se puede eliminar
    public function verificarCliente($id = null)
    {
        $pedidoModel = new \App\Models\PedidoModel();
        $mcliente = $this->clienteModel;

        $pedidos = $pedidoModel->where('id_cliente1', $id)->countAllResults();

        $naddres = $mcliente->join('direccion', 'cliente.id_cliente = direccion.id_cliente')
        ->where('cliente.id_cliente', $id)
        ->countAllResults();

        if ($pedidos > 0) {
            return $this->response->setJSON([
                'puede_eliminar' => false,
                'mensaje' => "Este cliente tiene {$pedidos} pedido(s) registrado(s) y no puede ser eliminado."
            ]);
        }else {
            if ($naddres > 0) {
                return $this->response->setJSON([
                'puede_eliminar' => true
                ]);
            }

            if ($naddres == 0) {
                return $this->response->setJSON([
                'puede_eliminar' => true
                ]);
            }
        }
        
    }

    // Elimina sin validar (ya se validó antes)
    public function borrarc($id = null)
    {
        $this->clienteModel->eliminarCliente($id);

        return $this->response->setJSON([
            'success' => 'Cliente eliminado correctamente.'
        ]);
    }

    public function tabla_client()
    {
        $mcliente = $this->clienteModel;
        $cliente = $mcliente->findAll();
        $datos = ["clientes" => $cliente];

        return view("cliente/v_tabla_clientes", $datos);
    }
}
