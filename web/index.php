<?php 
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
