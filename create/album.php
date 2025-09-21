<?php
include("../dbConnection.php");
session_start();
$userID = $_SESSION['userID'];
$stmt = DBConn::getConn()->prepare("SELECT isArtist FROM user WHERE userID = $userID;");
$stmt->execute();
$result = $stmt->get_result();
if (!(isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true && $result->fetch_assoc()['isArtist'])) {
	header("Location: ../account/login.php");
	exit();
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - create an album</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
	<link href="../mainStyle.css" rel="stylesheet">
</head>

<body>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/AlbumController.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/ArtistController.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/SongController.php";

// Get the current user's artist ID
$stmt = DBConn::getConn()->prepare("SELECT artistID, name FROM artist WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$artistResult = $stmt->get_result();
$currentArtist = $artistResult->fetch_assoc();
$currentArtistID = $currentArtist['artistID'];
$currentArtistName = $currentArtist['name'];

// Get all artists for selection
$artistList = ArtistController::getArtistList();

// Get only songs by the current artist
$artistSongs = SongController::getArtistSongs($currentArtistID);

if (isset($_POST['albumName']) && isset($_POST['songInput']) && isset($_POST['artistInput'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/converter.php";

	$imageName = "";
	$thumbnailName = "";

	if (isset($_FILES['imageFileInput']) && $_FILES['imageFileInput']['error'] === UPLOAD_ERR_OK) {
		$imageResult = Converter::uploadImage($_FILES['imageFileInput'], ImageType::ALBUM);

		if ($imageResult['success']) {
			$imageName = $imageResult['large_filename'];
			$thumbnailName = $imageResult['thumbnail_filename'];
		} else {
			$isValid = false;
			$errorMessage = $imageResult['error'];
		}
	}

	$totalMilliSeconds = 0;
	foreach ($_POST['songInput'] as $selectedSongID) {
		$totalMilliSeconds += SongController::getSongByID($selectedSongID)->getSongLength();
	}

	// Get artist IDs for selected artists
	$selectedArtistIDs = $_POST['artistInput'];
	$artistNames = [];
	foreach ($selectedArtistIDs as $artistID) {
		$artistNames[] = ArtistController::getArtistByID($artistID)->getName();
	}

	$releaseDate = $_POST['releaseDateInput'] ?? date('Y-m-d');
	$isSingle = isset($_POST['isSingleInput']);

	AlbumController::insertAlbum(new Album(
			0,
			$_POST['albumName'],
			$_POST['songInput'],
			$artistNames,
			$selectedArtistIDs,
			$imageName,
			$thumbnailName,
			count($_POST['songInput']),
			$totalMilliSeconds,
			$releaseDate,
			$isSingle
	));
	echo "<div class='alert alert-success'>Album created successfully!</div>";
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
					<a href="/BeatStream/admin/" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="main col-md" style="min-height: 80vh; margin-left: 150px; padding: 2rem;">

			<div class="container" style="max-width: 1700px;">
				<h1 class="mb-4">Create an Album</h1>
				<form action="album.php" method="post" enctype="multipart/form-data">
					<div class="mb-3">
						<label for="albumName" class="form-label">Album Name:</label>
						<input type="text" class="form-control" id="albumName" name="albumName" required>
					</div>

					<div class="form-group">
						<label for="artist">Artists:</label>
						<div id="artistFields">
							<div class="artist-field d-flex mb-2">
								<select name="artistInput[]" class="form-control me-2" required>
									<option value="">--Please Select--</option>
									<?php
									foreach ($artistList as $artist) {
										echo "<option value='{$artist->getArtistID()}'>{$artist->getName()}</option>";
									}
									?>
								</select>
								<button type="button" class="btn btn-danger remove-artist" style="display:none;"
										onclick="removeArtist(this)">-
								</button>
							</div>
						</div>
						<button type="button" onclick="addArtist()" class="btn btn-info mt-2">+</button>
					</div>

					<div class="form-group mb-3">
						<label for="songFields">Add Songs:</label>
						<div id="songFields">
							<div class="song-field d-flex mb-2">
								<select name="songInput[]" class="form-control me-2" required>
									<option value="">--Please Select--</option>
									<?php
									foreach ($artistSongs as $song) {
										echo "<option value={$song->getSongID()}>{$song->getTitle()}</option>";
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

					<div class="form-group mb-3">
						<label for="imageFile">Album Cover Image:</label>
						<input type="file" id="imageFile" name="imageFileInput" class="form-control" accept="image/*"
							   required>
					</div>

					<div class="mb-3">
						<label for="releaseDateInput" class="form-label">Release Date:</label>
						<input type="date" class="form-control" id="releaseDateInput" name="releaseDateInput" required>
					</div>

					<div class="mb-3">
						<label for="isSingleInput" class="form-label">Is this a single?</label>
						<input type="checkbox" id="isSingleInput" name="isSingleInput" value="1">
					</div>

					<input type="submit" class="btn btn-primary mt-3" value="Create Album">
				</form>
			</div>
		</main>
	</div>
</div>
<script>
	function updateRemoveButtons() {
		const fields = document.querySelectorAll('#artistFields .artist-field');
		fields.forEach((field, idx) => {
			const btn = field.querySelector('.remove-artist');
			btn.style.display = (fields.length > 1) ? 'inline-block' : 'none';
		});
	}

	function addArtist() {
		const artistFields = document.getElementById('artistFields');
		const firstField = artistFields.querySelector('.artist-field');
		const newField = firstField.cloneNode(true);
		newField.querySelector('select').value = '';
		artistFields.appendChild(newField);
		updateRemoveButtons();
	}

	function removeArtist(btn) {
		btn.closest('.artist-field').remove();
		updateRemoveButtons();
	}

	document.addEventListener('DOMContentLoaded', updateRemoveButtons);

	function updateSongRemoveButtons() {
		const fields = document.querySelectorAll('#songFields .song-field');
		fields.forEach((field) => {
			const btn = field.querySelector('.remove-song');
			btn.style.display = (fields.length > 1) ? 'inline-block' : 'none';
		});
	}

	function addSong() {
		const songFields = document.getElementById('songFields');
		const firstField = songFields.querySelector('.song-field');
		const newField = firstField.cloneNode(true);
		newField.querySelector('select').value = '';
		songFields.appendChild(newField);
		updateSongRemoveButtons();
	}

	function removeSong(btn) {
		btn.closest('.song-field').remove();
		updateSongRemoveButtons();
	}

	document.addEventListener('DOMContentLoaded', function () {
		updateRemoveButtons();
		updateSongRemoveButtons();
	});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>