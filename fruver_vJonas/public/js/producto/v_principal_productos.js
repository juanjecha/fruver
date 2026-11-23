
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

    $(document).on('click', '.eliminar-prod-btn', function() {
        var id = $(this).data('id');//lee el atributo del boton clikeado

        $.ajax({
            url: BASE_URL + "verificar_producto/" + id,
 
            method: "GET",
            success: function(response) {
                if (!response.puede_eliminar) {//respnse.puede_eliminar, es propiedade el obj JSON retornado en el servidor,
                    //el controlador revisa si esa el producto ligado a pedidos 
                    alert("No se puede eliminar: " + response.mensaje);
                    return;
                }

                if (confirm("¿Estás seguro de que deseas eliminar este producto?")) {
                    $.ajax({
                        url: BASE_URL + "eliminar/" + id,
                        
                        method: "GET",
                        success: function() {
                            alert("Producto eliminado correctamente.");
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

// ─--------------------── HISTORIAL ──────────────────────────────────

//Inicializa el modal de Bootstrap
let modalHistorial = new bootstrap.Modal(document.getElementById('modalHistorial'));

//escucha el click de class="historial-btn",
$(document).on('click', '.historial-btn', function () {

    //Lee los atributos data-id y data-nombre del botón clickeado.
    const id     = $(this).data('id');
    const nombre = $(this).data('nombre');

    //Actualiza el título del modal con el nombre del producto
    $('#tituloHistorial').text('Historial de entradas: ' + nombre);

    //Abre el modal 
    modalHistorial.show();

    $.ajax({
        url: BASE_URL + "historial_entradas/" + id,
        method: "GET",

        //Se ejecuta si el servidor respondió correctamente, manda JSON devuelto por el controlador
        success: function (response) {
            if (!response.success || response.entradas.length === 0) { //Verifica si la respuesta indica éxito y si hay entradas.
                $('#cuerpoHistorial').html(
                    '<p class="text-muted text-center">Sin entradas registradas.</p>'
                );
                return;
            }

            //Construye la tabla HTML con las entradas recibidas.
            let html = `
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cantidad</th>
                            <th>U. Compra</th>
                            <th>U. Venta</th>
                            <th>Precio compra</th>
                            <th>Caducidad</th>
                        </tr>
                    </thead>
                    <tbody>`;


            //Itera cada entrada del arreglo y genera una fila <tr> por cada una
            response.entradas.forEach(function (e) {
                html += `
                    <tr>
                        <td>${e.fecha}</td>
                        <td>${e.cantidad}</td>
                        <td>${e.u_compra}</td>
                        <td>${e.u_venta}</td>
                        <td>$${e.precio_compra}</td>
                        <td>${e.fecha_caducidad}</td>
                    </tr>`;
            });

            html += `</tbody></table>`;

            $('#cuerpoHistorial').html(html); //Inserta la tabla construida dentro del modal
        },
        error: function () { //Se ejecuta si hubo un error de red o el servidor devolvió un error
            $('#cuerpoHistorial').html(
                '<p class="text-danger text-center">Error al cargar el historial.</p>'
            );
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
