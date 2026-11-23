<?php
namespace App\Controllers\inventario;
use App\Models\EntradaModel;
use App\Models\MermaModel;
use App\Controllers\BaseController;

class EntradaController extends BaseController
{
    protected $entradaModel;

    public function __construct()
    {
        $this->entradaModel = new EntradaModel();
    }

    public function entrada()
    {   
        $filtros = [
            'q' => $this->request->getGet('q') ?? ''
        ];

        $data=[
        'entradas' => $this->entradaModel->busquedaCompleja($filtros)->paginate(4),
        'pager'=>$this->entradaModel->pager,
        'filtros'=>$filtros,
        'url_tabla'=>base_url('tabla_entradas')
        ];

        $data['titulo'] = 'Gestión de entradas';

        return $this->render('inventario/entrada/v_principal_entradas', $data, 'inventario');
    }

    public function guardar_entrada()
    {
        $mentrada = $this->entradaModel;

        $datos_entrada = [
            "fecha"          => $this->request->getPost("fecha"),
            "fecha_caducidad"  => $this->request->getPost("fecha_caducidad"),
            "cantidad"       => $this->request->getPost("cantidad"),
            "u_compra"       => $this->request->getPost("u_compra"),
            "u_venta"        => $this->request->getPost("u_venta"),
            "precio_compra"  => $this->request->getPost("precio_compra"),
            "equivalente"    => $this->request->getPost("equivalente"),
            "conversion"     => $this->request->getPost("conversion"),
            "cantidad_vendida" => 0,
            "id_producto2"   => $this->request->getPost("id_producto2"),
            // fecha_caducidad la pone el trigger automáticamente
        ];

        if ($mentrada->insert($datos_entrada)) {
            echo "ok";
        } else {
            echo "error";
        }
        exit;
    }

    public function registrar_entradas()
    {
        $productoModel = new \App\Models\ProductoModel(); 
        $data['productos'] = $productoModel->orderBy('nombre', 'ASC')->findAll();

        // PUNTO 15 - Repetir entrada: si llega ?repetir=ID, el formulario se llena
        // con los datos de esa entrada (la fecha se queda en la de hoy)
        $data['base'] = null;
        $idRepetir = (int) $this->request->getGet('repetir');
        if ($idRepetir > 0) {
            $data['base'] = $this->entradaModel->getEntrada($idRepetir);
        }

        return view('inventario/entrada/v_alta_entradas', $data);
        
    }

    public function tabla_entradas()
    {
        $filtros = [
            'q' => $this->request->getGet('q') ?? ''
        ];

        $datos = [
            'entradas' => $this->entradaModel->busquedaCompleja($filtros)->paginate(4),
            'pager'    => $this->entradaModel->pager,
            'filtros'  => $filtros,
        ];

        return view('inventario/entrada/v_tabla_entradas', $datos);
    }

    public function verificarEntrada($id = null)
    {
        $mermaModel = new \App\Models\MermaModel();
        $mermas = $mermaModel->where('id_entrada', $id)->countAllResults();

        if ($mermas > 0) {
            return $this->response->setJSON([
                'puede_eliminar' => false,
                'mensaje' => "Esta entrada tiene {$mermas} merma(s) registrada(s) y no puede ser eliminada."
            ]);
        }

        return $this->response->setJSON([
            'puede_eliminar' => true
        ]);
    }

    public function borrarEn($id = null)
    {
        $this->entradaModel->delete($id);

        return $this->response->setJSON([
            'success' => 'Entrada eliminada correctamente.'
        ]);
    }

    public function form_editent($id_entrada = null)
    {
        if ($id_entrada != null) {
            $productoModel = new \App\Models\ProductoModel();

            $datos['datos_entrada'] = $this->entradaModel->getEntrada($id_entrada);
            $datos['productos']     = $productoModel->orderBy('nombre', 'ASC')->findAll();

            return view('inventario/entrada/v_editar_entradas', $datos);
        }
    }

    public function actualizarent()
    {
        $id      = $this->request->getPost("id_entrada");
        $mentrada = $this->entradaModel;

        $datos_mod = [
            "fecha"            => $this->request->getPost("fecha"),
            "fecha_caducidad"  => $this->request->getPost("fecha_caducidad"),
            "cantidad"         => $this->request->getPost("cantidad"),
            "u_compra"         => $this->request->getPost("u_compra"),
            "u_venta"          => $this->request->getPost("u_venta"),
            "precio_compra"    => $this->request->getPost("precio_compra"),
            "equivalente"      => $this->request->getPost("equivalente"),
            "conversion"       => $this->request->getPost("conversion"),   
            "cantidad_vendida"  => $this->request->getPost("cantidad_vendida"),
            "id_producto2"     => $this->request->getPost("id_producto2"), 
        ];

        if ($mentrada->update($id, $datos_mod)) {
            echo "ok";
        } else {
            echo "error";
        }
        exit;
    }

    //HISTORIAL
    public function historialMermas(int $id_entrada)
    {
        $mermaModel = new MermaModel();

        // Llama al método del modelo que consulta en la base de datos
        // todas las mermas relacionadas con el id_entrada recibido
        $mermas = $mermaModel->getMermasPorEntrada($id_entrada);

        // Devuelve las mermas en formato JSON junto con un indicador de éxito
        // para que el frontend pueda verificar si la petición fue exitosa
        return $this->response->setJSON([
            'success' => true,
            'mermas'  => $mermas
        ]);
    }
}