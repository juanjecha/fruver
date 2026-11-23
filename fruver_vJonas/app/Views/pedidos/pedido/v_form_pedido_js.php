<?php
/**
 * JS del formulario del pedido (el contenido del <script>, sin las etiquetas).
 * Quien lo incluya debe envolverlo en <script> y cargarlo DESPUÉS de jQuery.
 * Necesita: $productos
 */
?>
// Datos desde PHP
const productos = <?= json_encode($productos) ?>;
const precarga  = <?= json_encode($precarga ?? null) ?>; // null salvo al reutilizar un pedido
const unidades  = ['caja','mazo','arpilla','kilos','ramos'];
let filaIndex   = 0;

// Al cargar: si se reutiliza un pedido se dibujan sus productos; si no, una fila vacía
if (precarga && precarga.productos.length) {
    precarga.productos.forEach(p => agregarFila(p));
    document.getElementById('totalGeneral').value = Number(precarga.total_general).toFixed(2);
} else {
    agregarFila();
}

document.getElementById('btnAgregarProducto').addEventListener('click', () => agregarFila());

// "Recalcular": cada total = cantidad × precio y vuelve a sumar el total final
document.getElementById('btnCalcular').addEventListener('click', function() {
    document.querySelectorAll('#productosBody tr').forEach(recalcularFila);
    recalcularTotal();
});

// Cálculo en vivo (delegación: sirve también para filas agregadas después)
//  - cambia cantidad o precio -> recalcula el total de esa fila y el final
//  - edita el total de un producto a mano -> solo recalcula el final
document.getElementById('productosBody').addEventListener('input', function(e) {
    if (e.target.matches('.inp-cantidad, .inp-precio')) {
        recalcularFila(e.target.closest('tr'));
        recalcularTotal();
        if (e.target.matches('.inp-cantidad')) validarExistencias();
    } else if (e.target.matches('.inp-subtotal')) {
        recalcularTotal();
    }
});

// Al elegir otro producto: muestra su disponible y revisa de nuevo las cantidades
document.getElementById('productosBody').addEventListener('change', function(e) {
    if (e.target.matches('.sel-producto')) {
        mostrarDisponible(e.target.closest('tr'));
        validarExistencias();
    }
});

//--------------------------EXISTENCIAS DISPONIBLES--------------------
function fmtNum(n) {
    return Number(n).toLocaleString('es-MX', { maximumFractionDigits: 2 });
}

function productoPorId(id) {
    return productos.find(p => Number(p.id_producto) === Number(id)) || null;
}

// Texto "Disponible: 271 kilos" debajo del selector de producto
function mostrarDisponible(fila) {
    const info = fila.querySelector('.disp-info');
    const prod = productoPorId(fila.querySelector('.sel-producto').value);

    info.classList.remove('text-danger');
    if (!prod) {
        info.textContent = '';
        info.classList.remove('mt-1');
        return;
    }

    const disp = Number(prod.disponible) || 0;
    info.classList.add('mt-1');
    if (disp <= 0) {
        info.textContent = 'Sin existencias disponibles';
        info.classList.add('text-danger');
    } else {
        info.textContent = 'Disponible: ' + fmtNum(disp) + (prod.u_venta ? ' ' + prod.u_venta : '');
    }
}

/*
  Revisa que lo pedido no supere lo disponible.
  Suma las cantidades por producto: si el mismo producto está en 2 filas,
  entre las dos no pueden pasarse. Marca en rojo las filas con problema.
  Devuelve true si todo está bien.
*/
function validarExistencias() {
    const filas = [...document.querySelectorAll('#productosBody tr')];

    // 1) total pedido por producto
    const pedido = {};
    filas.forEach(fila => {
        const id = fila.querySelector('.sel-producto').value;
        if (!id) return;
        pedido[id] = (pedido[id] || 0) + (parseFloat(fila.querySelector('.inp-cantidad').value) || 0);
    });

    // 2) marcar filas
    let ok = true;
    filas.forEach(fila => {
        const inp  = fila.querySelector('.inp-cantidad');
        const msg  = fila.querySelector('.invalid-feedback');
        const id   = fila.querySelector('.sel-producto').value;
        const prod = id ? productoPorId(id) : null;

        let error = '';
        if (prod) {
            const disp   = Number(prod.disponible) || 0;
            const pedida = pedido[id];
            if (pedida > 0 && Math.round(pedida * 100) > Math.round(disp * 100)) {
                const repetido = filas.filter(f => f.querySelector('.sel-producto').value === id).length > 1;
                if (disp <= 0)      error = 'Sin existencias de este producto.';
                else if (repetido)  error = 'Entre todas las filas de este producto superan lo disponible (' + fmtNum(disp) + ').';
                else                error = 'Máximo disponible: ' + fmtNum(disp) + '.';
            }
        }

        inp.classList.toggle('is-invalid', error !== '');
        msg.textContent = error;
        if (error) ok = false;
    });

    return ok;
}

function recalcularFila(fila) {
    const cantidad = parseFloat(fila.querySelector('.inp-cantidad').value) || 0;
    const precio   = parseFloat(fila.querySelector('.inp-precio').value)   || 0;
    fila.querySelector('.inp-subtotal').value = (cantidad * precio).toFixed(2);
}

function recalcularTotal() {
    let total = 0;
    document.querySelectorAll('#productosBody .inp-subtotal').forEach(inp => {
        total += parseFloat(inp.value) || 0;
    });
    document.getElementById('totalGeneral').value = total.toFixed(2);
}

// agregarFila(datos): "datos" es opcional; al reutilizar trae
// { id_producto, cantidad, unidad_venta, precio_venta, total }
function agregarFila(datos = null) {
    const tbody = document.getElementById('productosBody');
    const index = filaIndex++;

    let optsProducto = `<option value="">Selecciona...</option>`;
    productos.forEach(p => {
        const sel = datos && Number(datos.id_producto) === Number(p.id_producto) ? 'selected' : '';
        optsProducto += `<option value="${p.id_producto}" ${sel}>${p.nombre}</option>`;
    });

    let optsUnidad = `<option value="">Selecciona...</option>`;
    unidades.forEach(u => {
        const sel = datos && datos.unidad_venta === u ? 'selected' : '';
        optsUnidad += `<option value="${u}" ${sel}>${u.charAt(0).toUpperCase() + u.slice(1)}</option>`;
    });

    const vCantidad = datos ? datos.cantidad : '';
    const vPrecio   = datos ? datos.precio_venta : '';
    const vTotal    = datos ? Number(datos.total).toFixed(2) : '0.00';

    const fila = `
    <tr id="fila_${index}">
        <td>
            <select name="productos[${index}][id_producto]" class="form-select form-select-sm sel-producto" required>
                ${optsProducto}
            </select>
            <div class="small disp-info"></div>
        </td>
        <td>
            <input type="number" name="productos[${index}][cantidad]"
                   class="form-control form-control-sm inp-cantidad"
                   min="0" step="any" placeholder="0" value="${vCantidad}" required>
            <div class="invalid-feedback"></div>
        </td>
        <td>
            <select name="productos[${index}][unidad_venta]" class="form-select form-select-sm" required>
                ${optsUnidad}
            </select>
        </td>
        <td>
            <input type="number" name="productos[${index}][precio_venta]"
                   class="form-control form-control-sm inp-precio"
                   min="0" step="any" placeholder="0.00" value="${vPrecio}" required>
        </td>
        <td>
            <input type="number" name="productos[${index}][total]"
                   class="form-control form-control-sm text-end subtotal-fila inp-subtotal"
                   min="0" step="any" value="${vTotal}"
                   title="Puedes editar el total de este producto">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm"
                    onclick="eliminarFila(${index})">✕</button>
        </td>
    </tr>`;

    tbody.insertAdjacentHTML('beforeend', fila);

    // Fila recién creada (si viene preseleccionada al reutilizar un pedido, ya muestra su disponible)
    mostrarDisponible(tbody.lastElementChild);
    if (datos) validarExistencias();
}

function eliminarFila(index) {
    const fila = document.getElementById(`fila_${index}`);
    if (fila) {
        fila.remove();
        recalcularTotal();
        validarExistencias();
    }
}

//--------------------------ENVIO DEL FORMULARIO CON AJAX--------------------
/**
$("#formPedido").submit(fn)
     jQuery escucha el evento submit del formulario con id="formPedido".
     e.preventDefault()
     Cancela el comportamiento nativo del navegador (recargar la página),
     para manejar el envío manualmente con AJAX.
 */
$("#formPedido").submit(function(e) {
    e.preventDefault();

    if (document.querySelectorAll('#productosBody tr').length === 0) { //Cuenta cuántas filas hay en la tabla si es 0 no hay ningun prod
        alert("Agrega al menos un producto al pedido.");
        return;// Detiene la ejecución, no envía nada
    }

    // No se envía si alguna cantidad supera la existencia disponible
    if (!validarExistencias()) {
        alert("Hay productos con cantidad mayor a la disponible. Corrige las filas marcadas en rojo.");
        const primera = document.querySelector('#productosBody .inp-cantidad.is-invalid');
        if (primera) primera.focus();
        return;
    }

    $.ajax({
        url: "<?= base_url('guardar_pedido') ?>",
        method: "POST",

        //this.serialize() - jQuery convierte todos los inputs del form en cadena con formato url
        data: $(this).serialize(),
        success: function(res) {
            console.log("RESPUESTA:", res);
            if (res.trim() === "ok") {
                pedidoGuardado();
            } else {
                alert("Error: " + res);
            }
        },
        error: function(xhr, status, err) {
            console.log("STATUS:", xhr.status);
            console.log("RESPUESTA SERVIDOR:", xhr.responseText);
            alert("Error " + xhr.status + ": " + xhr.responseText);
        }
    });
}); 

//--------------------------¿MODAL O PÁGINA COMPLETA?--------------------
/*
  Este mismo formulario se usa en dos contextos:
   - Dentro de un iframe (modal de Control de pedidos): el padre tiene cerrarModal().
   - Como página completa (menú lateral > Nuevo pedido): no hay padre con cerrarModal().
  Con esta bandera cada botón sabe qué hacer.
*/
const enModal = (window.parent !== window) && (typeof window.parent.cerrarModal === 'function');

function cancelarPedido() {
    if (enModal) {
        window.parent.cerrarModal();
    } else if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = "<?= base_url('crud_cliente') ?>";
    }
}

function pedidoGuardado() {
    if (enModal) {
        window.parent.cerrarModal();        // cierra el modal y recarga la lista (comportamiento de siempre)
        return;
    }

    // Página completa: avisa, limpia el formulario y deja una fila vacía lista para otro pedido
    const aviso = document.getElementById('avisoPedido');
    aviso.textContent = 'Pedido registrado correctamente.';
    aviso.classList.remove('d-none');

    document.getElementById('formPedido').reset();
    document.getElementById('productosBody').innerHTML = '';
    document.getElementById('totalGeneral').value = '0.00';
    agregarFila();

    window.scrollTo({ top: 0, behavior: 'smooth' });
}