<a href="index.php?action=upload" class="position-fixed top-0 end-0 m-2 z-3">
    <button class="btn btn-primary">Dodaj zdjęcie</button>
</a>

<h1 class="text-center mt-5">Biblioteka gier</h1>
<p class="text-center">poniżej znajdują się moje gry</p>

<div class="d-flex flex-wrap gap-4 mt-4 p-2 justify-content-center">
    <?php foreach($viewData['photos'] as $photo_name): ?>
        <div class="card">
            <img class="card-img-top" src="<?= 'images/thumbnails/' . $photo_name; ?>" alt="<?php echo $photo_name; ?>">
            <div class="card-body"><?= $photo_name; ?></div>
        </div>
    <?php endforeach; ?>
</div>

<?php
    include "components/pagination.php";
?>