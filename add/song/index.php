<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeatStream - add a song</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../addStyle.css" rel="stylesheet">
    <link href="../../favicon.ico" rel="icon">
</head>

<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <div class="collapse navbar-collapse myNavbar">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="../../">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="../">Add content</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="tab">
    <ul class="nav nav-tabs justify-content-center">
        <li class="nav-item"><a class="nav-link active" href="../song">Song</a></li>
        <li class="nav-item"><a class="nav-link" href="../artist">Artist</a></li>
        <li class="nav-item"><a class="nav-link" href="../user">User</a></li>
        <li class="nav-item"><a class="nav-link" href="../playlist">Playlist</a></li>
        <li class="nav-item"><a class="nav-link" href="../album">Album</a></li>
    </ul>
</div>

<?php
include("../../SongController.php");
$artistList = SongController::getArtistList();
$isValid = true;

// Check if all fields are filled out
if (!(
	!empty($_POST["titleInput"]) && !empty($_POST["artistInput"]) && !empty($_POST["genreInput"]) && !empty($_POST["releaseDateInput"]) && !empty($_POST["ratingInput"]) && !empty($_POST["songLengthInput"]) && !empty($_POST["filePathInput"]) && !empty($_POST["imagePathInput"])
)) {
	$isValid = false;
}

if ($isValid) {
	SongController::insertSong(new Song(
		"",
		$_POST["titleInput"],
		$_POST["artistInput"],
		$_POST["genreInput"],
		$_POST["releaseDateInput"],
		$_POST["ratingInput"],
		$_POST["songLengthInput"],
		$_POST["filePathInput"],
		$_POST["imagePathInput"]
	));
}
?>

<!-- Song Form -->
<div class="container mt-5">
    <h1>Add song</h1>

    <form action="index.php" method="post" id="addSongForm">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="titleInput" class="form-control" placeholder="Enter song title" required>
        </div>

        <div class="form-group">
            <label for="artist">Artist:</label>
            <select name="artistInput" id="artist" class="form-control" required>
                <option value="">--Please Select--</option>
				<?php
				foreach ($artistList as $artist) {
					echo "<option value='{$artist->getName()}'>{$artist->getName()}</option>";
				}
				?>
            </select>
            <button type="button" onclick="addArtist()" class="btn btn-info mt-2">+</button>
        </div>

        <div class="form-group">
            <label for="genre">Genre:</label>
            <input type="text" id="genre" name="genreInput" class="form-control" placeholder="Enter genre" required>
        </div>

        <div class="form-group">
            <label for="releaseDate">Release Date:</label>
            <input type="date" id="releaseDate" name="releaseDateInput" class="form-control" placeholder="Enter release date" required>
        </div>

        <div class="form-group">
            <label for="rating">Rating:</label>
            <input type="number" id="rating" name="ratingInput" class="form-control" step="0.1" min="1" max="5" placeholder="Enter rating (1.0 - 5.0)" required>
        </div>

        <div class="form-group">
            <label for="songLength">Song Length:</label>
            <input type="time" id="songLength" name="songLengthInput" class="form-control" step="1" placeholder="Enter song length" required>
        </div>

        <div class="form-group">
            <label for="filePath">File Path:</label>
            <input type="text" id="filePath" name="filePathInput" class="form-control" placeholder="Enter file path" required>
        </div>

        <div class="form-group">
            <label for="imagePath">Image Path:</label>
            <input type="text" id="imagePath" name="imagePathInput" class="form-control" placeholder="Enter image path" required>
        </div>

        <input type="submit" class="btn btn-primary mt-3" value="Submit">
    </form>
</div>

<script>
    // JavaScript to handle adding a new artist
    function addArtist() {

        let newArtist = prompt("Enter the name of the new artist:");

        if (newArtist) {
            let select = document.getElementById("artist");
            let option = document.createElement("option");
            option.value = newArtist;
            option.text = newArtist;
            select.appendChild(option);
            select.value = newArtist; // Automatically select the new artist
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>