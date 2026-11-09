<?php

namespace App\Controllers\inventario;
//enlazar con el modelo
use App\Models\MermaModel;
use App\Models\EntradaModel;
use Config\View;
use App\Controllers\BaseController;

class MermaController extends BaseController
{

    public function registrar_merma(): string
    {
        $entradaModel = new \App\Models\EntradaModel();
        $data['entradas'] = $entradaModel->getEntradasConProducto();
        return view('inventario/merma/v_alta_merma', $data); 
    }

    /*public function guarda_merma()
    {
         $mmerma = new MermaModel();

        $datos_merma = [
            'cantidad'   => $this->request->getPost('cantidad'),
            'fecha'      => $this->request->getPost('fecha'),
            'notas'      => $this->request->getPost('notas'),
            'id_entrada' => $this->request->getPost('id_entrada'),
        ];

        // Validación básica
        if (empty($datos_merma['cantidad']) || empty($datos_merma['fecha']) || empty($datos_merma['id_entrada'])) {
            echo "error";
            exit;
        }

        $mmerma->insert($datos_merma);
        echo "ok";

        exit;
    }

    public function borrar_merma($id = null){
        $mmerma = new MermaModel();
        $mmerma ->delete($id);
        return "ok";
    }*/

    //guarda con validación de cantidad d emerma
    /*
    public function guarda_merma()
    {
        $mmerma   = new MermaModel();
        $mentrada = new \App\Models\EntradaModel();

        $cantidad   = (float) $this->request->getPost('cantidad');
        $id_entrada = (int)   $this->request->getPost('id_entrada');
        $fecha      = $this->request->getPost('fecha');
        $notas      = $this->request->getPost('notas');

        if (empty($cantidad) || empty($fecha) || empty($id_entrada)) {
            echo "error_campos"; exit;
        }

        // Disponible = cantidad entrada - mermas ya registradas
        $entrada        = $mentrada->find($id_entrada);
        $mermaAcumulada = $mmerma->getMermaAcumulada($id_entrada);
        $disponible     = $entrada['cantidad'] - $mermaAcumulada;

        if ($cantidad > $disponible) {
            echo "excede|" . $disponible . "|" . $entrada['u_compra'];
            exit;
        }

        $mmerma->insert([
            'cantidad'   => $cantidad,
            'fecha'      => $fecha,
            'notas'      => $notas,
            'id_entrada' => $id_entrada,
        ]);

        echo "ok"; exit;
    }*/
    public function guarda_merma()
    {
        $mmerma   = new MermaModel();
        $mentrada = new \App\Models\EntradaModel();

        $cantidadKg = (float) $this->request->getPost('cantidad'); // kg recibidos
        $id_entrada = (int)   $this->request->getPost('id_entrada');
        $fecha      = $this->request->getPost('fecha');
        $notas      = $this->request->getPost('notas');

        if (empty($cantidadKg) || empty($fecha) || empty($id_entrada)) {
            echo "error_campos"; exit;
        }

        $entrada     = $mentrada->find($id_entrada);
        $equivalente = (float) $entrada['equivalente']; // kg por caja

        // Convertir kg → cajas para guardar en BD
        $cantidadCajas = $cantidadKg / $equivalente;

        // Validar que no exceda lo disponible (en cajas)
        $mermaAcumulada = $mmerma->getMermaAcumulada($id_entrada); // en cajas
        $disponible     = $entrada['cantidad'] - $mermaAcumulada;  // en cajas

        if ($cantidadCajas > $disponible) {
            $disponibleKg = $disponible * $equivalente;
            echo "excede|" . $disponibleKg . "|" . $entrada['u_venta'];
            exit;
        }

        $mmerma->insert([
            'cantidad'   => $cantidadCajas, // guarda en cajas, igual que antes
            'fecha'      => $fecha,
            'notas'      => $notas,
            'id_entrada' => $id_entrada,
        ]);

        echo "ok"; exit;
    }

    

    public function listar_merma()
    {   
            
        //try para ver errores (temporalmente)
        /*try {
            $mmerma = new MermaModel();

            $filtros = [
                'q' => $this->request->getGet('q') ?? ''
            ];

            $datos = [
                'datos_merma' => $mmerma->getMermasConEntrada($filtros['q'])->paginate(5),
                'pager'       => $mmerma->pager,
                'filtros'     => $filtros,

                'url_tabla'      => base_url('tabla_merma')
            ];

            return view('layout/v_header')
                . view('merma/v_principal_merma', $datos)   
                . view('layout/v_footer');

        } catch (\Exception $e) {
            dd($e->getMessage());
        }*/

        
            $mmerma = new MermaModel();

            $filtros = [
                'q' => $this->request->getGet('q') ?? ''
            ];

            $datos = [
                'datos_merma' => $mmerma->busquedaCompleja($filtros)->paginate(5),
                'pager'       => $mmerma->pager,
                'filtros'     => $filtros,
                'url_tabla'   => base_url('merma/tabla_merma'),
                'titulo'      => 'Gestión de mermas',
            ];

            return $this->render('inventario/merma/v_principal_merma', $datos, 'inventario');
        
        
    }

    public function tabla_merma()
    {
        $mmerma = new MermaModel();
        $merma = $mmerma->findAll();
        $datos = ["datos_merma" => $merma];

        return view("inventario/merma/v_tabla_merma", $datos);
    }

    public function recupera_merma($id_merma = null)
    {
        if ($id_merma != null) {
            $mmerma   = new MermaModel();
            $mentrada = new EntradaModel();

            $datos['datos_merma'] = $mmerma->getMermaConEquivalente($id_merma); 
            $datos['entradas']    = $mentrada->getEntradasConProducto();

            return view("inventario/merma/v_editar_merma", $datos);
        }
    }

    public function actualiza_merma()
    {
        $id       = $this->request->getPost("id_merma");
        $mmerma   = new MermaModel();
        $mentrada = new \App\Models\EntradaModel();

        $cantidadKg = (float) $this->request->getPost("cantidad");
        $id_entrada = (int)   $this->request->getPost("id_entrada");

        $entrada     = $mentrada->find($id_entrada);
        $equivalente = (float) $entrada['equivalente'];

        // Convertir kg → cajas para guardar
        $cantidadCajas = $cantidadKg / $equivalente;

        $datos_mod = [
            "id_entrada" => $id_entrada,
            "cantidad"   => $cantidadCajas,
            "fecha"      => $this->request->getPost("fecha"),
            "notas"      => $this->request->getPost("notas"),
        ];

        $mmerma->update($id, $datos_mod);
        echo "ok";
        exit;
    }
}