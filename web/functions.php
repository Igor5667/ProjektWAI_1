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

function getDataForLibrary($page){
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

function handleUpload($photo, $postData) {
    $messages = [];
    $photoName = basename($photo['name']);
    $target = 'images/' . $photoName;
    $thumbTarget = 'images/thumbnails/' . pathinfo($photoName, PATHINFO_FILENAME) . '.jpg';

    // sprawdzanie czy wszystkie pola są uzupełnione
    $requiredFields = ['title', 'author'];
    if(!checkRequiredFields($postData, $requiredFields)){
        $messages[] = "Nie uzupełniono wszystkich pól.";
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
    $login = $postData['login'];
    $password = $postData['password'];

    // sprawdzanie czy wszystkie pola są uzupełnione
    $requiredFields = ['email', 'login', 'password', 'password-confirmation'];
    if(!checkRequiredFields($postData, $requiredFields)){
        $messages[] = "Nie uzupełniono wszystkich pól.";
    }

    if (getUserByLogin($login)) {
        $messages[] = "Ten login jest już zajęty.";
    }

    if ($password !== $postData['password-confirmation']) {
        $messages[] = "Hasła nie są identyczne.";
    }
    
    $messages = array_merge($messages, validatePhoto($photo));

    // Jeżeli są błędy to przerwanie
    if (!empty($messages)) {
        return ['success' => false, 'messages' => $messages];
    }

    $uniqueName = $login . '_' . time() . '.jpg';
    $targetPath = 'images/profilePhotos/' . $uniqueName;
    
    // Tworzymy miniaturkę 
    if (createThumbnail($photo['tmp_name'], $targetPath, 150, 150)) {
        // dodawanie do bazy danych
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $document = [
            'email' => $postData['email'],
            'login' => $login,
            'password' => $hash,
            'profile_picture' => $targetPath
        ];
        insertToDb('users', $document);
        
        // ustawianie sesji żeby zapisać message przed przekierowaniem
        $_SESSION['flash_message'] = [
            'success' => true, 
            'messages' => ["Konto <b>$login</b> zostało utworzone!<br>Możesz się zalogować."]
        ];
        header("Location: index.php?action=login");
        exit;
    } else {
        return ['success' => false, 'messages' => ["Błąd po stronie serwera. Prosimy spróbować ponownie później."]];
    }
}

function handleLogin($postData){
    // sprawdzanie czy są wszystkie pola wypełnione
    if(!checkRequiredFields($postData, ['login', 'password'])){
        return ['success' => false, 'messages' => ["Nie uzupełniono wszystkich pól."]];
    }

    $user = getUserByLogin($postData['login']);

    // sprawdzanie czy jest taki uzytkownik
    if(!$user){
        return ['success' => false, 'messages' => ["Nie ma takiego użytkownika."]];
    }

    // sprawdzanie hasła
    if(password_verify($postData['password'], $user->password)) {
        session_regenerate_id(); 
        // ustawianie parametrów sesji
        $_SESSION['user_id'] = (string)$user->_id;
        $_SESSION['user_login'] = $user->login;
        $_SESSION['user_photo'] = isset($user->profile_picture) ? $user->profile_picture : null;

        // ustawianie sesji żeby zapisać message przed przekierowaniem
        $_SESSION['flash_message'] = [
            'success' => true, 
            'messages' => ["Zalogowano pomyślnie."]
        ];
        header("Location: index.php?action=library");
        exit;
    }
    else{
        return ['success' => false, 'messages' => ["Niewłaściwe hasło."]];
    }
    return ['success' => false, 'messages' => ["Błąd po stronie serwera. Prosimy spróbować ponownie później."]];
}

function logoutUser() {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}