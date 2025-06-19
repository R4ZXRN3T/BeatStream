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
	<link href="../addStyle.css" rel="stylesheet">
	<link href="../../../favicon.ico" rel="icon">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container-fluid">
		<div class="collapse navbar-collapse myNavbar">
			<ul class="navbar-nav">
				<li class="nav-item"><a class="nav-link" href="../../view/songs">View</a></li>
				<li class="nav-item"><a class="nav-link active" href="../../add/song">Add content</a></li>
			</ul>
			<?php if (isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true): ?>
				<div class="dropdown ms-auto">
					<button class="btn d-flex align-items-center dropdown-toggle p-0 bg-transparent border-0"
							type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
						<div class="text-end">
							<div class="fw-bold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
							<div class="small text-white-50"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
						</div>
						<img src="<?php echo $_SESSION['imagePath'] ? '../../../images/user/' . $_SESSION['imagePath'] : '../../../images/default.webp'; ?>"
							 alt="Profile" class="rounded-circle me-2"
							 style="width:40px; height:40px; object-fit:cover; margin-left: 15px; margin-right: 15px;">
					</button>
					<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
						<li><a class="dropdown-item" href="../../../account/profile.php">View Profile</a></li>
						<li>
							<hr class="dropdown-divider">
						</li>
						<li><a class="dropdown-item text-danger" href="../../../account/logout.php">Log Out</a></li>
					</ul>
				</div>
			<?php else: ?>
				<div class="ms-auto d-flex">
					<a href="../../../account/login.php" class="btn btn-outline-light me-2">Login</a>
					<a href="../../../account/signup.php" class="btn btn-primary">Sign Up</a>
				</div>
			<?php endif; ?>
		</div>
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

// Check if all fields are filled out
if (!(
	!empty($_POST["titleInput"]) && !empty($_POST["artistInput"]) && !empty($_POST["genreInput"]) && !empty($_POST["releaseDateInput"]) && !empty($_POST["ratingInput"]) && !empty($_POST["songLengthInput"]) && !empty($_POST["filePathInput"]) && !empty($_POST["imagePathInput"])
)) {
	$isValid = false;
}

if ($isValid) {
	$artistString = implode(", ", $_POST["artistInput"]);
	DataController::insertSong(new Song(
		"",
		$_POST["titleInput"],
		$artistString,
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
			<input type="text" id="title" name="titleInput" class="form-control" placeholder="Enter song title"
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
							echo "<option value='{$artist->getName()}'>{$artist->getName()}</option>";
						}
						?>
					</select>
					<button type="button" class="btn btn-danger remove-artist" style="display:none;" onclick="removeArtist(this)">-</button>
				</div>
			</div>
			<button type="button" onclick="addArtist()" class="btn btn-info mt-2">+</button>
		</div>

		<div class="form-group">
			<label for="genre">Genre:</label>
			<input type="text" id="genre" name="genreInput" class="form-control" placeholder="Enter genre" required>
		</div>

		<div class="form-group">
			<label for="releaseDate">Release Date:</label>
			<input type="date" id="releaseDate" name="releaseDateInput" class="form-control"
				   placeholder="Enter release date" required>
		</div>

		<div class="form-group">
			<label for="rating">Rating:</label>
			<input type="number" id="rating" name="ratingInput" class="form-control" step="0.1" min="1" max="5"
				   placeholder="Enter rating (1.0 - 5.0)" required>
		</div>

		<div class="form-group">
			<label for="songLength">Song Length:</label>
			<input type="time" id="songLength" name="songLengthInput" class="form-control" step="1"
				   placeholder="Enter song length" required>
		</div>

		<div class="form-group">
			<label for="filePath">File Path:</label>
			<input type="text" id="filePath" name="filePathInput" class="form-control" placeholder="Enter file path"
				   required>
		</div>

		<div class="form-group">
			<label for="imagePath">Image Path:</label>
			<input type="text" id="imagePath" name="imagePathInput" class="form-control" placeholder="Enter image path"
				   required>
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
</body>

</html>