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
            $katalog = 'images/thumbnails';
            $nazwy_plikow = array_diff(scandir($katalog), ['.','..']);

            foreach($nazwy_plikow as $nazwa_pliku):
                if(preg_match('/\.(jpg|png)$/i', $nazwa_pliku)):
        ?>
                    <div class="card">
                        <img class="card-img-top" src="<?php echo $katalog . '/' . $nazwa_pliku; ?>" alt="<?php echo $nazwa_pliku; ?>">
                        <div class="card-body"><?php echo $nazwa_pliku; ?></div>
                    </div>
        <?php
                endif;
            endforeach;
        ?>
    </div>
</body>
</html>