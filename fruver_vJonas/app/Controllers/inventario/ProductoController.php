<?php

namespace App\Controllers\inventario;
//enlazar con el modelo
use App\Models\ProductoModel;
use App\Models\EntradaModel;
use Config\View;
use App\Controllers\BaseController;

class ProductoController extends BaseController
{
    public function registrar_productos(): string
    {
        return view('inventario/producto/v_alta_productos');
    }
    
    public function guardar_producto()
    {
        $mproducto = new ProductoModel();

        $datos_producto = [
            "nombre" => mb_convert_case(
                    trim((string) $this->request->getPost('nombre')),
                    MB_CASE_TITLE,
                    'UTF-8'
                ),
            "categoria" => $this->request->getPost("categoria"),
            "descripcion" => $this->request->getPost("descripcion")
        ];

        if(empty($datos_producto["nombre"]) || ! in_array($datos_producto["categoria"], ['fruta', 'verdura', 'hierba'], true)){
        echo "error";
        exit;
        }

        $repeted_prod = $mproducto->buscaNombre($datos_producto['nombre']);
        $rep_counter = 0;//// Contador de coincidencias exactas encontradas

        foreach($repeted_prod as $product){
            //strtopper convierte a mayusculas sin importar si se ingreso TOMATE o tomate
            if(strtoupper($datos_producto['nombre']) == strtoupper($product['nombre'])){
                $rep_counter += 1;
            }
        }

        if($rep_counter > 0){
            echo "duplicado";
            exit;
        }

        // FOTO (opcional)
        $foto = $this->guardarFoto();
        if ($foto === false) {
            echo "foto_invalida";
            exit;
        }
        if ($foto !== null) {
            $datos_producto['imagen'] = $foto;
        }

        // CÓDIGO ÚNICO automático según la categoría (FRU-001, VER-001, HIE-001)
        $datos_producto['codigo'] = $mproducto->generarCodigo($datos_producto['categoria']);

        $mproducto->insert($datos_producto);
        echo "ok";

        exit;
    }
    
    public function listar_productos()
    {   
        $mproducto = new ProductoModel();

        $filtros = [
            'q' => $this->request->getGet('q') ?? ''
        ];

        $datos = [
            'datos_producto' => $mproducto->busquedaCompleja($filtros)->paginate(5),
            'pager'          => $mproducto->pager,
            'filtros'        => $filtros,

             'url_tabla'      => base_url('tabla_productos')
        ];


        return $this->render('inventario/producto/v_principal_productos', $datos, 'inventario');
    }

    public function tabla_productos()
    {
        $mproducto = new ProductoModel();
        $productos = $mproducto->findAll();
        $datos = ["datos_producto" => $productos];

        return view("inventario/producto/v_tabla_productos", $datos);
    }



    public function verificarProducto($id = null) //ver, si tiene entradas
    {
        $entradaModel = new \App\Models\EntradaModel();
        $entradas = $entradaModel->where('id_producto2', $id)->countAllResults(); //filtra por el id, cuenta cuantos registros coinciden con ese filtro

         // Si el producto tiene al menos una entrada, no se permite eliminar
        // Devuelve JSON con puede_eliminar en false y un mensaje explicando cuántas entradas tiene
        if ($entradas > 0) {
            return $this->response->setJSON([
                'puede_eliminar' => false,
                'mensaje' => "Este producto tiene {$entradas} entrada(s) registrada(s) y no puede ser eliminado."
            ]);
        }

        // Si no tiene entradas, indica que sí puede eliminarse
        return $this->response->setJSON([
            'puede_eliminar' => true
        ]);
    }

    public function borrar($id = null)
    {
        $mproducto = new ProductoModel();
        $mproducto->delete($id);

        return $this->response->setJSON([
            'success' => 'Producto eliminado correctamente.'
        ]);
    }




    public function recupera($id_producto=null){
        if($id_producto != null){
            $mproducto = new ProductoModel();
            $datos['datos_producto'] = $mproducto->getProducto($id_producto);

            return view("inventario/producto/v_editar_productos", $datos);
        }
    }

    public function actualizar(){
        $id= $this -> request -> getPost("id_producto");
        $mproducto = new ProductoModel();
        $producto = $mproducto->getProducto($id);

        if( !$producto || empty($datos_producto["nombre"]) || ! in_array($datos_producto["categoria"], ['fruta', 'verdura', 'hierba'], true)){
        echo "error";
        exit;
        }

        $datos_producto = [
            "nombre" => $producto["nombre"],
            "categoria" => $producto["categoria"],
            "descripcion" => $producto["descripcion"]
        ];

        $repeted_prod = $mproducto->buscaNombre($datos_producto['nombre']);
        $rep_counter = 0;//// Contador de coincidencias exactas encontradas

        foreach($repeted_prod as $product){
            //strtopper convierte a mayusculas sin importar si se ingreso TOMATE o tomate
            if(strtoupper($datos_producto['nombre']) == strtoupper($product['nombre'])){
                $rep_counter += 1;
            }
        }

        if($rep_counter > 0){
            echo "duplicado";
            exit;
        }

        // El código NO se modifica: es el identificador del producto
        $datos_mod = [
                        "nombre" => mb_convert_case(
                            trim((string) $this->request->getPost('nom')),
                            MB_CASE_TITLE,
                            'UTF-8'
                        ),
                        "categoria" => $this -> request -> getPost("categoria"),
                        "descripcion" => $this -> request -> getPost("descripcion")];

        // FOTO: solo cambia si se eligió una nueva
        $foto = $this->guardarFoto($producto['imagen'] ?? null);
        if ($foto === false) {
            echo "foto_invalida";
            exit;
        }
        if ($foto !== null) {
            $datos_mod['imagen'] = $foto;
        }

        echo $mproducto->update($id, $datos_mod) ? "ok" : "error";
        exit;
    }

    //HISTORIAL
    public function historialEntradas(int $id_producto)
    {
        $entradaModel = new EntradaModel();

        // Llama al método del modelo que consulta en la base de datos
        // todas las entradas relacionadas con el id_producto recibido
        $entradas = $entradaModel->getEntradasPorProducto($id_producto);

        // Devuelve las entradas en formato JSON junto con un indicador de éxito
        // para que el frontend pueda verificar si la petición fue exitosa
        return $this->response->setJSON([
            'success' => true,
            'entradas' => $entradas
        ]);
    }

    // ───────────── FOTO DEL PRODUCTO ─────────────
    // Guarda la foto en public/img/productos y regresa el nombre del archivo.
    // null  = no se eligió foto (no se cambia nada)
    // false = el archivo no es una imagen válida (JPG, PNG o WEBP de máximo 2 MB)
    private function guardarFoto(?string $anterior = null)
    {
        $foto = $this->request->getFile('imagen');

        if (! $foto || $foto->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $tipos = ['image/jpeg', 'image/png', 'image/webp'];
        if (! $foto->isValid() || ! in_array($foto->getMimeType(), $tipos, true) || $foto->getSize() > 2 * 1024 * 1024) {
            return false;
        }

        $carpeta = FCPATH . 'img/productos';
        $nombre  = $foto->getRandomName();
        $foto->move($carpeta, $nombre);

        // Borra la foto anterior para no acumular archivos
        if ($anterior && is_file($carpeta . '/' . $anterior)) {
            unlink($carpeta . '/' . $anterior);
        }

        return $nombre;
    }
}
