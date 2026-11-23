<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
    <style>
        #mapaRecorrido { height: 520px; width: 100%; }
        .punto-color { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 6px; }
    </style>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="fv-pagina" id="recorrido">

    <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h3 id="titulo">Recorrido del día</h3>
            <small>Árbol de expansión mínima (algoritmo de Prim) de las entregas de hoy · distancias en línea recta</small>
        </div>
        <button type="button" class="btn btn-outline-secondary" id="btnActualizar">
            <i class="fa-solid fa-rotate me-1"></i> Actualizar
        </button>
    </div>

    <div id="mensajeRecorrido"></div>
    <div id="panelControles" class="mb-3"></div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card p-2"><div id="mapaRecorrido" class="rounded"></div></div>
        </div>
        <div class="col-lg-4" id="listaRepartidores"></div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script>
(function () {
    var URL_ARBOLES = "<?= base_url('recorrido_arboles') ?>";
    var URL_INICIAR = "<?= base_url('recorrido_iniciar') ?>";
    var COLORES = ['#e6194b', '#3cb44b', '#4363d8', '#f58231', '#911eb4', '#008080', '#f032e6', '#9a6324'];

    var mapa = L.map('mapaRecorrido').setView([19.0414, -98.2063], 12);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(mapa);

    mapa.attributionControl.addAttribution('Rutas © <a href="https://openrouteservice.org">openrouteservice</a>');

    var capa = L.layerGroup().addTo(mapa);            // puntos y aristas de los árboles (se borra y redibuja)
    var capaSeleccion = L.layerGroup().addTo(mapa);   // marcador temporal al elegir la salida
    var datos = null;
    var seleccionando = false;
    var puntoElegido = null;
    var marcadorSalida = null;
    var vehiculos = {};   // vehículo elegido por repartidor (id_repartidor -> 'moto' | 'carro')

    // ---------- utilidades ----------
    function esc(t) {   // los nombres de clientes vienen de la BD: se escapan antes de ponerlos en HTML
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
        });
    }
    function km(m) { return m >= 1000 ? (m / 1000).toFixed(1) + ' km' : Math.round(m) + ' m'; }
    function avisar(texto, tipo) {
        document.getElementById('mensajeRecorrido').innerHTML = texto
            ? '<div class="alert alert-' + (tipo || 'danger') + '">' + esc(texto) + '</div>' : '';
    }
    function miGrupo() {
        return datos.grupos.filter(function (g) { return g.id_repartidor === datos.mi_repartidor; })[0] || null;
    }

    // ---------- pedir datos ----------
    function cargar(ajustarMapa) {
        return fetch(URL_ARBOLES, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) { throw new Error('No se pudieron cargar los recorridos (error ' + r.status + ').'); }
                return r.json();
            })
            .then(function (d) { datos = d; avisar(''); dibujar(ajustarMapa); })
            .catch(function (e) { avisar(e.message); });
    }

    // ---------- dibujar mapa ----------
    function dibujar(ajustarMapa) {
        capa.clearLayers();
        var limites = [];

        datos.grupos.forEach(function (g, i) {
            var color = COLORES[i % COLORES.length];

            // aristas del árbol: cada una une dos nodos (el nodo 0 es la salida)
            if (g.arbol) {
                g.arbol.aristas.forEach(function (a) {
                    var A = g.arbol.nodos[a.desde], B = g.arbol.nodos[a.hasta];
                    var puntos = a.trazo || [[A.lat, A.lng], [B.lat, B.lng]];
                    L.polyline(puntos, { color: color, weight: 4, opacity: 0.85, dashArray: a.trazo ? null : '6 6' })
                        .bindTooltip(km(a.metros_calle || a.metros) + (a.trazo ? ' por calle' : ' en línea recta'))
                        .addTo(capa);
                });
            }

            g.paradas.forEach(function (p) {
                L.circleMarker([p.lat, p.lng], { radius: 7, color: '#ffffff', weight: 2, fillColor: color, fillOpacity: 1 })
                    .bindPopup('<strong>Pedido #' + p.id_pedido + '</strong><br>' + esc(p.cliente) +
                               '<br><small>' + esc(p.status) + ' · ' + esc(g.repartidor) + '</small>')
                    .addTo(capa);
                limites.push([p.lat, p.lng]);
            });

            if (g.salida) {
                /*
                    Salida predeterminada: contorno punteado
                    Salida elegida: contorno sólido
                */
                var porDefecto = !!g.salida_por_defecto;

                L.circleMarker([g.salida.lat, g.salida.lng], { radius: 11, color: color, weight: 4, dashArray: porDefecto ? '4 4' : null, fillColor: '#ffffff', fillOpacity: 1 })
                    .bindTooltip((porDefecto ? 'Salida predeterminada' : 'Salida') + ' . ' + esc(g.repartidor)).addTo(capa);
                limites.push([g.salida.lat, g.salida.lng]);
            }
        });

        if (ajustarMapa && limites.length && !seleccionando) {
            mapa.fitBounds(limites, { padding: [40, 40], maxZoom: 16 });
        }
        pintarLista();
        pintarControles();
    }

    // ---------- combustible ----------
    function vehiculoDe(id) {
        if (vehiculos[id]) { return vehiculos[id]; }
        var v = null;
        try { v = localStorage.getItem('fruver_vehiculo_' + id); } catch (e) { v = null; }
        if (!v || !datos.combustible.rendimiento[v]) { v = datos.combustible.vehiculo_defecto; }
        vehiculos[id] = v;
        return v;
    }
    function calcularCombustible(g) {
        var c = datos.combustible;
        var metros = g.arbol.costo_metros_calles != null
        ? g.arbol.costo_metros_calles
        : g.arbol.costo_metros * c.factor_calles;
        var vehiculo = vehiculoDe(g.id_repartidor);
        var metros = g.arbol.costo_metros * c.factor_calles;
        var litros = (metros / 1000) / c.rendimiento[vehiculo];       // combustible = distancia / rendimiento
        var nivel = litros <= c.litros_bajo ? 'bajo' : (litros <= c.litros_alto ? 'medio' : 'alto');
        return { vehiculo: vehiculo, metros: metros, litros: litros, costo: litros * c.precio_litro, nivel: nivel };
    }
    function bloqueCombustible(g) {
        var r = calcularCombustible(g);
        var clase = { bajo: 'bg-success', medio: 'bg-warning text-dark', alto: 'bg-danger' }[r.nivel];
        return '<div class="mt-2 pt-2 border-top small">' +
               '<div class="d-flex align-items-center justify-content-between gap-2 mb-1">' +
               '<span class="fw-semibold"><i class="fa-solid fa-gas-pump me-1"></i>Gasolina estimada</span>' +
               '<select class="form-select form-select-sm w-auto" data-vehiculo="' + g.id_repartidor + '" aria-label="Tipo de vehículo">' +
               '<option value="moto"' + (r.vehiculo === 'moto' ? ' selected' : '') + '>Moto</option>' +
               '<option value="carro"' + (r.vehiculo === 'carro' ? ' selected' : '') + '>Carro</option>' +
               '</select></div>' +
               '<div>Distancia de la ruta: ≈ ' + km(r.metros) + '</div>' +
               '<div>Combustible: ≈ ' + r.litros.toFixed(2) + ' L · Costo: ≈ $' + r.costo.toFixed(2) + '</div>' +
               '<span class="badge ' + clase + ' mt-1">Consumo ' + r.nivel + '</span>' +
               '</div>';
    }

    // ---------- lista lateral ----------
    function pintarLista() {
        var html = '';
        if (!datos.grupos.length) {
            html += '<div class="card p-3 small">No hay pedidos pendientes con repartidor asignado para hoy.</div>';
        }
        datos.grupos.forEach(function (g, i) {
            var color = COLORES[i % COLORES.length];

            var etiquetaSalida = g.salida_por_defecto ? ' . <span class="badge bg-secondary">salida predeterminada</span>' : '';
            var estado = g.arbol
                ? 'Árbol: ' + km(g.arbol.costo_metros) + ' · ' + g.arbol.aristas.length + ' conexión(es)' + etiquetaSalida
                : (g.salida ? 'Sin pedidos con ubicación' + etiquetaSalida : '<span class="badge bg-secondary">Sin punto de salida</span>');

            html += '<div class="card p-3 mb-2">' +
                    '<div class="fw-semibold"><span class="punto-color" style="background:' + color + '"></span>' + esc(g.repartidor) + '</div>' +
                    '<div class="small">' + g.paradas.length + ' pedido(s) en el mapa · ' + estado + '</div>';
            if (g.arbol && datos.combustible) { html += bloqueCombustible(g); }
            if (g.sin_ubicacion.length) {
                html += '<div class="small mt-1 text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>' +
                        'Sin ubicación (no entran al árbol): ' +
                        g.sin_ubicacion.map(function (p) { return '#' + p.id_pedido + ' ' + esc(p.cliente); }).join(', ') + '</div>';
            }
            html += '</div>';
        });
        if (datos.sin_repartidor > 0) {
            html += '<div class="small mt-2">' + datos.sin_repartidor + ' pedido(s) de hoy aún sin repartidor asignado.</div>';
        }
        document.getElementById('listaRepartidores').innerHTML = html;
    }

    // ---------- panel del repartidor ----------
    function pintarControles() {
        var panel = document.getElementById('panelControles');
        if (!datos.puede_iniciar) { panel.innerHTML = ''; return; }   // administrador / vendedor: solo miran

        var g = miGrupo();
        if (!g) {
            panel.innerHTML = '<div class="alert alert-info mb-0">No tienes pedidos pendientes asignados para hoy, así que no hay recorrido que comenzar.</div>';
            return;
        }

        if (seleccionando) {
            panel.innerHTML =
                '<div class="card p-3">' +
                '<div class="fw-semibold mb-1">Elige tu punto de salida</div>' +
                '<div class="small mb-2">Haz clic en el mapa (puedes arrastrar el marcador) o usa tu ubicación actual.</div>' +
                '<div class="d-flex flex-wrap gap-2">' +
                '<button type="button" class="btn btn-outline-primary" data-accion="ubicacion"><i class="fa-solid fa-location-crosshairs me-1"></i> Usar mi ubicación</button>' +
                '<button type="button" class="btn btn-success" data-accion="confirmar"' + (puntoElegido ? '' : ' disabled') + '>Confirmar punto de salida</button>' +
                '<button type="button" class="btn btn-outline-secondary" data-accion="cancelar">Cancelar</button>' +
                '</div><div class="small mt-2" id="textoSalida">' +
                (puntoElegido ? 'Salida elegida: ' + puntoElegido.lat.toFixed(6) + ', ' + puntoElegido.lng.toFixed(6) : '') +
                '</div></div>';
            return;
        }

        if (g.salida && !g.salida_por_defecto) {
            var resumen = g.paradas.length + ' pedido(s) con ubicación' + (g.arbol ? ' · árbol de ' + km(g.arbol.costo_metros) : '');
            panel.innerHTML =
                '<div class="card p-3 d-flex flex-row flex-wrap align-items-center justify-content-between gap-2">' +
                '<div><div class="fw-semibold"><i class="fa-solid fa-circle-check text-success me-1"></i> Recorrido iniciado</div>' +
                '<div class="small">' + resumen + '</div></div>' +
                '<button type="button" class="btn btn-outline-primary" data-accion="comenzar">Cambiar punto de salida</button></div>';
        } else {
            panel.innerHTML =
                '<div class="card p-3 d-flex flex-row flex-wrap align-items-center justify-content-between gap-2">' +
                    '<div>' +
                        '<div class="fw-semibold">Aún no eliges punto de salida</div>' +
                        '<div class="small">' +
                            (
                                g.salida
                                    ? 'Sales de este punto (' +
                                    (datos.salida_defecto ? esc(datos.salida_defecto.nombre) : '') +
                                    ') este es tu recorrido con ' +
                                    g.paradas.length +
                                    ' pedido(s). Si sales de otro lugar, elígelo.'
                                    : 'Elige desde dónde saldrás'
                            ) +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-success" data-accion="comenzar">' +
                        '<i class="fa-solid fa-route me-1"></i> Comenzar recorrido del día' +
                    '</button>' +
                '</div>';
        }
    }

    // ---------- elegir la salida ----------
    function elegir(lat, lng) {
        puntoElegido = { lat: lat, lng: lng };
        if (marcadorSalida === null) {
            marcadorSalida = L.marker([lat, lng], { draggable: true }).addTo(capaSeleccion);
            marcadorSalida.on('dragend', function () {
                var p = marcadorSalida.getLatLng();
                elegir(p.lat, p.lng);
            });
        } else {
            marcadorSalida.setLatLng([lat, lng]);
        }
        var boton = document.querySelector('#panelControles [data-accion="confirmar"]');
        if (boton) { boton.disabled = false; }
        var t = document.getElementById('textoSalida');
        if (t) { t.textContent = 'Salida elegida: ' + lat.toFixed(6) + ', ' + lng.toFixed(6); }
    }

    function terminarSeleccion() {
        seleccionando = false;
        puntoElegido = null;
        marcadorSalida = null;
        capaSeleccion.clearLayers();
    }

    mapa.on('click', function (e) { if (seleccionando) { elegir(e.latlng.lat, e.latlng.lng); } });

    document.getElementById('panelControles').addEventListener('click', function (e) {
        var boton = e.target.closest('[data-accion]');
        if (!boton) { return; }

        switch (boton.dataset.accion) {
            case 'comenzar':
                seleccionando = true;
                puntoElegido = null;
                marcadorSalida = null;
                capaSeleccion.clearLayers();
                var g = miGrupo();
                if (g && g.salida && !g.salida_por_defecto) { elegir(g.salida.lat, g.salida.lng); }   // propone la salida actual
                pintarControles();
                break;

            case 'cancelar':
                terminarSeleccion();
                pintarControles();
                break;

            case 'ubicacion':
                if (!navigator.geolocation) { avisar('Tu navegador no permite obtener la ubicación.'); break; }
                navigator.geolocation.getCurrentPosition(function (pos) {
                    elegir(pos.coords.latitude, pos.coords.longitude);
                    mapa.setView([pos.coords.latitude, pos.coords.longitude], 16);
                }, function () {
                    avisar('No se pudo obtener tu ubicación. Haz clic en el mapa para elegirla.');
                }, { enableHighAccuracy: true, timeout: 10000 });
                break;

            case 'confirmar':
                if (!puntoElegido) { break; }
                var valor = puntoElegido.lat.toFixed(6) + ',' + puntoElegido.lng.toFixed(6);
                fetch(URL_INICIAR, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                    credentials: 'same-origin',
                    body: new URLSearchParams({ salida: valor }).toString()
                })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, json: j }; }); })
                .then(function (res) {
                    if (!res.ok || !res.json.ok) { avisar(res.json.error || 'No se pudo guardar el punto de salida.'); return; }
                    terminarSeleccion();
                    avisar('');
                    cargar(true);
                })
                .catch(function () { avisar('No se pudo guardar el punto de salida.'); });
                break;
        }
    });

    // Cambiar moto/carro: solo recalcula, no vuelve a pedir datos al servidor.
    document.getElementById('listaRepartidores').addEventListener('change', function (e) {
        var sel = e.target.closest('[data-vehiculo]');
        if (!sel) { return; }
        vehiculos[sel.dataset.vehiculo] = sel.value;
        try { localStorage.setItem('fruver_vehiculo_' + sel.dataset.vehiculo, sel.value); } catch (err) { /* sin almacenamiento: queda solo en esta sesión */ }
        pintarLista();
    });

    document.getElementById('btnActualizar').addEventListener('click', function () { cargar(true); });

    // El mapa se crea antes de tener su tamaño final: se recalcula.
    setTimeout(function () { mapa.invalidateSize(); }, 300);

    cargar(true);
    // Se refresca solo cada minuto (sin mover el mapa), salvo mientras el repartidor elige su salida.
    setInterval(function () { if (!seleccionando) { cargar(false); } }, 60000);
})();
</script>
<?= $this->endSection() ?>