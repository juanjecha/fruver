/**
 * Menú lateral: se abre/cierra SOLO al pulsar las 3 líneas.
 *  - Computadora y tablet (>= 768 px): el estado se recuerda en localStorage y se conserva al cambiar de pantalla.
 *  - Teléfono (< 768 px): el menú es un cajón que se superpone; arranca cerrado en cada pantalla.
 * El estado vive en la clase "fv-menu-abierto" del <html> (la pone un script corto en el <head> de v_base).
 */
(function () {
    'use strict';

    var raiz        = document.documentElement;
    var boton       = document.getElementById('fvMenuToggle');
    var abrirMovil  = document.getElementById('fvAbrirMovil');
    var fondo       = document.getElementById('fvMenuFondo');
    var CLAVE       = 'fv_menu';
    var movil       = window.matchMedia('(max-width: 767.98px)');

    function estaAbierto() {
        return raiz.classList.contains('fv-menu-abierto');
    }

    function poner(abierto, guardar) {
        raiz.classList.toggle('fv-menu-abierto', abierto);
        if (boton) { boton.setAttribute('aria-expanded', String(abierto)); }
        if (guardar && !movil.matches) {
            try { localStorage.setItem(CLAVE, abierto ? 'abierto' : 'cerrado'); } catch (e) { /* sin almacenamiento */ }
        }
    }

    function alternar() { poner(!estaAbierto(), true); }

    if (boton)      { boton.addEventListener('click', alternar); }
    if (abrirMovil) { abrirMovil.addEventListener('click', alternar); }
    if (fondo)      { fondo.addEventListener('click', function () { poner(false, false); }); }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && movil.matches && estaAbierto()) { poner(false, false); }
    });

    // Si cambia el tamaño de pantalla (girar el teléfono, redimensionar) se aplica el estado que corresponde
    if (movil.addEventListener) {
        movil.addEventListener('change', function () {
            var guardado = null;
            try { guardado = localStorage.getItem(CLAVE); } catch (e) { /* sin almacenamiento */ }
            poner(movil.matches ? false : guardado !== 'cerrado', false);
        });
    }

    poner(estaAbierto(), false);

    // Activa las animaciones hasta que la página ya pintó (así no se "desliza" al cargar)
    window.requestAnimationFrame(function () { raiz.classList.add('fv-listo'); });
})();
