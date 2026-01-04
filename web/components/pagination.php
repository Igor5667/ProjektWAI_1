<div class="mt-3 d-flex justify-content-center align-items-center gap-3 ">
    <?php if($viewData['page'] > 1): ?>
    <a href="index.php?page=<?= $viewData['page'] - 1 ?>">
        <button class="btn btn-secondary">←</button>
    </a>
    <?php endif; ?>
    <div>Strona: <?= $viewData['page'] ?></div>
    <?php if($viewData['page'] < $viewData['pagesAmount']): ?>
    <a href="index.php?page=<?= $viewData['page'] + 1 ?>">
        <button class="btn btn-secondary">→</button>
    </a>
    <?php endif; ?>
</div>