<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Songs</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="container-fluid">
			<div class="collapse navbar-collapse myNavbar">
				<ul class="navbar-nav">
					<li class="nav-item"><a class="nav-link" href="../../">Home</a></li>
					<li class="nav-item"><a class="nav-link" href="../song">Add Songs</a></li>
					<li class="nav-item"><a class="nav-link" href="../artist">Add Artist</a></li>
					<li class="nav-item"><a class="nav-link" href="../user">Add User</a></li>
					<li class="nav-item"><a class="nav-link" href="../playlist">Add Playlist</a></li>
					<li class="nav-item"><a class="nav-link" href="../album">Add Album</a></li>
				</ul>
			</div>
		</div>
	</nav>

	<?php
	include("../../SongController.php");
	include("../../Objects/Album.php");
	$artistList = SongController::getArtistList();

	$isValid = true;

	if (!(
		!empty($_POST["nameInput"]) && !empty($_POST["artistsInput"]) && !empty($_POST["lengthInput"]) && !empty($_POST["durationInput"]) && !empty($_POST["imagePathInput"])
	)) {
		$isValid = false;
	}

	if ($isValid) {
		SongController::insertAlbum(new Album(
			"",
			$_POST["nameInput"],
			$_POST["artistsInput"],
			$_POST["imagePathInput"],
			$_POST["lengthInput"],
			$_POST["durationInput"]
		));
	}
	?>

	<div class="container mt-5">
		<h1>Album Einfügen</h1>

		<form action="index.php" method="post" id="addAlbumForm">
			<div class="form-group">
				<label for="name">Albumtitel:</label>
				<input type="text" id="name" name="nameInput" class="form-control" placeholder="Enter album title">
			</div>
			<div class="form-group">
				<label for="artists">Artist:</label>
				<select name="artistsInput" id="artists" class="form-control" style="width: 100%;">
					<option value="none">--Please Select--</option>
					<?php
					for ($i = 0; $i < count($artistList); $i++) {
					?>
						<option value='<?php echo $artistList[$i]->getName() ?>'><?php echo $artistList[$i]->getName() ?></option>
					<?php
					}
					?>
				</select>
			</div>
			<div class="form-group">
				<label for="length">Länge:</label>
				<input type="number" id="length" name="lengthInput" class="form-control" placeholder="Enter length in minutes">
			</div>
			<div class="form-group">
				<label for="duration">Dauer:</label>
				<input type="time" id="duration" name="durationInput" class="form-control" step="1">
			</div>
			<div class="form-group">
				<label for="imagePath">Image path:</label>
				<input type="text" id="imagePath" name="imagePathInput" class="form-control" placeholder="Enter image path">
			</div>
			<button type="submit" class="btn btn-primary mt-3">Submit</button>
		</form>
	</div>

	<!-- Bootstrap JS (optional for some interactive components) -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>