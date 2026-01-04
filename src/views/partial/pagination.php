<div class="mt-3 d-flex justify-content-center align-items-center gap-3 ">
    <?php if($model['page'] > 1): ?>
    <a href="front_controller.php?page=<?= $model['page'] - 1 ?>">
        <button class="btn btn-secondary">←</button>
    </a>
    <?php endif; ?>
    <div>Strona: <?= $model['page'] ?></div>
    <?php if($model['page'] < $model['pagesAmount']): ?>
    <a href="front_controller.php?page=<?= $model['page'] + 1 ?>">
        <button class="btn btn-secondary">→</button>
    </a>
    <?php endif; ?>
</div>