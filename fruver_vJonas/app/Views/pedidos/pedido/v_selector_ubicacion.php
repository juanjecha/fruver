<?php
$ubicacionActual = trim((string) ($ubicacionActual ?? ''));
$puntoActual = ubicacion_parsear($ubicacionActual);
$textoAntiguo = ($ubicacionActual !== '' && $puntoActual === null) ? $ubicacionActual : null;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">

<div class="mb-3" id="selectorUbicacion">
    <label class="form-label fw-semibold">Ubicación de entrega *</label>

    <?php if ($textoAntiguo !== null): ?>
        <div class="alert alert-warning py-2 small mb-2">
             <strong><?= esc($textoAntiguo) ?></strong>.<br>
             Elige la ubicación real en el mapa.
        </div>
    <?php endif; ?>

    <div id="mapaUbicacion" class="border rounded" style="height: 300px;"></div>
    <input type="hidden" name="direccion" id="direccion"
        value="<?= $puntoActual ? esc(ubicacion_formatear($puntoActual['lat'], $puntoActual['lng']), 'attr') : '' ?>">
    <div class="form-text" id="textoUbicacion">Haz click en el mapa para marcar el punto de entrega</div>
    
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>

<script>
(function () {
    //Sin coordenadas se abre en el centro de puebla
    var centroInicial = [19.0414, -98.2063];
    var puntoActual = <?= $puntoActual ? json_encode([$puntoActual['lat'], $puntoActual['lng']]) : 'null' ?>;

    var campo = document.getElementById('direccion');
    var texto = document.getElementById('textoUbicacion');
    var mapa = L.map('mapaUbicacion').setView(puntoActual || centroInicial, puntoActual ? 16 : 13);
    var marcador = null;

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(mapa);

    function fijarPunto(lat, lng) {
        var valor = lat.toFixed(6) + ',' + lng.toFixed(6);
        campo.value = valor;
        texto.textContent = 'Ubicación seleccionada: ' + valor;

        if (marcador === null) {
            marcador = L.marker([lat, lng], {draggable: true}).addTo(mapa);
            marcador.on('dragend', function () {
                var p = marcador.getLatLng();
                fijarPunto(p.lat, p.lng);
            });
        } else {
            marcador.setLatLng([lat, lng]);
        }
    }

    mapa.on('click', function (e) { fijarPunto(e.latlng.lat, e.latlng.lng); });

    if (puntoActual) { fijarPunto(puntoActual[0], puntoActual[1]); }

    //creación del mapa
    setTimeout(function () { mapa.invalidateSize(); }, 300);

    //validación para que no se envíe sin ubicación
    var formulario = campo.closest('form');
    if (formulario) {
        formulario.addEventListener('submit', function (e) {
            if (campo.value === '') {
                e.preventDefault();
                e.stopImmediatePropagation();
                alert('Selecciona la ubicación de entrega');
                document.getElementById('selectorUbicacion').scrollIntoView({behavior: 'smooth', block: 'center'});
            }
        }, true);
    }
})();
</script>