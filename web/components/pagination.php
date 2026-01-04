<div class="mt-3 d-flex justify-content-center align-items-center gap-3 ">
    <?php if($page > 1): ?>
    <a href="index.php?page=<?= $page - 1 ?>">
        <button class="btn btn-secondary">←</button>
    </a>
    <?php endif; ?>
    <div>Strona: <?= $page ?></div>
    <?php if($page < $pagesAmount): ?>
    <a href="index.php?page=<?= $page + 1 ?>">
        <button class="btn btn-secondary">→</button>
    </a>
    <?php endif; ?>
</div>