<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moje gry</title>
    <link rel="stylesheet" href="static/style.css"> 
    <link href="static/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php 
    // renderowanie navbara
    include "components/navBar.php";

    // renderowanie widoku
    include $view; 
    
    // wyświetlanie message jeżeli jest wiadomość
    require_once "components/message.php";
    if(!empty($message)){
        showMessage($message);
    }; 
    ?>
    <script src="static/script.js"></script>
</body>
</html>