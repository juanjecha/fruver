
<?php foreach($datos_producto as $producto): ?>
<tr>
    <td><?= esc($producto['nombre']) ?></td>
    <td><?= esc($producto['categoria']) ?></td>
    <td><?= esc($producto['descripcion']) ?></td>
    <td>
        <button class="btn btn-warning btn-sm"
            onclick="abrirModal('<?= base_url('editar/'.$producto['id_producto']) ?>')">
            Editar
        </button>
        <button class="btn btn-danger btn-sm eliminar-prod-btn"
            data-id="<?= $producto['id_producto'] ?>">
            <i class="fa fa-trash"></i> Eliminar
        </button>
    </td>
</tr>
<?php endforeach; ?>