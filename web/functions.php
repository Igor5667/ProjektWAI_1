<?php

function downloadGames(){
    $manager = getDb();
    $query = new MongoDB\Driver\Query([], []); // wybieram wszystkie dokumenty
    $cursor = $manager->executeQuery('wai.games', $query);
    $results = $cursor->toArray();

    return $results;
}

function getDb() {
    try {
        $password = urlencode("w@i_w3b");
        $db_host = "mongodb://wai_web:$password@localhost:27017/wai";
        $manager = new MongoDB\Driver\Manager($db_host);
        return $manager;
    } catch (Exception $e) {
        die("Błąd połączenia z bazą: " . $e->getMessage());
    }
}

function createThumbnail($sourcePath, $destPath, $width = 200, $height = 125) {
    $info = getimagesize($sourcePath);
    if (!$info) return false;

    $fileType = $info['mime'];
    $sourceImage = null;

    if ($fileType === 'image/jpeg') $sourceImage = imagecreatefromjpeg($sourcePath);
    elseif ($fileType === 'image/png') $sourceImage = imagecreatefrompng($sourcePath);

    if (!$sourceImage) return false;

    $origWidth = imagesx($sourceImage);
    $origHeight = imagesy($sourceImage);

    $thumbImage = imagecreatetruecolor($width, $height);
    
    // Białe tło
    $white = imagecolorallocate($thumbImage, 255, 255, 255);
    imagefill($thumbImage, 0, 0, $white);

    imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

    $result = imagejpeg($thumbImage, $destPath, 90);

    imagedestroy($sourceImage);
    imagedestroy($thumbImage);

    return $result;
}

function showMessage($message, $passed){
    $message = implode("<br>", $message);

    $alertClass = $passed ? 'alert-success' : 'alert-danger';
    $icon = $passed ? '✅' : '❌';
    echo "
        <div class='alert $alertClass position-fixed bottom-0 start-50 translate-middle-x mb-5' role='alert'>
            $icon &nbsp; $message
        </div>";
}

function handleUpload($file) {
    $messages = [];
    $name = basename($file['name']);
    $target = 'images/' . $name;
    $thumbTarget = 'images/thumbnails/' . pathinfo($name, PATHINFO_FILENAME) . '.jpg';

    // sprawdzanie typu zdjecia
    if (!preg_match('/\.(jpg|png)$/i', $name)) {
        $messages[] = "Wybrano nieodpowiedni typ zdjęcia.";
    }

    // sprawdzanie rozmiaru zdjecia <1MB
    if ($file['size'] > 1024 * 1024 
    || $file['error'] == UPLOAD_ERR_INI_SIZE // ten blad jest wtedy gdy size>2MB
    || $file['error'] == UPLOAD_ERR_FORM_SIZE) {
        $messages[] = "Plik jest za duży (max 1MB).";
    }

    // przenoszenie obecnego pliku i tworzenie miniaturki
    if (empty($messages)) {
        if (move_uploaded_file($file['tmp_name'], $target)) {
            createThumbnail($target, $thumbTarget);
            return ['success' => true, 'msg' => "Udało się dodać zdjęcie $name"];
        } else {
            $messages[] = "Błąd serwera przy zapisie.";
        }
    }

    return ['success' => false, 'messages' => $messages];
}

function displayGames($page){
    $games = downloadGames();

    $perPage = 4;
    
    $pagesAmount = ceil(count($games)/$perPage);
    if($page < 0) $page = 0;
    if($page > $pagesAmount) $page = $pagesAmount;
    $offset = ($page - 1) * $perPage;

    return [
        'gamesToDisplay' => array_slice($games, $offset, $perPage),
        'pagesAmount' => $pagesAmount
    ];
}