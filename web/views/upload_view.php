<a href="index.php" class="position-fixed top-0 end-0 m-2">
    <button class="btn btn-primary">Wróć</button>
</a>

<h1 class="text-center mt-5">Dodaj grę do biblioteki</h1>
<p class="text-center">wypełnij poniższy formularz i prześlij</p>

<form 
    method="post" 
    enctype="multipart/form-data" 
    class="container d-flex flex-column gap-3 mt-5" 
    style="background-color: white; max-width: 600px;"
    >
    <div class="d-flex flex-column gap-3">
        <input type="text" name="author" id="author" placeholder="Autor" class="form-control">
        <input type="text" name="title" id="title" placeholder="Tytuł" class="form-control">
        <div class="d-flex flex-column">
            <input type="file" name="photo" id="fileInput" class="form-control">
            <label for="fileInput" class="text-start" style="color: #000000c1; font-size: 12px">
                Powinno być w formacie JPG lub PNG oraz nie przekraczać 1MB
            </label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Wyślij</button>
</form>