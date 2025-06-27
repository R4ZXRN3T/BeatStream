<?php
include("../dbConnection.php");
include("../DataController.php");
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
$artistList = DataController::getArtistList();

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

	$imageUploadDir = $_SERVER["DOCUMENT_ROOT"] . "/BeatStream/images/song/";
	$audioUploadDir = $_SERVER["DOCUMENT_ROOT"] . "/BeatStream/audio/";
	$songimageName = "";
	$newimageName = "";
	$songfileName = "";
	$newFileName = "";

	// Handle image upload
	if ($isValid && isset($_FILES['songImageInput']) && $_FILES['songImageInput']['error'] === UPLOAD_ERR_OK) {
		$fileTmpPath = $_FILES['songImageInput']['tmp_name'];
		$fileName = $_FILES['songImageInput']['name'];
		$newimageName = uniqid() . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
		$destPath = $imageUploadDir . $newimageName;

		if (!is_dir($imageUploadDir)) {
			mkdir($imageUploadDir, 0777, true);
		}

		if (move_uploaded_file($fileTmpPath, $destPath)) {
			$songimageName = $newimageName;
		} else {
			$isValid = false;
			$errorMessage = "Image upload failed";
		}
	}

	// Handle audio upload
	if ($isValid) {
		if (isset($_FILES['fileInput']) && $_FILES['fileInput']['error'] === UPLOAD_ERR_OK) {
			$fileTmpPath = $_FILES['fileInput']['tmp_name'];
			$fileName = $_FILES['fileInput']['name'];
			$extension = pathinfo($fileName, PATHINFO_EXTENSION);
			$newFileName = uniqid() . '.' . $extension;
			$destPath = $audioUploadDir . $newFileName;

			if (!is_dir($audioUploadDir)) {
				mkdir($audioUploadDir, 0777, true);
			}

			if (move_uploaded_file($fileTmpPath, $destPath)) {
				include_once("../mp3file.class.php");
				$mp3File = new MP3File($destPath);
				$songLength = $mp3File->getDuration();

				$artistString = implode(", ", $_POST["artistInput"]);

				DataController::insertSong(new Song(
					0,
					$_POST["titleInput"],
					$artistString,
					$_POST["genreInput"],
					$_POST["releaseDateInput"],
					MP3File::formatTime($songLength),
					$newFileName,
					$newimageName
				));

				$successMessage = "Song uploaded successfully!";
			} else {
				$isValid = false;
				$errorMessage = "Audio upload failed";
			}
		} else {
			$isValid = false;
			$errorMessage = "No audio file provided or upload error";
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
<?php include("../topBar.php"); ?>

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
					<div class="alert alert-danger"><?php echo $errorMessage; ?></div>
				<?php endif; ?>

				<?php if (!empty($successMessage)): ?>
					<div class="alert alert-success"><?php echo $successMessage; ?></div>
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
										<option value="<?php echo htmlspecialchars($artist->getName()); ?>"
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
						<input type="file" id="songFile" name="fileInput" class="form-control" accept="audio/mpeg"
							   required>
					</div>

					<div class="form-group mb-3">
						<label for="songImage">Cover Image:</label>
						<input type="file" id="songImage" name="songImageInput" class="form-control" accept="image/*">
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