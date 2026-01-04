<h1 class="text-center mt-5">Zaloguj się</h1>

<form 
    method="post" 
    enctype="multipart/form-data" 
    class="container d-flex flex-column gap-3 mt-5 max" 
    style="background-color: white; max-width: 400px;"
    >
    <div class="d-flex flex-column gap-3">
        <input type="text" name="login" id="login" placeholder="Login" class="form-control">
        <input type="text" name="password" id="password" placeholder="Hasło" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Zaloguj</button>
    <div>Nie masz konta? <a href="index.php?action=register">Zarejestruj się</a></div>
</form>