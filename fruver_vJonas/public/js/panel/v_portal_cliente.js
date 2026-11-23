/**
 * Portal del cliente.
 *  1) Pestañas (Por entregar / Entregados · Deudas / Pagados). La pestaña activa se guarda en
 *     el #hash de la URL, así al recargar o volver atrás se conserva.
 *  2) Botón "Detalles": pide el pedido a "detalle_pedido" (el servidor solo lo entrega si es del cliente).
 */
(function () {
    'use strict';

    // ───────────── 1) Pestañas ─────────────
    var tabs    = Array.prototype.slice.call(document.querySelectorAll('.cli-tab'));
    var paneles = Array.prototype.slice.call(document.querySelectorAll('.cli-panel'));

    function activar(tab, guardar) {
        tabs.forEach(function (t) {
            var on = t === tab;
            t.classList.toggle('activa', on);
            t.setAttribute('aria-selected', String(on));
        });
        paneles.forEach(function (p) { p.hidden = p.id !== tab.dataset.panel; });

        if (guardar && window.history.replaceState) {
            window.history.replaceState(null, '', '#' + tab.dataset.hash);
        }
    }

    if (tabs.length) {
        tabs.forEach(function (t) {
            t.addEventListener('click', function () { activar(t, true); });
        });
        var hash = window.location.hash.replace('#', '');
        activar(tabs.filter(function (t) { return t.dataset.hash === hash; })[0] || tabs[0], false);
    }

    // ───────────── 2) Detalle del pedido ─────────────
    var modalEl = document.getElementById('modalDetalle');
    if (!modalEl) { return; }

    var modal  = new bootstrap.Modal(modalEl);
    var cuerpo = document.getElementById('modalDetalleBody');
    var titulo = document.getElementById('detalle_id');

    var COLORES = { confirmado: 'primary', 'en transito': 'warning', entregado: 'success',
                    pagado: 'dark', cancelado: 'danger', credito: 'info' };
    var TEXTOS  = { solicitado: 'Solicitado', confirmado: 'Confirmado', 'en transito': 'En tránsito',
                    entregado: 'Entregado', pagado: 'Pagado', credito: 'A crédito', cancelado: 'Cancelado' };

    function esc(v) {   // los textos vienen de la base de datos: se escapan antes de pintarlos
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function dinero(n)  { return '$' + (parseFloat(n) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function cant(n)    { return String(parseFloat(n)); }
    function badge(est) {
        var e = est || 'solicitado';
        return '<span class="badge bg-' + (COLORES[e] || 'secondary') + '">' + esc(TEXTOS[e] || e) + '</span>';
    }

    function pintar(data) {
        var p = data.pedido || {};
        var total = 0;

        var filas = (data.productos || []).map(function (pr) {
            total += parseFloat(pr.total || 0);
            return '<tr><td>' + esc(pr.nombre) + '</td><td>' + esc(cant(pr.cantidad)) + '</td><td>' + esc(pr.unidad_venta) +
                   '</td><td>' + dinero(pr.precio_venta) + '</td><td>' + dinero(pr.total) + '</td></tr>';
        }).join('');

        var hist = (data.historial || []).map(function (h) {
            return '<tr><td>' + badge(h.estado) + '</td><td>' + esc(h.fecha) + '</td></tr>';
        }).join('') || '<tr><td colspan="2" class="text-center text-muted">Sin historial</td></tr>';

        cuerpo.innerHTML =
            '<div class="row mb-3">' +
              '<div class="col-sm-6">' +
                '<p class="mb-1"><strong>Fecha del pedido:</strong> ' + esc(p.fecha) + '</p>' +
                '<p class="mb-1"><strong>Fecha de entrega:</strong> ' + esc(p.fecha_entrega || 'Sin fecha') + '</p>' +
                '<p class="mb-1"><strong>Dirección:</strong> ' + esc(p.direccion_pedido || 'No especificada') + '</p>' +
              '</div>' +
              '<div class="col-sm-6">' +
                '<p class="mb-1"><strong>Repartidor:</strong> ' + esc(p.repartidor || 'No asignado') + '</p>' +
                '<p class="mb-1"><strong>Método de pago:</strong> ' + esc(p.pago === 'credito' ? 'Crédito' : (p.pago ? 'Contado' : 'No definido')) + '</p>' +
                '<p class="mb-1"><strong>Estatus:</strong> ' + badge(p.status) + '</p>' +
              '</div>' +
            '</div><hr>' +
            '<h6 class="fw-bold">Productos</h6>' +
            '<div class="table-responsive"><table class="table table-bordered table-sm">' +
              '<thead><tr><th>Producto</th><th>Cantidad</th><th>Unidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead>' +
              '<tbody>' + filas + '</tbody>' +
              '<tfoot><tr><td colspan="4" class="text-end fw-bold">Total:</td><td class="fw-bold text-success">' + dinero(total) + '</td></tr></tfoot>' +
            '</table></div><hr>' +
            '<h6 class="fw-bold">Historial de estatus</h6>' +
            '<div class="table-responsive"><table class="table table-bordered table-sm">' +
              '<thead><tr><th>Estatus</th><th>Fecha y hora</th></tr></thead><tbody>' + hist + '</tbody>' +
            '</table></div>';
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btnVerDetalle');
        if (!btn) { return; }

        titulo.textContent = '#' + btn.dataset.id;
        cuerpo.innerHTML = '<p class="text-center">Cargando...</p>';
        modal.show();

        fetch(modalEl.dataset.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: 'id_pedido=' + encodeURIComponent(btn.dataset.id),
            credentials: 'same-origin'
        })
        .then(function (r) { return r.text(); })
        .then(function (txt) {
            var data = JSON.parse(txt);
            if (data.error) { cuerpo.innerHTML = '<p class="text-danger">' + esc(data.error) + '</p>'; return; }
            pintar(data);
        })
        .catch(function () {
            cuerpo.innerHTML = '<p class="text-danger">Error al cargar el detalle. Vuelve a intentarlo.</p>';
        });
    });
})();
