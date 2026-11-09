<h5>Historial de entradas (<?= $pager->getTotal() ?>)</h5>

<div class="table-responsive">
<table class="table mt-3 align-middle">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Unidad de Compra</th>
            <th>Precio Compra</th>
            <th>Valor Total</th>
            <th>Cantidad Total (en u. venta)</th>
            <th>Unidad de Venta</th>
            <th>Fecha Entrada</th>
            <th>Fecha Expiración</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($entradas)): ?>
        <tr>
            <td colspan="10" class="text-center text-muted py-3">
                <?php if (! empty($filtros['q'])): ?>
                    Sin resultados para "<strong><?= esc($filtros['q']) ?></strong>"
                <?php else: ?>
                    Aún no hay entradas registradas.
                <?php endif; ?>
            </td>
        </tr>
        <?php else: ?>
        <?php foreach($entradas as $entrada): ?>
        <?php
            // Lógica para colorear caducidad
            $hoy        = new DateTime();
            $caducidad  = new DateTime($entrada['fecha_caducidad']);
            $diff       = $hoy->diff($caducidad)->days;
            $pasado     = $caducidad < $hoy;

            $classCad = '';
            if ($pasado)       $classCad = 'vencido';
            elseif ($diff <= 2) $classCad = 'pronto';

            $valor_total = $entrada['cantidad'] * $entrada['precio_compra'];
        ?>
        <tr class="fila-producto">
            <td class="nombre"><?= esc($entrada['nombre_producto']) ?></td>
            <td><?= esc($entrada['cantidad']) ?></td>
            <td>
                <?= esc($entrada['u_compra']) ?>
                <?php if($entrada['equivalente'] > 0): ?>
                    (<?= $entrada['equivalente'] ?> <?= esc($entrada['u_venta']) ?>)
                <?php endif; ?>
            </td>
            <td>$<?= number_format($entrada['precio_compra'], 2) ?></td>
            <td><strong>$<?= number_format($valor_total, 2) ?></strong></td>
            <td><?= esc($entrada['conversion']) ?></td>
            <td><?= esc($entrada['u_venta']) ?></td>
            <td class="fecha"><?= esc($entrada['fecha']) ?></td>
            <td class="fecha">
                <span class="badge-caducidad <?= $classCad ?>">
                    <?= esc($entrada['fecha_caducidad']) ?>
                </span>
            </td>
            <td class="acciones">
                <button class="btn btn-warning btn-sm"
                    onclick="abrirModal('<?= base_url('editaren/'.$entrada['id_entrada']) ?>', 'Editar entrada')">
                    <i class="fa fa-pencil"></i> Editar
                </button>
                <button class="btn btn-danger btn-sm eliminar-ent-btn"
                    data-id="<?= $entrada['id_entrada'] ?>">
                    <i class="fa fa-trash"></i> Eliminar
                </button>
                <!-- PUNTO 15: abre "Nueva entrada" con los mismos datos de esta entrada y fecha de hoy -->
                <button class="btn btn-repetir btn-sm"
                    onclick="abrirModal('<?= base_url('alta_entrada?repetir='.$entrada['id_entrada']) ?>', 'Repetir entrada')">
                    <i class="fa-solid fa-rotate-right"></i> Repetir
                </button>
                <button class="btn btn-info btn-sm merma-btn"
                    data-id="<?= $entrada['id_entrada'] ?>"
                    data-producto="<?= esc($entrada['nombre_producto'], 'attr') ?>"
                    data-fecha="<?= esc($entrada['fecha'], 'attr') ?>">
                    <i class="fa fa-history"></i> Mermas
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?= $pager->links("default","v_paginacion") ?>