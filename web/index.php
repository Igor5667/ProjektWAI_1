<?php 
require_once './functions.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'library';
$view = '';
$viewData = [];

switch ($action) {
    case 'upload':
        $messages = [];
        $passed = false;
        $view = 'upload_view.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            $messages[] = "Nie wybrano zdjęcia";
        } else {
            $result = handleUpload($_FILES['photo']);
            
            if ($result['success']) {
                $passed = true;
                $messages[] = $result['msg'];
            } else {
                $messages = array_merge($messages, $result['messages']);
            }
        }
        $viewData['messages'] = $messages;
        $viewData['passed'] = $passed;
        
        break;
    case 'library':
    default:
        $view = 'library_view.php';

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $gamesData = displayGames($page);
        $viewData['gamesToDisplay'] = $gamesData['gamesToDisplay'];
        $viewData['pagesAmount'] = $gamesData['pagesAmount'];
        
        break;
}

include 'views/layout.php';
?>
