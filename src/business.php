<?php 

function fetchData($collection){
    $manager = getDb();
    $query = new MongoDB\Driver\Query([], []); // wybieram wszystkie dokumenty
    $cursor = $manager->executeQuery("wai.$collection", $query);
    $results = $cursor->toArray();
    return $results;
}

function getUserByLogin($login) {
    $manager = getDb();
    $query = new MongoDB\Driver\Query(['login' => $login]);
    $cursor = $manager->executeQuery('wai.users', $query);
    $results = $cursor->toArray();
    return $results[0] ?? null;
}

function validatePhoto($photo) {
    $errors = [];
    if (!$photo || $photo['error'] === UPLOAD_ERR_NO_FILE) {
        return ["Nie wybrano zdjęcia."];
    }

    if ($photo['error'] !== UPLOAD_ERR_OK) {
        return ["Błąd przesyłania zdjęcia."];
    }

    $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), ['jpg', 'png'])) {
        $errors[] = "Niedozwolony format pliku. (jpg lub png)";
    }

    if ($photo['size'] > 1024 * 1024
    || $photo['error'] == UPLOAD_ERR_INI_SIZE // ten blad jest wtedy gdy size>2MB
    || $photo['error'] == UPLOAD_ERR_FORM_SIZE) {
        $errors[] = "Plik jest za duży (max 1MB).";
    }

    return $errors;
}

function checkRequiredFields($data, $fieldsToCheck){
    foreach ($fieldsToCheck as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            return false;
        }
    }
    return true;
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

?>