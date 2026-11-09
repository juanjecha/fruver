/* FRUVER - login y registro: ver/ocultar contraseña, sugerir contraseña segura y checklist */
(function () {
    'use strict';

    // 1) Ojo para mostrar/ocultar contraseña
    document.querySelectorAll('[data-ver-pass]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.verPass);
            var mostrar = input.type === 'password';
            input.type = mostrar ? 'text' : 'password';
            btn.querySelector('i').className = mostrar ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });
    });

    // 2) Reglas de contraseña (las mismas que valida el servidor)
    var reglas = {
        largo: function (p) { return p.length >= 10; },
        min:   function (p) { return /\p{Ll}/u.test(p); },
        may:   function (p) { return /\p{Lu}/u.test(p); },
        num:   function (p) { return /\p{Nd}/u.test(p); },
        sim:   function (p) { return /[^\p{L}\p{N}\s]/u.test(p); }
    };

    var pass = document.getElementById('password');
    var lista = document.getElementById('reglas-pass');

    function revisar() {
        if (!pass || !lista) return;
        lista.querySelectorAll('li').forEach(function (li) {
            li.classList.toggle('ok', reglas[li.dataset.regla](pass.value));
        });
    }
    if (pass && lista) {
        pass.addEventListener('input', revisar);
        revisar();
    }

    // 3) Generador de contraseña segura (usa el generador aleatorio del navegador)
    function aleatorio(n) {
        var a = new Uint32Array(1);
        var limite = Math.floor(0x100000000 / n) * n; // evita sesgo
        do { crypto.getRandomValues(a); } while (a[0] >= limite);
        return a[0] % n;
    }

    function generar(largo) {
        var grupos = [
            'abcdefghijkmnpqrstuvwxyz',   // sin l, o (se confunden)
            'ABCDEFGHJKLMNPQRSTUVWXYZ',   // sin I, O
            '23456789',                   // sin 0, 1
            '!@#$%^&*?-_+='
        ];
        var todos = grupos.join('');
        var chars = grupos.map(function (g) { return g[aleatorio(g.length)]; }); // al menos uno de cada grupo
        while (chars.length < largo) chars.push(todos[aleatorio(todos.length)]);
        for (var i = chars.length - 1; i > 0; i--) {                              // mezcla
            var j = aleatorio(i + 1);
            var t = chars[i]; chars[i] = chars[j]; chars[j] = t;
        }
        return chars.join('');
    }

    var btnSugerir = document.getElementById('btn-sugerir');
    if (btnSugerir && pass) {
        btnSugerir.addEventListener('click', function () {
            pass.value = generar(14);
            pass.type = 'text'; // se muestra para que el usuario pueda copiarla/guardarla
            var ojo = document.querySelector('[data-ver-pass="password"] i');
            if (ojo) ojo.className = 'fa-regular fa-eye-slash';
            revisar();
            pass.focus();
        });
    }
})();
