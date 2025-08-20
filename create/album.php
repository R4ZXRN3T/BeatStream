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
include("../DataController.php");

// Get the current user's artist ID
$stmt = DBConn::getConn()->prepare("SELECT artistID, name FROM artist WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$artistResult = $stmt->get_result();
$currentArtist = $artistResult->fetch_assoc();
$currentArtistID = $currentArtist['artistID'];
$currentArtistName = $currentArtist['name'];

// Get all artists for selection
$artistList = DataController::getArtistList();

// Get only songs by the current artist
$stmt = DBConn::getConn()->prepare("
        SELECT song.songID, song.title, song.genre, song.releaseDate, song.imageName, song.songLength, song.fileName 
        FROM song 
        JOIN releases_song ON song.songID = releases_song.songID 
        WHERE releases_song.artistID = ?
        ORDER BY song.title");
$stmt->bind_param("i", $currentArtistID);
$stmt->execute();
$songResult = $stmt->get_result();

$artistSongs = array();
while ($row = $songResult->fetch_assoc()) {
	$artistSongs[] = new Song(
		$row["songID"],
		$row["title"],
		$currentArtistName,
		$row["genre"],
		$row["releaseDate"],
		$row["songLength"],
		$row["fileName"],
		$row["imageName"]
	);
}

$isValid = true;

if (isset($_POST['albumName']) && isset($_POST['songInput']) && isset($_POST['artistInput'])) {
	$uploadDir = "../images/album/";
	$finalFileName = "";
	if (isset($_FILES['imageFileInput']) && $_FILES['imageFileInput']['error'] == UPLOAD_ERR_OK) {
		$imageFile = $_FILES['imageFileInput'];
		$extension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
		$finalFileName = uniqid() . '.' . $extension;
		$imageName = $uploadDir . $finalFileName;
		if (!move_uploaded_file($imageFile['tmp_name'], $imageName)) {
			echo "<div class='alert alert-danger'>Failed to upload image.</div>";
			$isValid = false;
		}
	}

	$totalDuration = new DateTime("00:00:00");
	foreach ($_POST['songInput'] as $selectedSongID) {
		foreach ($artistSongs as $song) {
			if ($song->getSongID() == $selectedSongID) {
				$duration = $song->getSongLength();
				$hours = (int)$duration->format('H');
				$minutes = (int)$duration->format('i');
				$seconds = (int)$duration->format('s');
				$interval = new DateInterval("PT{$hours}H{$minutes}M{$seconds}S");
				$totalDuration->add($interval);
				break;
			}
		}
	}

	// Get artist names for selected artist IDs
	$artistNames = [];
	foreach ($_POST['artistInput'] as $artistID) {
		foreach ($artistList as $artist) {
			if ($artist->getArtistID() == $artistID) {
				$artistNames[] = $artist->getName();
				break;
			}
		}
	}

	DataController::insertAlbum(new Album(
		0,
		$_POST['albumName'],
		$_POST['songInput'],
		$artistNames,
		$finalFileName,
		count($_POST['songInput']),
		$totalDuration->format('H:i:s')
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
					<a href="/BeatStream/admin" class="nav-link mb-2">Admin</a>
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