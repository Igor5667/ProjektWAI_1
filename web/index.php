<?php 
session_start();
require_once '../src/business.php';
require_once '../src/controllers.php';

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
        controller_logout();
        break;
    case 'upload':
        $view = controller_upload($viewData);
        break;
    case 'login':
        $view = controller_login($viewData);
        break;
    case 'register':
        $view = controller_register($viewData);
        break;
    case 'library':
    default:
        $view = controller_library($viewData);
        break;
}
include 'views/layout.php';
?>
