<?php
include($GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php");
include($GLOBALS['PROJECT_ROOT_DIR'] . "/converter.php");
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
		header("Location: {$GLOBALS['PROJECT_ROOT']}/admin/blocked.php");
		exit();
	}
	$_SESSION['isAdmin'] = $isAdmin;
} else {
	header("Location: {$GLOBALS['PROJECT_ROOT']}/account/login.php");
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
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/mainStyle.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/favicon.ico" rel="icon">
</head>

<body>

<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<?php
		$activePage = 'admin';
		include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/sidebar.php");
		?>
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">

			<!-- Admin Navigation Bar -->
			<nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
				<div class="container-fluid">
					<ul class="navbar-nav">
						<li class="nav-item"><a class="nav-link"
												href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/songs.php">View</a>
						</li>
						<li class="nav-item"><a class="nav-link active"
												href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/song.php">Add
								content</a></li>
					</ul>
				</div>
			</nav>

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/song.php">Song</a>
					</li>
					<li class="nav-item"><a class="nav-link"
											href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/artist.php">Artist</a></li>
					<li class="nav-item"><a class="nav-link" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/user.php">User</a>
					</li>
					<li class="nav-item"><a class="nav-link"
											href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/playlist.php">Playlist</a>
					</li>
					<li class="nav-item"><a class="nav-link active" href="">Album</a></li>
				</ul>
			</div>

			<?php
			require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/AlbumController.php";
			require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/ArtistController.php";
			require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php";
			require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/UserController.php";
			$artistList = ArtistController::getArtistList();
			$songList = SongController::getSongList();

			$imageName = "";
			$thumbnailName = "";
			$isValid = true;

			if (!(!empty($_POST["nameInput"]) && !empty($_POST["artistInput"]) && !empty($_POST["songInput"]))) {
				$isValid = false;
			}

			if ($isValid && isset($_FILES['albumImageInput']) && $_FILES['albumImageInput']['error'] === UPLOAD_ERR_OK) {
				$imageResult = Converter::uploadImage($_FILES['albumImageInput'], ImageType::ALBUM);

				if ($imageResult['success']) {
					$imageName = $imageResult['large_filename'];
					$thumbnailName = $imageResult['thumbnail_filename'];
					$originalImageName = $imageResult['original_filename'];
				} else {
					$isValid = false;
					$errorMessage = $imageResult['error'];
				}
			}

			if ($isValid) {
				$totalMilliSeconds = 0;

				foreach ($_POST['songInput'] as $selectedSongID) {
					$totalMilliSeconds += SongController::getSongByID($selectedSongID)->getSongLength();
				}

				// Set album length to number of songs
				$albumLength = count($_POST["songInput"]);

				$artistNames = [];
				foreach ($_POST['artistInput'] as $selectedArtistID) {
					$artistNames[] = ArtistController::getArtistByID($selectedArtistID)->getName();
				}

				$releaseDate = $_POST['releaseDateInput'] ?? date('Y-m-d');
				$isSingle = isset($_POST['isSingleInput']);

				try {
					AlbumController::insertAlbum(new Album(
							0,
							$_POST["nameInput"],
							$_POST["songInput"],
							$artistNames,
							$_POST["artistInput"],
							$imageName,
							$thumbnailName,
							$albumLength,
							$totalMilliSeconds,
							$releaseDate,
							$isSingle,
							$originalImageName ?? ""
					));
					$successMessage = "Album successfully added!";
				} catch (Exception $e) {
					$errorMessage = "Error: " . $e->getMessage();
				}
			}
			?>
			<div class="container mt-5">
				<h1>Add Album</h1>
				<form action="album.php" method="post" id="addAlbumForm" enctype="multipart/form-data">
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
										echo "<option value='{$artist->getArtistID()}'>" . $artist->getName() . " (" . UserController::getUserById($artist->getUserID())->getUsername() . ")" . "</option>";
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
										echo "<option value='{$song->getSongID()}'>{$song->getTitle()} - " . implode(", ", $song->getArtists()) . "</option>";
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

					<div class="form-group">
						<label for="releaseDateInput">Release Date:</label>
						<input type="date" id="releaseDateInput" name="releaseDateInput" class="form-control" required>
					</div>

					<div class="form-group">
						<label for="isSingleInput">Is this a single?</label>
						<input type="checkbox" id="isSingleInput" name="isSingleInput" value="1">
					</div>

					<input type="submit" class="btn btn-primary mt-3" value="Submit">
				</form>
			</div>
	</div>


	<script>
		function updateRemoveButtons() {
			const fields = document.querySelectorAll('#artistFields .artist-field');
			fields.forEach((field) => {
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
