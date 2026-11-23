<?php $pager->setSurroundCount(2) ?>

<nav class="paginacion-nav" aria-label="Paginación">
    <ul class="paginacion">

        <?php if ($pager->hasPreviousPage()) : ?>
            <li class="paginacion__item">
                <a class="paginacion__link" href="<?= $pager->getFirst() ?>" aria-label="Primera">«</a>
            </li>
            <li class="paginacion__item">
                <a class="paginacion__link" href="<?= $pager->getPreviousPage() ?>" aria-label="Anterior">‹</a>
            </li>
        <?php else : ?>
            <li class="paginacion__item paginacion__item--deshabilitado">
                <span class="paginacion__link">«</span>
            </li>
            <li class="paginacion__item paginacion__item--deshabilitado">
                <span class="paginacion__link">‹</span>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="paginacion__item <?= $link['active'] ? 'paginacion__item--activo' : '' ?>">
                <a class="paginacion__link" href="<?= $link['uri'] ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNextPage()) : ?>
            <li class="paginacion__item">
                <a class="paginacion__link" href="<?= $pager->getNextPage() ?>" aria-label="Siguiente">›</a>
            </li>
            <li class="paginacion__item">
                <a class="paginacion__link" href="<?= $pager->getLast() ?>" aria-label="Última">»</a>
            </li>
        <?php else : ?>
            <li class="paginacion__item paginacion__item--deshabilitado">
                <span class="paginacion__link">›</span>
            </li>
            <li class="paginacion__item paginacion__item--deshabilitado">
                <span class="paginacion__link">»</span>
            </li>
        <?php endif ?>

    </ul>
</nav>