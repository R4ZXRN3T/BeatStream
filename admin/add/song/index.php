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

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - add a song</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../../../mainStyle.css" rel="stylesheet">
	<link href="../../../favicon.ico" rel="icon">
</head>

<body>

<?php
include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php");
include_once("../../../mp3file.class.php")
?>

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
					<li class="nav-item"><a class="nav-link active" href="">Song</a></li>
					<li class="nav-item"><a class="nav-link" href="../artist">Artist</a></li>
					<li class="nav-item"><a class="nav-link" href="../user">User</a></li>
					<li class="nav-item"><a class="nav-link" href="../playlist">Playlist</a></li>
					<li class="nav-item"><a class="nav-link" href="../album">Album</a></li>
				</ul>
			</div>

			<?php
			include("../../../DataController.php");
			$artistList = DataController::getArtistList();
			$isValid = true;
			$errorMessage = "";

			if (!(!empty($_POST["titleInput"]) && !empty($_POST["artistInput"][0]) && !empty($_POST["genreInput"]) && !empty($_POST["releaseDateInput"]) && !empty($_FILES["fileInput"]))) {
				$isValid = false;
			}

			// After $isValid = true; and before DataController::insertSong(...)
			$imageUploadDir = "../../../../BeatStream/images/song/";
			$songimageName = "";
			$newimageName = "";

			$audioUploadDir = "../../../audio/";
			$songfileName = "";
			$newFileName = "";

			if ($isValid && isset($_FILES['songImageInput']) && $_FILES['songImageInput']['error'] === UPLOAD_ERR_OK) {
				$fileTmpPath = $_FILES['songImageInput']['tmp_name'];
				$fileName = $_FILES['songImageInput']['name'];
				$extension = pathinfo($fileName, PATHINFO_EXTENSION);
				$newimageName = uniqid() . '.' . $extension;
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
				$destPath = "";
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
						$songfileName = "/BeatStream/audio/" . $newFileName;
						$_FILES["fileNameInput"] = $songfileName;

					} else {
						$isValid = false;
						$errorMessage = "Audio upload failed";
					}
				} else {
					$isValid = false;
					$errorMessage = "no file provided or upload error";
				}

				$mp3File = new MP3File($destPath);
				$songLength = $mp3File->getDuration();

				DataController::insertSong(new Song(
					0,
					$_POST["titleInput"],
					[],
					$_POST["artistInput"],
					$_POST["genreInput"],
					$_POST["releaseDateInput"],
					MP3File::formatTime($songLength),
					$newFileName,
					$newimageName
				));
			}
			?>

			<!-- Song Form -->
			<div class="container mt-5">
				<h1>Add song</h1>

				<form action="index.php" method="post" id="addSongForm" enctype="multipart/form-data">

					<?php
					if (!empty($errorMessage)) {
						echo '<div class="alert alert-danger" role="alert">' . $errorMessage . '</div>';
					}
					?>

					<div class="form-group">
						<label for="title">Title:</label>
						<input type="text" id="title" name="titleInput" class="form-control"
							   placeholder="Enter song title"
							   required>
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

					<div class="form-group">
						<label for="genre">Genre:</label>
						<input type="text" id="genre" name="genreInput" class="form-control" placeholder="Enter genre"
							   required>
					</div>

					<div class="form-group">
						<label for="releaseDate">Release Date:</label>

						<input type="date" id="releaseDate" name="releaseDateInput" class="form-control"
							   placeholder="Enter release date" required>
					</div>

					<div class="form-group">
						<label for="songFile">File:</label>
						<input type="file" id="songFile" name="fileInput" class="form-control" accept="audio/mpeg"
							   placeholder="Upload song file"
							   required>
					</div>

					<div class="form-group">
						<label for="songImage">Image:&nbsp;&nbsp;&nbsp;&nbsp;(not required)</label>
						<input type="file" id="songImage" name="songImageInput" class="form-control" accept="image/*">
					</div>

					<input type="submit" class="btn btn-primary mt-3" value="Submit">
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

			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
		</main>
	</div>
</div>
</body>

</html>