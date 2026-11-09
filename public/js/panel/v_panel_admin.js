// Abre y cierra los submenús de las tarjetas (Inventario y Pedidos).
// Solo puede haber uno abierto a la vez; se cierran al hacer clic fuera o con Esc.
document.addEventListener('DOMContentLoaded', () => {
    const botones = document.querySelectorAll('.tarjeta-grupo > button.tarjeta');

    // Cierra todos los submenús, menos el del botón que se indique
    function cerrarTodos(excepto = null) {
        botones.forEach(btn => {
            if (btn === excepto) return;
            btn.setAttribute('aria-expanded', 'false');
            document.getElementById(btn.getAttribute('aria-controls')).hidden = true;
        });
    }

    botones.forEach(btn => {
        btn.addEventListener('click', () => {
            const menu    = document.getElementById(btn.getAttribute('aria-controls'));
            const abierto = btn.getAttribute('aria-expanded') === 'true';

            cerrarTodos(btn);
            btn.setAttribute('aria-expanded', String(!abierto));
            menu.hidden = abierto;
        });
    });

    // Clic fuera de cualquier tarjeta con submenú: cierra todo
    document.addEventListener('click', e => {
        if (!e.target.closest('.tarjeta-grupo')) cerrarTodos();
    });

    // Tecla Esc: cierra todo
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') cerrarTodos();
    });
});