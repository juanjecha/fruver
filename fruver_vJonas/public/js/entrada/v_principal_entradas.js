
        let modal = new bootstrap.Modal(document.getElementById('miModal'));

        function abrirModal(url, titulo) {
        document.querySelector('#miModal .modal-title').textContent = titulo;
        $("#iframeContenido").attr("src", url);
        modal.show();
        }


        function cerrarModal(){
            modal.hide();
            $("#iframeContenido").attr("src", "");
            location.reload();
        }

        // El iframe toma la altura de su formulario, así nunca se corta el botón de guardar
        const iframeModal = document.getElementById('iframeContenido');
        function ajustarAltura() {
            try {
                const doc = iframeModal.contentDocument;
                if (doc && doc.body && doc.body.children.length) {
                    iframeModal.style.height = '0px';
                    iframeModal.style.height = doc.documentElement.scrollHeight + 'px';
                }
            } catch (e) { /* si no se puede medir, se queda con su altura */ }
        }
        iframeModal.addEventListener('load', function () { setTimeout(ajustarAltura, 60); });
        document.getElementById('miModal').addEventListener('shown.bs.modal', ajustarAltura);

        // RECARGAR TABLA
        function cargarEntradas() {
            $("#tablaEntradas").load(BASE_URL + "tabla_entradas");
        }

        // ELIMINAR CON CONFIRMACIÓN
            $(document).on('click', '.eliminar-ent-btn', function() {
                var id = $(this).data('id');

                $.ajax({
                    url: BASE_URL + "verificar_entrada/" + id,
                    
                    method: "GET",
                    success: function(response) {
                        if (!response.puede_eliminar) {
                            alert("No se puede eliminar: " + response.mensaje);
                            return;
                        }

                        if (confirm("¿Estás seguro de que deseas eliminar esta entrada?")) {
                            $.ajax({
                                url: BASE_URL + "eliminare/" + id,
                               
                                method: "GET",
                                success: function() {
                                    alert("Entrada eliminada correctamente.");
                                    location.reload();
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log("ERROR:", xhr.status, xhr.responseText);
                        alert("Error al conectar con el servidor.");
                    }
                });
            });


//<!--- ------------------------BUSCADOR ----------------- -->

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


//-----HISTORIAL-----

// Inicializa el modal de Bootstrap vinculándolo al elemento HTML con id 'modalMermas'
let modalMermas = new bootstrap.Modal(document.getElementById('modalMermas'));

$(document).on('click', '.merma-btn', function () {// Escucha clic de 'merma-btn'

    // Extrae los datos almacenados en los atributos data-* del botón clickeado
    const id       = $(this).data('id');
    const producto = $(this).data('producto');
    const fecha    = $(this).data('fecha');

    // Título con contexto: producto y fecha de la entrada
    $('#tituloMermas').text('Mermas — ' + producto + ' (' + fecha + ')');
    modalMermas.show();


    // Hace una petición GET al servidor para obtener las mermas de la entrada seleccionada
    $.ajax({
        url: BASE_URL + "historial_mermas/" + id,
        method: "GET",
        success: function (response) {

            // Si el servidor responde sin éxito o no hay mermas, muestra mensaje y detiene la ejecución
            if (!response.success || response.mermas.length === 0) {
                $('#cuerpoMermas').html(
                    '<p class="text-muted text-center">Sin mermas registradas para esta entrada.</p>'
                );
                return;
            }

            // Construye la estructura base de la tabla con sus encabezados
            let html = `
                <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cantidad ( en unidades de compra)</th>
                        <th>Equivale a</th>   
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>`;

            // Recorre cada merma y agrega una fila a la tabla
            // ?? '—' muestra un guión si el valor es null o undefined
            response.mermas.forEach(function (m) {
                html += `
                    <tr>
                        <td>${m.fecha}</td>
                        <td>${m.cantidad} ${m.u_compra}</td>
                        <td>${m.cantidad_venta ?? '—'} ${m.u_venta ?? ''}</td>
                        <td>${m.notas ?? '—'}</td>
                    </tr>`;
                });

            html += `</tbody></table>`;

            // Inserta la tabla en el modal
            $('#cuerpoMermas').html(html);
        },
        //mensaje de error
        error: function () {
            $('#cuerpoMermas').html(
                '<p class="text-danger text-center">Error al cargar las mermas.</p>'
            );
        }
    });
});