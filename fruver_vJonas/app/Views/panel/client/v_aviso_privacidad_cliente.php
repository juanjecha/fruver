<?php /** Modal con el aviso de privacidad (se abre con el botón "Ver" de Mis datos). */ ?>
<div class="modal fade" id="modalAviso" tabindex="-1" aria-labelledby="avisoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content cli-modal cli-aviso-modal">
            <div class="modal-header justify-content-center position-relative">
                <h5 class="modal-title" id="avisoTitulo">Aviso de privacidad</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <p class="cli-aviso-modal__marca">Fruver</p>

                <p>Fruver, gestor y administrador de inventario, con dirección (...), es responsable del tratamiento de tus datos personales, conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares.</p>

                <h6>¿Qué recolectamos?</h6>
                <ul>
                    <li><strong>Clientes:</strong> nombre, dirección, teléfono, RFC, tipo de cliente (Contado, Menudeo, Crédito, Mayoreo), historial de compras, pagos, adeudos, datos de entrega.</li>
                    <li><strong>Repartidores:</strong> nombre, teléfono, dirección, pedidos asignados, hora de salida, evidencia de entrega.</li>
                    <li><strong>Administradores:</strong> usuario, contraseña y registro de las operaciones que realizan en el sistema.</li>
                </ul>
                <p>La empresa no recolecta datos personales sensibles (salud, religión, origen étnico, etc.).</p>

                <h6>¿Para qué los usamos?</h6>
                <ul>
                    <li>Registrar y clasificar clientes y repartidores</li>
                    <li>Asignar precios y listas de precios especiales</li>
                    <li>Registrar ventas, pedidos y entregas</li>
                    <li>Administrar crédito y pagos</li>
                    <li>Mantener historial de compras</li>
                </ul>

                <h6>¿Con quién compartimos tus datos?</h6>
                <p>No vendemos tus datos. Solo los compartimos con autoridades cuando la ley lo exige. Por otra parte, nuestros repartidores tienen acceso a información necesaria (dirección y detalles de compra) con el fin de entregar completa y correctamente su compra.</p>

                <h6>¿Cuánto tiempo los conservamos?</h6>
                <p>Durante la relación con nosotros y el tiempo que exijan las leyes fiscales y contables. Después se bloquean y eliminan de forma segura.</p>

                <h6>¿Cómo protegemos tus datos?</h6>
                <p>Con medidas administrativas, técnicas y físicas, como acceso por usuario y contraseña, permisos por función y registro de movimientos.</p>

                <h6>Tus derechos</h6>
                <p>Puedes acceder a tus datos, rectificarlos, cancelarlos u oponerte a su uso (derechos ARCO), además de revocar tu consentimiento. Envía tu solicitud a <strong>fruver@gmail.com</strong> con tu nombre, una identificación oficial y la descripción del derecho que deseas ejercer.</p>
                <p>La cancelación puede no proceder si tienes pedidos activos, o adeudos pendientes, o si la Ley nos obliga a conservar tu información.</p>

                <h6>En caso de cambios</h6>
                <p>Cualquier modificación a este aviso de privacidad será notificada a través del correo electrónico proporcionado. La versión vigente siempre estará disponible en el sitio web.</p>
            </div>

            <div class="modal-footer justify-content-between">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" id="avisoConsentimiento" checked>
                    <label class="form-check-label" for="avisoConsentimiento">
                        He leído el aviso de privacidad detalladamente y doy mi consentimiento para el uso de mis datos personales para las finalidades necesarias.
                    </label>
                </div>
                <button type="button" class="cli-boton" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
