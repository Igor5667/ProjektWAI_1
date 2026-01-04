<nav class="d-flex justify-content-end gap-2 p-2 position-fixed w-100 top-0 z-3" style="background: rgba(255,255,255,0.9); border-bottom: 1px solid #ccc;">
    
    <?php if($action != 'library'): ?>
    <a href="index.php">
        <button class="btn btn-primary rounded-pill px-3 h-100">Wróć</button>
    </a>
    <?php endif; ?>

    <a href="index.php?action=upload">
        <button class="btn btn-primary rounded-pill px-3 h-100">Dodaj zdjęcie</button>
    </a>

    <?php if (!isset($_SESSION['user_id'])): ?>
        
        <a href="index.php?action=login">
            <button class="btn btn-primary rounded-pill px-3 h-100">Zaloguj się</button>
        </a>

    <?php else: ?>

        <div class="d-flex align-items-center border rounded-pill ps-1 pe-3 py-1 bg-light border border-2 border-primary">

            <?php if (!empty($_SESSION['user_photo'])): ?>
                <img src="<?= htmlspecialchars($_SESSION['user_photo']) ?>" 
                     alt="Avatar" 
                     class="rounded-circle"
                     style="width: 32px; height: 32px; object-fit: cover;">
            <?php endif; ?>

            <span class="fw-bold text-dark ps-2">
                Witaj 
                <?= htmlspecialchars($_SESSION['user_login']) ?>!
            </span>

            
        </div>

        <a href="index.php?action=logout" class="text-decoration-none">
                <button class="btn btn-sm btn-outline-danger rounded-pill h-100 px-3 border-2 fw-bold">Wyloguj</button>
        </a>
    <?php endif; ?>
</nav>
<!-- Placeholder pod navbarem -->
<div style="height: 50px;"></div>