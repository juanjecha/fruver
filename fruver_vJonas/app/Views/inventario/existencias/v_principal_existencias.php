<?php
/**
 * Existencias (Inventario) con layout y menú lateral, igual que Productos.
 * Lo llama ExistenciasController::index() con:
 *     $this->render('inventario/existencias/v_principal_existencias', $datos, 'inventario');
 */
?>
<?= $this->extend('layout/v_base') ?>

<?= $this->section('estilos') ?>
    <link rel="stylesheet" href="<?= base_url('css/existencias/v_principal_existencias.css') ?>?v=2">
<?= $this->endSection() ?>


<?= $this->section('contenido') ?>
<div class="fv-pagina">

    <div class="mb-3">
        <h3 id="titulo">Existencias</h3>
        <small>Resumen de existencias del inventario</small>
    </div>

    <!-- ───────── GRÁFICA GENERAL ───────── -->
    <div class="card mb-3 p-3">
        <h5>Resumen de existencias</h5>
        <div class="ex-grafica">
            <canvas id="graficaExistencias"></canvas>
        </div>
    </div>

    <!-- ───────── MERMA POR PRODUCTO ───────── -->
    <div class="card mb-3 p-3">
        <h5>Merma por Producto</h5>

        <div class="ex-grid-merma mt-2">
            <?php if (empty($mermas_lista)): ?>
                <p class="text-muted">No hay mermas registradas.</p>
            <?php else: ?>
                <?php foreach ($mermas_lista as $m): ?>
                <div class="ex-merma-card">
                    <span class="ex-merma-nombre"><?= esc($m['label']) ?></span>
                    <span class="ex-merma-valor"><?= number_format($m['total_merma'], 2) ?></span>
                    <small class="ex-merma-nota">unidades en merma</small>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ───────── CONTROL DE EXISTENCIAS ───────── -->
    <div class="card p-3">
        <h5 class="ex-titulo">Control de existencias</h5>
        <p class="ex-subtitulo">Cantidad en inventario por producto</p>

        <!-- Tarjetas resumen -->
        <div class="ex-cards-resumen">

            <div class="ex-card">
                <div class="ex-card-line ex-verde-line"></div>
                <div class="ex-card-body">
                    <div class="ex-card-top">
                        <span class="ex-card-label">Total Productos</span>
                        <span class="ex-icono ex-icono-verde"></span>
                    </div>
                    <span class="ex-card-valor"><?= $total_productos ?></span>
                </div>
            </div>

            <div class="ex-card">
                <div class="ex-card-line ex-amarillo-line"></div>
                <div class="ex-card-body">
                    <div class="ex-card-top">
                        <span class="ex-card-label">Con Stock Bajo</span>
                        <span class="ex-icono ex-icono-amarillo"></span>
                    </div>
                    <span class="ex-card-valor"><?= $stock_bajo ?></span>
                </div>
            </div>

            <div class="ex-card">
                <div class="ex-card-line ex-rojo-line"></div>
                <div class="ex-card-body">
                    <div class="ex-card-top">
                        <span class="ex-card-label">Sin Stock</span>
                        <span class="ex-icono ex-icono-rojo"></span>
                    </div>
                    <span class="ex-card-valor"><?= $sin_stock ?></span>
                </div>
            </div>

        </div>

        <!-- Título sección productos -->
        <div class="ex-seccion-titulo">
            <h5>Existencias por Producto</h5>
        </div>

        <!-- Lista por producto -->
        <?php if (empty($productos)): ?>
            <p class="text-muted">No hay productos registrados.</p>

        <?php else: ?>
            <?php foreach ($productos as $p): ?>

                <?php
                //para_venta en el modelo
                    if ($p['para_venta'] <= 0) {
                        $estado       = 'Sin Stock';
                        $clase_borde  = 'ex-borde-rojo';
                        $clase_badge  = 'ex-badge-rojo';
                    } elseif ($p['para_venta'] < 5) {
                        $estado       = 'Stock Bajo';
                        $clase_borde  = 'ex-borde-amarillo';
                        $clase_badge  = 'ex-badge-amarillo';
                    } else {
                        $estado       = 'En Stock';
                        $clase_borde  = 'ex-borde-verde';
                        $clase_badge  = 'ex-badge-verde';
                    }
                ?>

                <div class="ex-producto-card <?= $clase_borde ?>">
                    <div class="ex-producto-header">
                        <h5 class="ex-producto-nombre"><?= esc($p['nombre']) ?></h5>
                        <span class="ex-badge <?= $clase_badge ?>"><?= $estado ?></span>
                    </div>

                    <div class="ex-grid">

                        <div class="ex-box ex-box-verde">
                            <small class="ex-box-label">Existencias Totales</small>
                            <span class="ex-box-valor ex-color-verde"><?= number_format($p['total'], 2) ?></span>
                            <small class="ex-box-nota">(Bloqueadas + Para venta)</small>
                        </div>

                        <div class="ex-box ex-box-naranja">
                            <small class="ex-box-label ex-color-naranja">Existencias Bloqueadas</small>
                            <span class="ex-box-valor ex-color-naranja"><?= number_format($p['bloqueadas'], 2) ?></span>
                            <small class="ex-box-nota ex-color-naranja">En proceso de pedido</small>
                        </div>

                        <div class="ex-box ex-box-verde">
                            <small class="ex-box-label">Existencias para Venta</small>
                            <span class="ex-box-valor ex-color-verde"><?= number_format($p['para_venta'], 2) ?></span>
                            <small class="ex-box-nota">Disponibles para nuevos pedidos</small>
                        </div>

                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels     = <?= $chart_labels ?>; // Etiquetas del eje X
    const ventas     = <?= $chart_ventas ?>; // Unidades disponibles para venta
    const bloqueadas = <?= $chart_bloqueadas ?>;
    const merma      = <?= $chart_merma ?>;

    const ctx = document.getElementById('graficaExistencias').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Para Venta',
                    data: ventas,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: '#28a745',
                    borderWidth: 1
                },
                {
                    label: 'Bloqueadas',
                    data: bloqueadas,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: '#ffc107',
                    borderWidth: 1
                },
                {
                    label: 'Merma',
                    data: merma,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: '#dc3545',
                    borderWidth: 1,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: {
                    display: true,
                    text: 'Resumen general de existencias'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Unidades' }
                }
            }
        }
    });
</script>
<?= $this->endSection() ?>
