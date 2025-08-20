<?php
session_start();
?>

<!Doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - create a playlist</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
	<link href="../mainStyle.css" rel="stylesheet">
</head>

<body>
<?php
include("../DataController.php");
$songList = DataController::getSongList();

$isValid = true;

if (!isset($_POST['playlistName']) && !isset($_POST['songInput'])) {
	$isValid = false;
}

if ($isValid) {
	$uploadDir = "../images/playlist/";
	$finalFileName = "";
	if (isset($_FILES['imageFileInput']) && $_FILES['imageFileInput']['error'] == UPLOAD_ERR_OK) {
		$imageFile = $_FILES['imageFileInput'];
		$extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
		$finalFileName = uniqid() . '.' . $extension;
		$imageName = $uploadDir . $finalFileName;
		if (!move_uploaded_file($imageFile['tmp_name'], $imageName)) {
			echo "<div class='alert alert-danger'>Failed to upload image.</div>";
			$isValid = false;
		}
	} else {
		$imageName = ""; // No image uploaded
	}
	$totalDuration = new DateTime("00:00:00");
	foreach ($_POST['songInput'] as $selectedSongID) {
		foreach ($songList as $song) {
			if ($song->getSongID() == $selectedSongID) {
				$duration = $song->getSongLength(); // Assuming this returns a DateInterval or DateTime
				$totalDuration->add(new DateInterval('PT' . $duration->format('s') . 'S'));
				break;
			}
		}
	}

	DataController::insertPlaylist(new Playlist(
		0,
		$_POST['playlistName'],
		$_POST['songInput'],
		$totalDuration->format('H:i:s'),
		count($_POST['songInput']),
		$finalFileName,
		$_SESSION['userID']
	));
}

include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2">Home</a>
				<a href="../search/" class="nav-link mb-2">Search</a>
				<a href="../discover/" class="nav-link mb-2">Discover</a>
				<a href="/BeatStream/create/" class="nav-link mb-2 active">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="main col-md" style="min-height: 80vh; margin-left: 150px; padding: 2rem;">

			<div class="container" style="max-width: 1700px;">
				<h1 class="mb-4">Create a Playlist</h1>
				<form action="playlist.php" method="post" enctype="multipart/form-data">
					<div class="mb-3">
						<label for="playlistName" class="form-label">Playlist Name:</label>
						<input type="text" class="form-control" id="playlistName" name="playlistName" required>
					</div>
					<div class="form-group">
						<label for="songFields">Add Songs:</label>
						<div id="songFields">
							<div class="song-field d-flex mb-2">
								<select name="songInput[]" class="form-control me-2" required>
									<option value="">--Please Select--</option>
									<?php
									foreach ($songList as $song) {
										echo "<option value={$song->getSongID()}>{$song->getTitle()} - " . implode(", ", $song->getArtists()) . "</option>";
									}
									?>
								</select>
								<button type="button" class="btn btn-danger remove-song" style="display:none;"
										onclick="removeSong(this)">-
								</button>
							</div>
						</div>
						<button type="button" onclick="addSong()" class="btn btn-info mt-2">+</button>
					</div>

					<div class="form-group">
						<label for="playlistImage">Image:&nbsp;&nbsp;&nbsp;&nbsp;(not required)</label>
						<input type="file" id="imageFile" name="imageFileInput" class="form-control" accept="image/*"
							   placeholder="Upload an image">
					</div>
					<input type="submit" class="btn btn-primary mt-3" value="Create Playlist">
				</form>
			</div>
		</main>
	</div>
</div>
<script>
	function updateRemoveButtons() {
		const fields = document.querySelectorAll('#songFields .song-field');
		fields.forEach((field, idx) => {
			const btn = field.querySelector('.remove-song');
			btn.style.display = (fields.length > 1) ? 'inline-block' : 'none';
		});
	}

	function addSong() {
		const artistFields = document.getElementById('songFields');
		const firstField = artistFields.querySelector('.song-field');
		const newField = firstField.cloneNode(true);
		newField.querySelector('select').value = '';
		artistFields.appendChild(newField);
		updateRemoveButtons();
	}

	function removeSong(btn) {
		btn.closest('.song-field').remove();
		updateRemoveButtons();
	}

	document.addEventListener('DOMContentLoaded', updateRemoveButtons);
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
