<h5>Listado de Repartidores (<?= $pager->getTotal() ?>)</h5>

<div class="table-responsive">
<table class="table mt-3">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Apellido Paterno</th>
            <th>Apellido Materno</th>
            <th>Teléfono</th>
            <th>Dirección</th>
            <th>Notas</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($repartidores)): ?>
            <tr>
                <td colspan="7" class="text-center text-muted py-3">
                    <?php if (! empty($filtros['q'])): ?>
                        Sin resultados para "<strong><?= esc($filtros['q']) ?></strong>"
                    <?php else: ?>
                        Aún no hay repartidores registrados.
                    <?php endif; ?>
                </td>
            </tr>
        <?php else: ?>
        <?php foreach ($repartidores as $repartidor): ?>
        <tr class="fila-producto">
            <td class="nombre"><?= esc($repartidor['nombre']) ?></td>
            <td><?= esc($repartidor['ap_p']) ?></td>
            <td><?= esc($repartidor['ap_m'] ?? '') ?></td>
            <td class="descripcion"><?= esc($repartidor['tel']) ?></td>
            <td class="direccion"><?= esc($repartidor['direccion']) ?></td>
            <td class="notas"><?= esc($repartidor['notas'] ?? '') ?></td>
            <td class="acciones">
                <?php if (puede('repartidores.editar')): ?>
                <button class="btn btn-warning btn-sm"
                    onclick="abrirModal('<?= base_url('editarrep/' . $repartidor['id_repartidor']) ?>', 'Editar Repartidor')">
                    <i class="fa-solid fa-pencil"></i> Editar
                </button>
                <?php endif; ?>
                <?php if (puede('repartidores.eliminar')): ?>
                <button class="btn btn-danger btn-sm eliminar-rep-btn"
                    data-id="<?= $repartidor['id_repartidor'] ?>">
                    <i class="fa-solid fa-trash"></i> Eliminar
                </button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?= $pager->links('default', 'v_paginacion') ?>
