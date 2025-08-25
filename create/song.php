<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/BeatStream/controller/DBConn.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/BeatStream/controller/SongController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/BeatStream/controller/ArtistController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/BeatStream/converter.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/BeatStream/Objects/Song.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION['account_loggedin']) || $_SESSION['account_loggedin'] !== true) {
	header("Location: ../account/login.php");
	exit();
}

// Check if user is an artist
$userID = $_SESSION['userID'];
$stmt = DBConn::getConn()->prepare("SELECT isArtist FROM user WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$isArtist = $result->fetch_assoc()['isArtist'] ?? false;
$stmt->close();

if (!$isArtist) {
	header("Location: ../artist/become_artist.php");
	exit();
}

// Get current user's artist info
$stmt = DBConn::getConn()->prepare("SELECT artistID, name FROM artist WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$currentArtist = $result->fetch_assoc();
$stmt->close();

// Get all artists for dropdown
$artistList = ArtistController::getArtistList();

// Process form submission
$isValid = true;
$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!(!empty($_POST["titleInput"]) && !empty($_POST["artistInput"][0]) && !empty($_POST["genreInput"]) &&
			!empty($_POST["releaseDateInput"]) && !empty($_FILES["fileInput"]))) {
		$isValid = false;
		$errorMessage = "Please fill all required fields";
	}

	$imageName = "";
	$thumbnailName = "";
	$flacFileName = "";
	$opusFileName = "";
	$duration = 0;

	// Handle image upload if provided
	if ($isValid && isset($_FILES['songImageInput']) && $_FILES['songImageInput']['error'] === UPLOAD_ERR_OK) {
		$imageResult = Converter::uploadImage($_FILES['songImageInput'], ImageType::SONG);
		if ($imageResult['success']) {
			$imageName = $imageResult['large_filename'];
			$thumbnailName = $imageResult['thumbnail_filename'];
		} else {
			$isValid = false;
			$errorMessage = "Image upload failed: " . $imageResult['error'];
		}
	}

	// Handle audio upload
	if ($isValid) {
		$audioResult = Converter::uploadAudio($_FILES['fileInput']);

		if ($audioResult['success']) {
			$flacFileName = $audioResult['flac_filename'];
			$opusFileName = $audioResult['opus_filename'];
			$duration = $audioResult['duration'];

			// Show warning if lossy format was uploaded
			if (isset($audioResult['warning'])) {
				$errorMessage = $audioResult['warning'];
			}
		} else {
			$isValid = false;
			$errorMessage = "Audio upload failed: " . $audioResult['error'];
		}
	}

	// Create and insert song
	if ($isValid) {
		try {
			SongController::insertSong(new Song(
					0,
					$_POST["titleInput"],
					[],
					$_POST["artistInput"],
					$_POST["genreInput"],
					$_POST["releaseDateInput"],
					$duration,
					$flacFileName,
					$opusFileName,
					$imageName,
					$thumbnailName
			));
			$successMessage = "Song uploaded successfully!";
		} catch (Exception $e) {
			$isValid = false;
			$errorMessage = "Failed to create song: " . $e->getMessage();
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - Create Song</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../mainStyle.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php"); ?>

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
		<main class="main col-md ms-sm-auto px-0 py-0">
			<div class="container mt-5">
				<h1>Create New Song</h1>

				<?php if (!empty($errorMessage)): ?>
					<div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
				<?php endif; ?>

				<?php if (!empty($successMessage)): ?>
					<div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
				<?php endif; ?>

				<form action="song.php" method="post" enctype="multipart/form-data">
					<div class="form-group mb-3">
						<label for="title">Title:</label>
						<input type="text" id="title" name="titleInput" class="form-control"
							   placeholder="Enter song title" required>
					</div>

					<div class="form-group mb-3">
						<label for="artist">Artists:</label>
						<div id="artistFields">
							<div class="artist-field d-flex mb-2">
								<select name="artistInput[]" class="form-control me-2" required>
									<?php foreach ($artistList as $artist): ?>
										<option value="<?php echo htmlspecialchars($artist->getArtistID()); ?>"
												<?php echo ($currentArtist && $artist->getArtistID() == $currentArtist['artistID']) ? 'selected' : ''; ?>>
											<?php echo htmlspecialchars($artist->getName()); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<button type="button" class="btn btn-danger remove-artist" style="display:none;"
										onclick="removeArtist(this)">-
								</button>
							</div>
						</div>
						<button type="button" onclick="addArtist()" class="btn btn-info mt-2">Add Collaborator</button>
					</div>

					<div class="form-group mb-3">
						<label for="genre">Genre:</label>
						<input type="text" id="genre" name="genreInput" class="form-control" placeholder="Enter genre"
							   required>
					</div>

					<div class="form-group mb-3">
						<label for="releaseDate">Release Date:</label>
						<input type="date" id="releaseDate" name="releaseDateInput" class="form-control" required>
					</div>

					<div class="form-group mb-3">
						<label for="songFile">Audio File:</label>
						<input type="file" id="songFile" name="fileInput" class="form-control" accept="audio/*"
							   required>
						<small class="form-text text-muted">Supported formats: FLAC, WAV, MP3, AAC, OGG, and many
							others. Lossless formats recommended.</small>
					</div>

					<div class="form-group mb-3">
						<label for="songImage">Cover Image (optional):</label>
						<input type="file" id="songImage" name="songImageInput" class="form-control" accept="image/*">
						<small class="form-text text-muted">Will be converted to WebP format and resized
							automatically.</small>
					</div>

					<button type="submit" class="btn btn-primary mt-3">Upload Song</button>
				</form>
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
			</script>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>