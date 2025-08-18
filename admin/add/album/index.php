<?php
include("../../../dbConnection.php");
session_start();
$isAdmin = false;
if (isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true) {
	$stmt = DBConn::getConn()->prepare("SELECT isAdmin FROM user WHERE userID = ?;");
	$stmt->bind_param("i", $_SESSION['userID']);
	$stmt->execute();
	$isAdmin = $stmt->get_result()->fetch_assoc()['isAdmin'] ?? false;
	$stmt->close();
	if (!$isAdmin) {
		$_SESSION['isAdmin'] = $isAdmin;
		header("Location: ../../blocked.php");
		exit();
	}
	$_SESSION['isAdmin'] = $isAdmin;
} else {
	header("Location: ../../../account/login.php");
	exit();
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - add an album</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../../../mainStyle.css" rel="stylesheet">
	<link href="../../../favicon.ico" rel="icon">
</head>

<body>

<?php include("../../../topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../../../" class="nav-link mb-2">Home</a>
				<a href="../../../search/" class="nav-link mb-2">Search</a>
				<a href="../../../discover/" class="nav-link mb-2">Discover</a>
				<a href="/BeatStream/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin/" class="nav-link mb-2 active">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">

			<!-- Admin Navigation Bar -->
			<nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
				<div class="container-fluid">
					<ul class="navbar-nav">
						<li class="nav-item"><a class="nav-link" href="../../view/songs">View</a></li>
						<li class="nav-item"><a class="nav-link active" href="../../add/song">Add content</a></li>
					</ul>
				</div>
			</nav>

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link" href="../song">Song</a></li>
					<li class="nav-item"><a class="nav-link" href="../artist">Artist</a></li>
					<li class="nav-item"><a class="nav-link" href="../user">User</a></li>
					<li class="nav-item"><a class="nav-link" href="../playlist">Playlist</a></li>
					<li class="nav-item"><a class="nav-link active" href="">Album</a></li>
				</ul>
			</div>

			<?php
			include("../../../DataController.php");
			$artistList = DataController::getArtistList();
			$songList = DataController::getSongList();

			$imageUploadDir = "../../../../BeatStream/images/album/";
			$songimageName = "";
			$newimageName = "";

			$isValid = true;

			if (!(
				!empty($_POST["nameInput"]) && !empty($_POST["artistInput"]) && !empty($_POST["songInput"])
			)) {
				$isValid = false;
			}

			if ($isValid && isset($_FILES['albumImageInput']) && $_FILES['albumImageInput']['error'] === UPLOAD_ERR_OK) {
				$fileTmpPath = $_FILES['albumImageInput']['tmp_name'];
				$fileName = $_FILES['albumImageInput']['name'];
				$newimageName = uniqid() . "." . pathinfo($fileName, PATHINFO_EXTENSION);
				$destPath = $imageUploadDir . $newimageName;

				if (!is_dir($imageUploadDir)) {
					mkdir($imageUploadDir, 0777, true);
				}

				if (move_uploaded_file($fileTmpPath, $destPath)) {
					$songimageName = $imageUploadDir . $newimageName;
					$_FILES["imageFileInput"] = $songimageName;
				} else {
					$isValid = false;
					$errorMessage = "Image upload failed";
				}
			}

			if ($isValid) {
				$totalSeconds = 0;

				foreach ($_POST['songInput'] as $selectedSongID) {
					foreach ($songList as $song) {
						if ($song->getSongID() == $selectedSongID) {
							$timeparts = explode(':', $song->getSongLength()->format('i:s'));
							$seconds = $timeparts[0] * 60 + $timeparts[1];
							$totalSeconds += $seconds;
							break;
						}
					}
				}

				// Format total duration
				$hours = floor($totalSeconds / 3600);
				$minutes = floor(($totalSeconds % 3600) / 60);
				$seconds = $totalSeconds % 60;
				$formattedDuration = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

				// Set album length to number of songs
				$albumLength = count($_POST["songInput"]);

				try {
					DataController::insertAlbum(new Album(
						0, // Random ID will be generated in DataController
						$_POST["nameInput"],
						$_POST["songInput"],
						$_POST["artistInput"],
						$newimageName,
						$albumLength,
						$formattedDuration
					));
					$successMessage = "Album successfully added!";
				} catch (Exception $e) {
					$errorMessage = "Error: " . $e->getMessage();
				}
			}
			?>

			<div class="container mt-5">
				<h1>Album Einfügen</h1>

				<form action="index.php" method="post" id="addAlbumForm" enctype="multipart/form-data">
					<div class="form-group">
						<label for="name">Album title:</label>
						<input type="text" id="name" name="nameInput" class="form-control"
							   placeholder="Enter album title" required>
					</div>

					<div class="form-group">
						<label for="artist">Artists:</label>
						<div id="artistFields">
							<div class="artist-field d-flex mb-2">
								<select name="artistInput[]" class="form-control me-2" required>
									<option value="">--Please Select--</option>
									<?php
									foreach ($artistList as $artist) {
										echo "<option value='{$artist->getName()}'>{$artist->getName()}</option>";
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


					<div class="form-group">
						<label for="song">Songs:</label>
						<div id="songFields">
							<div class="song-field d-flex mb-2">
								<select name="songInput[]" class="form-control me-2" required>
									<option value="">--Please Select--</option>
									<?php
									foreach ($songList as $song) {
										echo "<option value='{$song->getSongID()}'>{$song->getTitle()} - {$song->getArtists()}</option>";
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
						<label for="albumImage">Image:</label>
						<input type="file" id="albumImage" name="albumImageInput" class="form-control" accept="image/*"
							   required>
					</div>

					<input type="submit" class="btn btn-primary mt-3" value="Submit">
				</form>
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

	<!-- Bootstrap JS (optional for some interactive components) -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</div>
</body>

</html>