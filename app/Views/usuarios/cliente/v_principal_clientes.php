<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/comun/v_paginacion.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/comun/v_tabla_datos.css') ?>?v=2">

<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="fv-pagina">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 id="titulo">Gestión de Clientes</h3>
            <small>Administra la información de tus clientes</small>
        </div>
        <?php if (puede('clientes.crear')): ?>
        <button class="btn btn-success"
            onclick="abrirModal('<?= base_url('alta_clientes') ?>', 'Registrar Cliente')">
            + Registrar Cliente
        </button>
        <?php endif; ?>
    </div>

    <!-- BUSCADOR -->

    <div class="card mb-3 p-3" id="buscador-card" data-url-tabla="<?= esc($url_tabla) ?>">
        <label><b><i class="fa fa-search"></i> Búsqueda de Clientes</b></label>
        <div class="input-group mt-2">
            <input type="text" id="buscar" class="form-control mt-2" value="<?= esc($filtros['q']) ?>"
                   placeholder="Buscar por nombre, apellido, teléfono...">

                    <?php if (!empty($filtros['q'])): ?>
                        
                    <?php endif; ?>
        </div>
    </div>

    <div class="card p-3">
        <h5>Listado de Clientes (<?= count($datos_cliente) ?>)</h5>

        <div class="table-responsive">
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Nombre*</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($datos_cliente)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        Sin resultados para "<strong><?= esc($filtros['q'] ?? '') ?></strong>"
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach($datos_cliente as $cliente): ?>
                <td class="nombre"><?= estrella_cliente($cliente['preferencial'] ?? 0) ?> <?= esc($cliente['nombre']) ?></td>           
                    <td><?= esc($cliente['ap_p']) ?></td>    
                    <td><?= esc($cliente['ap_m']) ?></td>         
                    <td class="descripcion"><?= esc($cliente['tel']) ?></td>            
                    <td>
                        <?php if (puede('clientes.editar')): ?>
                        <button class="btn btn-warning btn-sm" 
                            onclick="abrirModal('<?= base_url('editarc/'.$cliente['id_cliente']) ?>', 'Editar Cliente')">
                            <i class="fa fa-pencil"></i> Editar
                        </button>
                        <?php endif; ?>
                        <?php if (puede('clientes.eliminar')): ?>
                        <button class="btn btn-danger btn-sm eliminar-btn"
                            data-id="<?= $cliente['id_cliente'] ?>">
                            <i class="fa fa-trash"></i> Eliminar
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <?= $pager->links("default","v_paginacion") ?>
        <?= $this->section('estilos') ?>
            <link rel="stylesheet" href="<?= base_url('css/comun/v_paginacion.css') ?>">
            <link rel="stylesheet" href="<?= base_url('css/comun/v_tabla_datos.css') ?>">   <!-- nueva -->
        <?= $this->endSection() ?>
    </div>
</div>

<div class="modal fade" id="miModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeContenido" style="width:100%; height:580px; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>
    let modal = new bootstrap.Modal(document.getElementById('miModal'));

    function abrirModal(url, titulo) {
        document.querySelector('#miModal .modal-title').textContent = titulo;
        $("#iframeContenido").attr("src", url);
        modal.show();
    }

    function cerrarModal() {
        modal.hide();
        $("#iframeContenido").attr("src", "");
        location.reload(); 
    }

    $(document).on('click', '.eliminar-btn', function() {
        var id = $(this).data('id');

        $.ajax({
            url: "<?= base_url('verificar_cliente') ?>/" + id,
             
            method: "GET",
            success: function(response) {
                if (!response.puede_eliminar) {
                    alert("No se puede eliminar: " + response.mensaje);
                    return;
                }

                if (confirm("¿Estás seguro de que deseas eliminar este cliente?")) {
                    $.ajax({
                        url:  "<?= base_url('eliminarc/') ?>/" + id,
                       
                        method: "GET",
                        success: function() {
                            alert("Cliente eliminado correctamente.");
                            location.reload();
                        }
                    });
                }
            },
            error: function(xhr) {
                console.log("ERROR:", xhr.status, xhr.responseText);
                alert("Error: " + xhr.status + " - revisa la consola");
            }
        });
    });
</script>

<!--- ------------------------BUSCADOR ----------------- -->


<script>
    /*
        PASO 1: Obtenemos la URL que el controlador preparó.
        El controlador generó la URL con base_url() y la guardó en $url_tabla,
        la vista la puso en el atributo data-url-tabla del HTML,
        y aquí el JS la lee desde ahí sin necesitar PHP.

        la urlTabla viene de base_url() que genera
        el propio servidor, el usuario no puede modificar este valor.
    */
    const urlTabla = document.getElementById('buscador-card').dataset.urlTabla;

    /*
        PASO 2: Variable para el temporizador del buscador.
        Se usa para no buscar con cada letra que el usuario escribe,
        sino esperar a que termine de escribir.
    */
    let delay = null;

    /*
        PASO 3: Escuchamos cuando el usuario escribe en el buscador.
        Cada vez que escribe una letra, este evento se activa.

        Aquí el usuario escribe lo que quiera, pero
        este código solo lee texto, no lo ejecuta ni lo manda directo a la BD.
    */
    document.getElementById('buscar').addEventListener('input', function () {

        /*
            PASO 3.1: Cancelamos la búsqueda anterior.
            Si el usuario sigue escribiendo, reiniciamos el temporizador
            para no buscar hasta que haga una pausa.
        */
        clearTimeout(delay);

        /*
            PASO 3.2: Guardamos lo que escribió el usuario.
            trim() elimina espacios en blanco al inicio y al final.
        */
        const termino = this.value.trim();

        /*
            PASO 3.3: Esperamos 400ms antes de buscar (debounce).
            Si el usuario no escribe nada en 400ms, ejecutamos la búsqueda.
            Esto evita hacer una petición al servidor por cada letra.
        */
        delay = setTimeout(function () {

            /*
                PASO 3.4: Tomamos la URL actual de la página.
                Ejemplo: http://localhost/productos/listar
            */
            const url = new URL(window.location.href);

            /*
                PASO 3.5: Agregamos el término de búsqueda a la URL.
                Ejemplo: http://localhost/productos/listar?q=leche

                searchParams escapa automáticamente los caracteres peligrosos. Por ejemplo
                si alguien escribe alert('hack')>,
                el navegador lo convierte a texto inofensivo:
                ?q=%3Cscript%3Ealert%28%27hack%27%29%3C%2Fscript%3E
                El servidor recibe texto plano, no código ejecutable.

                Esto es lo que el controlador recibe con getGet('q'),
                y el modelo lo usa con like() que internamente usa
                prepared statements, evitando SQL Injection.
            */
            url.searchParams.set('q', termino);

            /*
                PASO 3.6: Si el buscador está vacío, quitamos el ?q de la URL.
                Ejemplo: regresa a http://localhost/productos/listar
                para que el controlador muestre todos los productos.
            */
            if (!termino) url.searchParams.delete('q');

            /*
                PASO 3.7: Redirigimos a la nueva URL.
                Esto recarga la página y el controlador filtra
                los productos según el término buscado.
            */
            window.location.href = url.toString();

        }, 400);
    });

    /*
        PASO 4: Cuando la página termina de cargar,
        mantenemos el foco en el buscador y movemos
        el cursor al final del texto, para que el usuario
        pueda seguir escribiendo sin tener que hacer clic.

        solo mueve el cursor al final
        del input, no toca datos del usuario ni hace
        peticiones al servidor.
    */
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('buscar');

        if (input) {
            // Guardamos el texto actual del buscador
            const val = input.value;

            // Limpiamos, enfocamos y volvemos a poner el texto
            // Esto mueve el cursor al final automáticamente
            input.value = '';
            input.focus();
            input.value = val;
        }
    });

    /*
        PASO 5: Función para actualizar la tabla sin recargar toda la página.
        Usa jQuery para pedir solo el HTML de la tabla al controlador
        y reemplaza el contenido del <tbody> con la respuesta.
        Se llama, por ejemplo, después de borrar o editar un producto.

        La URL viene del servidor (urlTabla), no del usuario,
        y solo trae HTML de la tabla, no manda datos del usuario al servidor.
        La única responsabilidad de seguridad aquí recae en el controlador,
        que es quien genera el HTML que se inserta en la tabla.
    */
    function recargarTabla() {
        $.get(urlTabla, function(html) {
            $("tbody").html(html);
        });
    }
</script>
<?= $this->endSection() ?>
