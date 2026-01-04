<?php

function dispatch($routing, $action){
    // wybieranie odpowiedniego controllera jeżeli nie ma to libary
    $controller = $routing[$action] ?? $routing['library'];

    // ustawianie danych wejsciowych
    $viewData = [];
    $viewData['currentLocation'] = $action; 
    $viewData['message'] = [];
    $viewData['isUserLogged'] = isset($_SESSION['user_id']);

    // jeżeli wiadomosc z ostatniego żądania jest w sesji to zapisuję w viewData
    if (isset($_SESSION['flash_message'])) {
        $viewData['message'] = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }

    $view = $controller($viewData);
    
    // render widoku
    include 'views/layout.php';
}
?>