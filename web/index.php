<?php 
    require_once './helpers/functions.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteka gier</title>
    <link rel="stylesheet" href="static/style.css"> 
    <link href="static/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <a href="upload.php" class="position-fixed top-0 end-0 m-2"><button class="btn btn-primary">Dodaj zdjęcie</button></a>
    
    <h1 class="text-center mt-5">Biblioteka gier</h1>
    <p class="text-center">poniżej znajdują się moje gry</p>

    <div class="d-flex flex-wrap gap-4 mt-4 p-2 justify-content-center">
        <?php
            $dir = 'images/thumbnails';
            $photos = downloadPhotos($dir);
            
            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $perPage = 4;
            
            $photosToDisplay = array_slice($photos, 0, $perPage*$page);

            foreach($photosToDisplay as $photo_name):
        ?>
                    <div class="card">
                        <img class="card-img-top" src="<?php echo $dir . '/' . $photo_name; ?>" alt="<?php echo $photo_name; ?>">
                        <div class="card-body"><?php echo $photo_name; ?></div>
                    </div>
        <?php
            endforeach;
        ?>
    </div>
    <?php
        include "components/loadMoreButton.php";
    ?>
</body>
</html>