<?php

function fetchGames(){
    $manager = getDb();
    $query = new MongoDB\Driver\Query([], []); // wybieram wszystkie dokumenty
    $cursor = $manager->executeQuery('wai.games', $query);
    $results = $cursor->toArray();

    // thumbnaile są tylko jpg
    foreach($results as $result){
        $result->thnumbnail_name = pathinfo($result->file_name, PATHINFO_FILENAME) . '.jpg';
    }

    return $results;
}

function getDb() {
    try {
        // używam niskopoziomowego Manager ponieważ 
        // na VirtualMachine nie ma Composera żeby użyć Client
        $password = urlencode("w@i_w3b");
        $db_host = "mongodb://wai_web:$password@localhost:27017/wai";
        $manager = new MongoDB\Driver\Manager($db_host);
        return $manager;
    } catch (Exception $e) {
        die("Błąd połączenia z bazą: " . $e->getMessage());
    }
}

function insertToDb($collection, $document){
    $manager = getDb();
    $bulk = new MongoDB\Driver\BulkWrite;
    
    $bulk->insert($document);
    
    $manager->executeBulkWrite("wai.$collection", $bulk);
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

function handleUpload($photo, $uploadData) {
    $messages = [];
    $photoName = basename($photo['name']);
    $target = 'images/' . $photoName;
    $thumbTarget = 'images/thumbnails/' . pathinfo($photoName, PATHINFO_FILENAME) . '.jpg';

    // sprawdzanie typu zdjecia
    if (!preg_match('/\.(jpg|png)$/i', $photoName)) {
        $messages[] = "Wybrano nieodpowiedni typ zdjęcia.";
    }

    // sprawdzanie rozmiaru zdjecia <1MB
    if ($photo['size'] > 1024 * 1024 
    || $photo['error'] == UPLOAD_ERR_INI_SIZE // ten blad jest wtedy gdy size>2MB
    || $photo['error'] == UPLOAD_ERR_FORM_SIZE) {
        $messages[] = "Plik jest za duży (max 1MB).";
    }

    // sprawdzanie czy autor albo tytul zostali dodani
    if($uploadData['author'] == '' || $uploadData['title'] == ''){
        $messages[] = "Nie podano autora bądz tytułu";
    }

    if (empty($messages)) {

        // dodawaie do bazy danch
        $document = [
            'file_name' => $photoName,
            'author' => $viewData['author'],
            'title' => $viewData['title']
        ];
        insertToDb('games', $document);
        
        // przenoszenie pliku oraz tworzenie miniaturki
        if (move_uploaded_file($photo['tmp_name'], $target)) {
            createThumbnail($target, $thumbTarget);
            $title = $uploadData['title'];
            return ['success' => true, 'msg' => "Udało się dodać grę <b>$title</b>"];
        } else {
            $messages[] = "Błąd serwera przy zapisie.";
        }
    }

    return ['success' => false, 'messages' => $messages];
}

function displayGames($page){
    $games = fetchGames();

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