<?php 
    require_once './functions.php';

    $action = isset($_GET['action']) ? $_GET['action'] : 'library';
    $view = '';
    $viewData = [];

    switch ($action) {
        case 'upload':
            $view = 'upload_view.php';

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
            break;
        case 'library':
        default:
            $view = 'library_view.php';
            
            $dir = 'images/thumbnails';
            $photos = downloadPhotos($dir);
            
            $perPage = 4;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $pagesAmount = ceil(count($photos)/$perPage);
            if($page < 0) $page = 0;
            if($page > $pagesAmount) $page = $pagesAmount;
            $offset = ($page - 1) * $perPage;

            $viewData['photos'] = array_slice($photos, $offset, $perPage);
            
            break;
    }

    include 'views/layout.php';
?>
