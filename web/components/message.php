<?php
function showMessage($data){
    $success = $data['success'];
    $messages = $data['messages'];
    
    $messagesString = implode("<br>", $messages);

    $alertClass = $success ? 'alert-success' : 'alert-danger';
    $icon = $success ? '✅' : '❌';
    echo "
        <div class='alert $alertClass position-fixed bottom-0 start-50 
            translate-middle-x mb-5 d-flex align-items-center gap-3' 
            style='width:90%;max-width: 400px;'
            id='flash-message'
            role='alert'>
            <span>$icon</span>$messagesString
        </div>";
}
?>