<?php

function dispatch($routing, $action){
    // wybieranie odpowiedniego controllera jeżeli nie ma to libary
    $controller = $routing[$action] ?? $routing['library'];

    // ustawianie danych wejsciowych
    $model = [];
    $model['currentLocation'] = $action; 
    $model['message'] = [];
    $model['isUserLogged'] = isset($_SESSION['user_id']);

    // jeżeli wiadomosc z ostatniego żądania jest w sesji to zapisuję w model
    if (isset($_SESSION['flash_message'])) {
        $model['message'] = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }

    $view = $controller($model);
    
    // render widoku
    include 'views/layout.php';
}
?>