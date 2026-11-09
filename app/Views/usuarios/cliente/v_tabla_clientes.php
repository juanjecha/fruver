<h5>Listado de Clientes (<?= count($datos_cliente) ?>)</h5>

        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Nombre*</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($datos_cliente)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        Sin resultados para "<strong><?= esc($filtros['q'] ?? '') ?></strong>"
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach($datos_cliente as $cliente): ?>
                <td class="nombre"><?= estrella_cliente($cliente['preferencial'] ?? 0) ?> <?= esc($cliente['nombre']) ?></td>          
                    <td><?= esc($cliente['ap_p']) ?></td>    
                    <td><?= esc($cliente['ap_m']) ?></td>         
                    <td class="descripcion"><?= esc($cliente['tel']) ?></td>            
                    <td>
                        <button class="btn btn-warning btn-sm" 
                            onclick="abrirModal('<?= base_url('editarc/'.$cliente['id_cliente']) ?>', 'Editar Cliente')">
                            <i class="fa fa-pencil"></i> Editar
                        </button>
                        <button class="btn btn-danger btn-sm eliminar-btn"
                            data-id="<?= $cliente['id_cliente'] ?>">
                            <i class="fa fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?= $pager->links() ?>