<?php 
session_start();
require_once '../src/business.php';
require_once './functions.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'library';
$view = '';
$viewData = [];
$viewData['currentLocation'] = $action; 
$viewData['message'] = [];
$viewData['isUserLogged'] = isset($_SESSION['user_id']);

// jeżeli wiadomosc z ostatniego żądania jest w sesji to zapisuję w viewData
if (isset($_SESSION['flash_message'])) {
    $viewData['message'] = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

switch ($action) {
    case 'logout':
        logoutUser();
        break;
    case 'upload':
        $view = handleUpload($viewData);
        break;
    case 'login':
        $view = handleLogin($viewData);
        break;
    case 'register':
        $view = handleRegister($viewData);
        break;
    case 'library':
    default:
        $view = 'library_view.php';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $viewData['page'] = $page;

        $gamesData = getDataForLibrary($page);
        $viewData['gamesToDisplay'] = $gamesData['gamesToDisplay'];
        $viewData['pagesAmount'] = $gamesData['pagesAmount'];
        
        break;
}
include 'views/layout.php';
?>
