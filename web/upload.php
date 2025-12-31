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
    <?php
        // nie wyświetlaj gdy po prostu wchodzisz na stronę
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $passed = false; // Domyślnie operacja nieudana

            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = "Nie wybrano zdjęcia";
            } 
            else {
                $file = $_FILES['photo'];
                $name = basename($file['name']);
                $target = 'images/' . $name;
                $thumbTarget = 'images/thumbnails/' . pathinfo($name, PATHINFO_FILENAME) . '.jpg';

                // sprawdzenie rozmiaru zdjecia < 1MB
                if ($file['size'] > 1024 * 1024
                || $file['error'] == UPLOAD_ERR_INI_SIZE //    gdy size >2MB to zwraca error
                || $file['error'] == UPLOAD_ERR_FORM_SIZE) {// by size i zeruje wagę pliku
                    $errors[] = "Plik jest za duży (max 1MB).";
                }

                // sprawdzenie typu zdjecia
                if (!preg_match('/\.(jpg|png)$/i', $name)) {
                    $errors[] = "Wybrano nieodpowiedni typ zdjęcia.";
                }

                // przenoszenie obecnego pliku i tworzenie miniaturk
                if(empty($errors)){
                    if (move_uploaded_file($file['tmp_name'], $target)) {
                        createThumbnail($target, $thumbTarget);
                        $errors[] = "Udało się dodać zdjęcie <b>$name</b>";
                        $passed = true;
                    } else {
                        $errors[] = "Nie udało się dodać zdjęcia (błąd serwera) spróbuj ponownie później";
                    }
                }
            }
            showMessage($errors, $passed);
        }
    ?>

    <a href="index.php" class="position-fixed top-0 end-0 m-2">
        <button class="btn btn-primary">Wróć</button>
    </a>

    <h1 class="text-center mt-5">Dodaj grę do biblioteki</h1>
    <p class="text-center">wypełnij poniższy formularz i prześlij</p>

    
    <form method="post" enctype="multipart/form-data" class="container d-flex flex-column gap-3 mt-5" style="background-color: white">
        <div>
            <input type="file" name="photo" id="fileInput" class="form-control">
            <label for="fileInput" class="text-center" style="color: #000000c1; font-size: 12px">Powinno być w formacie JPG lub PNG oraz nie przekraczać 1MB</label>
        </div>
        <button type="submit" class="btn btn-primary">Wyślij</button>
    </form>

</body>
</html>