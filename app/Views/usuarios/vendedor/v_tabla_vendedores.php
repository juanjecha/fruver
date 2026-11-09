<h5>Cuentas registradas (<?= $pager->getTotal() ?>)</h5>

<div class="table-responsive">
<table class="table mt-3">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <?php if (puede('usuarios.ver_password')): ?><th>Contraseña</th><?php endif; ?>
            <th>Estado</th>
            <th>Último acceso</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($vendedores)): ?>
            <tr>
                <td colspan="6" class="text-center text-muted py-3">Aún no hay cuentas de vendedores avanzados.</td>
            </tr>
        <?php else: ?>
        <?php foreach ($vendedores as $v): ?>
        <tr class="fila-producto">
            <td class="nombre"><?= esc($v['nombre']) ?></td>
            <td><?= esc($v['tel'] ?? '') ?></td>
            <td><?= esc($v['correo']) ?></td>
            <?php if (puede('usuarios.ver_password')): ?>
                <td class="celda-password">
                    <?php if (! empty($v['password_texto'])): ?>
                        <span class="pw-texto" data-pw="<?= esc($v['password_texto'], 'attr') ?>">••••••••••</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-ver-pw" title="Mostrar u ocultar">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    <?php else: ?>
                        <span class="text-muted">No disponible</span>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            <td>
                <span class="badge bg-<?= (int) $v['activo'] === 1 ? 'success' : 'secondary' ?>">
                    <?= (int) $v['activo'] === 1 ? 'Activa' : 'Inactiva' ?>
                </span>
            </td>
            <td><?= ! empty($v['ultimo_acceso']) ? esc(date('d/m/Y H:i', strtotime($v['ultimo_acceso']))) : 'Nunca' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?= $pager->links('default', 'v_paginacion') ?>
