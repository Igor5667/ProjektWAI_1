<?php

function fetchData($collection){
    $manager = getDb();
    $query = new MongoDB\Driver\Query([], []); // wybieram wszystkie dokumenty
    $cursor = $manager->executeQuery("wai.$collection", $query);
    $results = $cursor->toArray();
    return $results;
}

function isLoginTaken($login) {
    $manager = getDb();
    $query = new MongoDB\Driver\Query(['login' => $login]);
    $cursor = $manager->executeQuery('wai.users', $query);
    $results = $cursor->toArray();
    return count($results) > 0;
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
        $errors = ["Niedozwolony format pliku. (jpg lub png)"];
    }

    if ($photo['size'] > 1024 * 1024
    || $photo['error'] == UPLOAD_ERR_INI_SIZE // ten blad jest wtedy gdy size>2MB
    || $photo['error'] == UPLOAD_ERR_FORM_SIZE) {
        $errors = ["Plik jest za duży (max 1MB)."];
    }

    return $errors;
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

function showMessage($messages, $passed){
    $message = implode("<br>", $messages);

    $alertClass = $passed ? 'alert-success' : 'alert-danger';
    $icon = $passed ? '✅' : '❌';
    echo "
        <div class='alert $alertClass position-fixed bottom-0 start-50 translate-middle-x mb-5 w-100 d-flex align-items-center gap-3' style='max-width: 400px;'role='alert'>
            <span>$icon</span>$message
        </div>";
}

function handleUpload($photo, $postData) {
    $messages = [];
    $photoName = basename($photo['name']);
    $target = 'images/' . $photoName;
    $thumbTarget = 'images/thumbnails/' . pathinfo($photoName, PATHINFO_FILENAME) . '.jpg';

    // sprawdzanie czy autor albo tytul zostali dodani
    if($postData['author'] == '' || $postData['title'] == ''){
        $messages[] = "Nie podano autora bądz tytułu.";
    }

    $messages = array_merge($messages, validatePhoto($photo));

    if (empty($messages)) {
        // dodawaie do bazy danch
        $document = [
            'file_name' => $photoName,
            'author' => $postData['author'],
            'title' => $postData['title']
        ];
        insertToDb('games', $document);
        
        // przenoszenie pliku oraz tworzenie miniaturki
        if (move_uploaded_file($photo['tmp_name'], $target)) {
            createThumbnail($target, $thumbTarget);
            $title = $postData['title'];
            return ['success' => true, 'messages' => ["Udało się dodać grę <b>$title</b>"]];
        } else {
            $messages[] = "Błąd serwera przy zapisie.";
        }
    }

    return ['success' => false, 'messages' => $messages];
}

function handleRegister($photo, $postData) {
    $messages = [];
    $email = $postData['email'];
    $login = $postData['login'];
    $pass = $postData['password'];
    $passConfirm = $postData['password-confirmation'];

    // sprawdzanie czy wszystkie pola są uzupełnione
    if($email == '' || $login == '' || $pass == '' || $passConfirm == ''){
        $messages[] = "Nie uzupełniono wszystkich pól.";
    }

    if (isLoginTaken($login)) {
        $messages[] = "Ten login jest już zajęty.";
    }

    if ($pass !== $passConfirm) {
        $messages[] = "Hasła nie są identyczne.";
    }
    
    $messages = array_merge($messages, validatePhoto($photo));

    // Jeżeli są błędy to przerwanie
    if (!empty($messages)) {
        return ['success' => false, 'messages' => $messages];
    }

    $targetPath = 'images/profilePhotos/' . $login;
    
    // Tworzymy miniaturkę 
    if (createThumbnail($photo['tmp_name'], $targetPath, 150, 150)) {
        // dodawanie do bazy danych
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $document = [
            'email' => $email,
            'login' => $login,
            'password' => $hash,
            'profile_picture' => $targetPath
        ];
        insertToDb('users', $document);

        return ['success' => true, 'messages' => ["Konto zostało utworzone!"]];
        
    } else {
        return ['success' => false, 'messages' => ["Błąd po stronie serwera. Prosimy spróbować ponownie później."]];
    }
}

function displayGames($page){
    $games = fetchData('games');

    // thumbnaile są tylko jpg
    foreach($games as $game){
        $game->thnumbnail_name = pathinfo($game->file_name, PATHINFO_FILENAME) . '.jpg';
    }

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