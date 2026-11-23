<?php foreach($datos_merma as $merma): ?>
    <tr class="fila-merma">
        <td><?= esc($merma['nombre']) ?></td>           <!-- nombre del producto -->
        <td><?= esc($merma['fecha_entrada']) ?></td>    <!-- fecha de la entrada -->
        <td><?= esc($merma['u_venta']) ?></td>          <!-- unidad -->
        <td class="cantidad"><?= esc($merma['cantidad']) ?></td>
        <td><?= esc($merma['fecha']) ?></td>            <!-- fecha de la merma -->
        <td class="notas"><?= esc($merma['notas']) ?></td>
        <td>
           <button class="btn btn-warning btn-sm"
              onclick="abrirModal('<?= base_url('editar_merma/'.$merma['id_merma']) ?>','Editar merma')">
            <i class="fa fa-pencil"></i> Editar
           </button>
           <button class="btn btn-danger btn-sm"
              onclick="eliminarProducto(<?= $merma['id_merma'] ?>)">
            <i class="fa fa-trash"></i> Eliminar
            </button>
        </td>
    </tr>
<?php endforeach; ?>
                    
