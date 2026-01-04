<h1 class="text-center mt-5">Biblioteka gier</h1>
<p class="text-center">poniżej znajdują się moje gry</p>

<div class="d-flex flex-wrap gap-4 mt-4 p-2 justify-content-center">
    <?php foreach($viewData['gamesToDisplay'] as $game): ?>
        <div class="card">
            <img class="card-img-top" src="<?= 'images/thumbnails/' . $game->thnumbnail_name; ?>" alt="<?= $game->title; ?>">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-1"><?= $game->title; ?></h5>
                <p class="card-text text-muted small">Autor: <?= $game->author; ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
    include "components/pagination.php";
?>