<?php 
session_start();
require_once './functions.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'library';
$view = '';
$viewData = [];

switch ($action) {
    case 'upload':
        $view = 'upload_view.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $photo = !isset($_FILES['photo']) ? null : $_FILES['photo'];
        $result = handleUpload($photo, $_POST);
            
        showMessage($result['messages'], $result['success']);
        
        break;
    case 'login':
        $view = 'login_view.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $result = handleLogin($_POST);
        
        if ($result['success']) {
            echo "<h1>Witaj " . htmlspecialchars($_SESSION['user_login']) . "!</h1>";
            echo "<p>Jesteś zalogowany. (ID sesji: " . session_id() . ")</p>";
            exit; 
        } else {
            showMessage($result['messages'], false);
        }
        
        break;
    case 'register':
        $view = 'register_view.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $photo = !isset($_FILES['photo']) ? null : $_FILES['photo'];
        $result = handleRegister($photo, $_POST);

        showMessage($result['messages'], $result['success']);

        break;
    case 'library':
    default:
        $view = 'library_view.php';

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $gamesData = getDataForLibrary($page);
        $viewData['gamesToDisplay'] = $gamesData['gamesToDisplay'];
        $viewData['pagesAmount'] = $gamesData['pagesAmount'];
        
        break;
}

include 'views/layout.php';
?>
