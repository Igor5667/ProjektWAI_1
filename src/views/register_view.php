<h1 class="text-center mt-5">Zarejestruj się</h1>

<form 
    method="post" 
    enctype="multipart/form-data" 
    class="container d-flex flex-column gap-3 mt-5" 
    style="background-color: white; max-width: 400px;"
    >
    <div class="d-flex flex-column gap-3">
        <input type="text" name="email" id="email" placeholder="E-mail" class="form-control">
        <input type="text" name="login" id="login" placeholder="Login" class="form-control">
        <input type="password" name="password" id="password" placeholder="Hasło" class="form-control">
        <input type="password" name="password-confirmation" id="password-confirmation" placeholder="Powtórz hasło" class="form-control">
        <div class="d-flex flex-column">
            <label for="fileInput" class="text-start" style="color: #000000c1; font-size: 12px">
                Dodaj zdjęcie profilowe:
            </label>
            <input type="file" name="photo" id="fileInput" class="form-control">
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Zarejestruj się</button>
    <div>Masz już konto? <a href="front_controller.php?action=login">Zaloguj się</a></div>
</form>