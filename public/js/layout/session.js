(function () {
    'use strict';

    var BASE  = window.FV_BASE_URL || '';
    var LOGIN = BASE + 'login?aviso=expirada';
    var yaFue = false;

    function irAlLogin() {
        if (yaFue) { return; }
        yaFue = true;
        window.top.location.replace(LOGIN);
    }

    function revisar() {
        fetch(BASE + 'sesion/estado', { credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (d) { if (!d.vigente) { irAlLogin(); } })
            .catch(function () { /* sin red: no se hace nada */ });
    }

    setInterval(revisar, 30000);

    // Los navegadores frenan los temporizadores en pestañas ocultas: al volver, se revisa de inmediato
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { revisar(); } });
    window.addEventListener('pageshow', function (e) { if (e.persisted) { revisar(); } });

    // Cualquier AJAX que reciba 401 (paso 2) también te saca
    if (window.jQuery) {
        jQuery(document).ajaxError(function (ev, xhr) { if (xhr.status === 401) { irAlLogin(); } });
    }
})();