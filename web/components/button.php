<?php

function renderButton($label, $url, $variant='outline-primary', $classes = '') {
    echo '<a href="' . htmlspecialchars($url) . '" 
             class="btn btn-'.$variant.' rounded-pill px-3 text-decoration-none d-flex align-items-center '.$variant.'">' 
             . htmlspecialchars($label) . 
         '</a>';
}

?>