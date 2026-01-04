<?php require_once "button.php" ?>
<nav class="d-flex justify-content-end gap-2 p-2 position-fixed w-100 top-0 z-3" style="background: rgba(255,255,255,0.9); border-bottom: 1px solid #ccc;">
    
    <?php 
    // 1. Przycisk WRÓĆ
    if($currentLocation != 'library'): 
        renderButton("Wróć", "index.php");
    endif; 
    ?>

    <!-- spacer -->
    <div class="flex-grow-1"></div>

    <?php 
    renderButton("Dodaj grę", "index.php?action=upload");
    ?>

    <?php 
    if (!$isUserLogged):
        if($currentLocation == 'login'){
            renderButton("Zarejestruj się", "index.php?action=register", "primary");
        }
        else{
            renderButton("Zaloguj się", "index.php?action=login", "primary");
        }
    else: ?>

        <div class="d-flex align-items-center border rounded-pill ps-1 pe-3 py-1 border border-primary">
            <?php if(!empty($_SESSION['user_photo'])): ?>
                <img src="<?= htmlspecialchars($_SESSION['user_photo']) ?>" 
                     alt="Avatar" 
                     class="rounded-circle"
                     style="width: 32px; height: 32px; object-fit: cover;">
            <?php endif; ?>

            <span class="text-dark ps-2">
                Witaj 
                <?= htmlspecialchars($_SESSION['user_login']) ?>!
            </span>
        </div>

        <?php 
        renderButton("Wyloguj", "index.php?action=logout", "outline-danger");
        ?>
    <?php endif; ?>
</nav>
<!-- Placeholder pod navbarem -->
<div style="height: 50px;"></div>