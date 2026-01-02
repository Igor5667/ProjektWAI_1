<?php 
    require_once './functions.php';

    $action = isset($_GET['action']) ? $_GET['action'] : 'library';
    $view = '';
    $viewData = [];

    switch ($action) {
        case 'upload':
            $errors = [];
            $passed = false;
            $view = 'upload_view.php';
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = "Nie wybrano zdjęcia";
            } else {
                $result = handleUpload($_FILES['photo']);
                
                if ($result['success']) {
                    $passed = true;
                    $errors[] = $result['msg'];
                } else {
                    $errors = array_merge($errors, $result['errors']);
                }
            }
            showMessage($errors, $passed);
            
            break;
        case 'library':
        default:
            $view = 'library_view.php';

            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

            $picturesData = displayPhotos($page);
            $viewData['photos'] = $picturesData['photosToDisplay'];
            $viewData['pagesAmount'] = $picturesData['pagesAmount'];
            
            break;
    }

    include 'views/layout.php';
?>
