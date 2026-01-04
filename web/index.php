<?php 
session_start();
require_once './functions.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'library';
$view = '';
$viewData = [];
$viewData['message'] = [];

switch ($action) {
    case 'logout':
        logoutUser();
        header("Location: index.php?action=login");
        exit;
    case 'upload':
        $view = 'upload_view.php';
        if($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $photo = !isset($_FILES['photo']) ? null : $_FILES['photo'];
        $viewData['message'] = handleUpload($photo, $_POST);
        
        break;
    case 'login':
        // jeżeli zalogowany to przekierowuje do library
        if(isset($_SESSION['user_id'])) {
            header("Location: index.php?action=library");
            exit;
        };

        $view = 'login_view.php';
        if($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $result = handleLogin($_POST);
            if($result['success']) {
                header("Location: index.php?action=library");
                exit;
            } else {
                $viewData['message'] = $result;
            }
        
        break;
    case 'register':
        $view = 'register_view.php';
        if($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $photo = !isset($_FILES['photo']) ? null : $_FILES['photo'];
        $viewData['message'] = handleRegister($photo, $_POST);

        break;
    case 'library':
    default:
        $view = 'library_view.php';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $gamesData = getDataForLibrary($page);
        $viewData['gamesToDisplay'] = $gamesData['gamesToDisplay'];
        $viewData['pagesAmount'] = $gamesData['pagesAmount'];
        $viewData['showReturnButton'] = false;
        
        break;
}

include 'views/layout.php';
?>
