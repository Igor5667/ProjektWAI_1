<?php
function handleUpload(&$model) {
    if($_SERVER['REQUEST_METHOD'] !== 'POST') return 'upload_view.php';
    $photo = !isset($_FILES['photo']) ? null : $_FILES['photo'];
    $messages = [];
    $photoName = basename($photo['name']);
    $target = 'images/' . $photoName;
    $thumbTarget = 'images/thumbnails/' . pathinfo($photoName, PATHINFO_FILENAME) . '.jpg';

    // jeżeli użytkownik jest zalogowany to on zawsze jest autorem
    if (isset($_SESSION['user_id'])) {
        $author = $_SESSION['user_login'];
    } else {
        $author = isset($_POST['author']) ? $_POST['author'] : null;
    }
    $_POST['author'] = $author;

    // sprawdzanie czy wszystkie pola są uzupełnione
    $requiredFields = ['title', 'author'];
    if(!checkRequiredFields($_POST, $requiredFields)){
        $messages[] = "Nie uzupełniono wszystkich pól.";
    }

    $messages = array_merge($messages, validatePhoto($photo));

    if (empty($messages)) {
        // dodawaie do bazy danch
        $document = [
            'file_name' => $photoName,
            'author' => $_POST['author'],
            'title' => $_POST['title']
        ];
        insertToDb('games', $document);
        
        // przenoszenie pliku oraz tworzenie miniaturki
        if (move_uploaded_file($photo['tmp_name'], $target)) {
            createThumbnail($target, $thumbTarget);
            
            // ustawianie sesji żeby zapisać message przed przekierowaniem
            $title = $_POST['title'];
            $_SESSION['flash_message'] = [
                'success' => true, 
                'messages' => ["Dodano grę <b>$title</b>."]
            ];
            header("Location: index.php");
            exit;
        } else {
            $messages[] = "Błąd serwera przy zapisie.";
        }
    }
    $model['message'] = ['success' => false, 'messages' => $messages];
    return 'upload_view.php';
}

function handleRegister(&$model) {
    if($_SERVER['REQUEST_METHOD'] !== 'POST') return 'register_view.php';
    $photo = !isset($_FILES['photo']) ? null : $_FILES['photo'];
    $messages = [];
    $login = $_POST['login'];
    $password = $_POST['password'];

    // sprawdzanie czy wszystkie pola są uzupełnione
    $requiredFields = ['email', 'login', 'password', 'password-confirmation'];
    if(!checkRequiredFields($_POST, $requiredFields)){
        $messages[] = "Nie uzupełniono wszystkich pól.";
    }

    if (getUserByLogin($login)) {
        $messages[] = "Ten login jest już zajęty.";
    }

    if ($password !== $_POST['password-confirmation']) {
        $messages[] = "Hasła nie są identyczne.";
    }
    
    $messages = array_merge($messages, validatePhoto($photo));

    // Jeżeli są błędy to przerwanie
    if (!empty($messages)) {
        $model['message'] = ['success' => false, 'messages' => $messages];
        return 'register_view.php';
    }

    $uniqueName = $login . '_' . time() . '.jpg';
    $targetPath = 'images/profilePhotos/' . $uniqueName;
    
    // Tworzymy miniaturkę 
    if (createThumbnail($photo['tmp_name'], $targetPath, 150, 150)) {
        // dodawanie do bazy danych
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $document = [
            'email' => $_POST['email'],
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
        $model['message'] =  ['success' => false, 'messages' => ["Błąd po stronie serwera. Prosimy spróbować ponownie później."]];
        return 'register_view.php';
    }
}

function handleLogin(&$model){
    // powrot gdy jest zalogowany
    if(isset($_SESSION['user_id'])) {
        header("Location: index.php?action=library");
        exit;
    };

    // przerwanie jeżeli GET
    if($_SERVER['REQUEST_METHOD'] !== 'POST') return 'login_view.php';

    // sprawdzanie czy są wszystkie pola wypełnione
    if(!checkRequiredFields($_POST, ['login', 'password'])){
        $model['message'] =  ['success' => false, 'messages' => ["Nie uzupełniono wszystkich pól."]];
        return 'login_view.php';
    }

    $user = getUserByLogin($_POST['login']);

    // sprawdzanie czy jest taki uzytkownik
    if(!$user){
        return ['success' => false, 'messages' => ["Nie ma takiego użytkownika."]];
    }

    // sprawdzanie hasła
    if(password_verify($_POST['password'], $user->password)) {
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
        $model['message'] =  ['success' => false, 'messages' => ["Niewłaściwe hasło."]];
        return 'login_view.php';
    }
    $model['message'] = ['success' => false, 'messages' => ["Błąd po stronie serwera. Prosimy spróbować ponownie później."]];
    return 'login_view.php';
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
    header("Location: index.php?action=login");
    exit;
}
?>