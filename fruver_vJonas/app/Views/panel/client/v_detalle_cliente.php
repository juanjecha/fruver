<?php /** Modal "Detalles" del pedido (lo comparten Pedidos y Crédito). Lo llena js/cliente/v_portal_cliente.js */ ?>
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="detalleTitulo" aria-hidden="true"
     data-url="<?= base_url('detalle_pedido') ?>">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content cli-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleTitulo">Detalle del pedido <span id="detalle_id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalDetalleBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
