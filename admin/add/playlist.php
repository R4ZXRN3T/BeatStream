<?php
include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/dbConnection.php");
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
		header("Location: /BeatStream/admin/blocked.php");
		exit();
	}
	$_SESSION['isAdmin'] = $isAdmin;
} else {
	header("Location: /BeatStream/account/login.php");
	exit();
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - add a playlist</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="/BeatStream/mainStyle.css" rel="stylesheet">
	<link href="/BeatStream/favicon.ico" rel="icon">
</head>

<body>

<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="/BeatStream/" class="nav-link mb-2">Home</a>
				<a href="/BeatStream/search/" class="nav-link mb-2">Search</a>
				<a href="/BeatStream/discover/" class="nav-link mb-2">Discover</a>
				<a href="/BeatStream/create/"
				   class="nav-link mb-2">Create</a><?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
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
						<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/view/songs.php">View</a></li>
						<li class="nav-item"><a class="nav-link active" href="/BeatStream/admin/add/song.php">Add
								content</a></li>
					</ul>
				</div>
			</nav>

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/song.php">Song</a></li>
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/artist.php">Artist</a></li>
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/user.php">User</a></li>
					<li class="nav-item"><a class="nav-link active" href="">Playlist</a></li>
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/album.php">Album</a></li>
				</ul>
			</div>

			<?php
			require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/UserController.php";
			require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/PlaylistController.php";
			require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/SongController.php";
			require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/converter.php";
			$userList = UserController::getUserList();
			$songList = SongController::getSongList();

			$isValid = true;

			if (!(!empty($_POST["nameInput"]) && !empty($_FILES["imageInput"]) && !empty($_POST["creatorInput"]))) {
				$isValid = false;
			}

			$imageName = "";
			$thumbnailName = "";

			if ($isValid) {
				if (isset($_FILES['imageInput']) && $_FILES['imageInput']['error'] == UPLOAD_ERR_OK) {
					$result = Converter::uploadImage($_FILES['imageInput'], ImageType::PLAYLIST);
					if ($result['success']) {
						$imageName = $result['large_filename'];
						$thumbnailName = $result['thumbnail_filename'];
					} else {
						echo "<div class='alert alert-danger'>Image upload failed: " . htmlspecialchars($result['error']) . "</div>";
						$isValid = false;
					}
				}

				$totalMilliSeconds = 0;
				foreach ($_POST['songInput'] as $selectedSongID) {
					foreach ($songList as $song) {
						if ($song->getSongID() == $selectedSongID) {
							$totalMilliSeconds += $song->getSongLength();
							break;
						}
					}
				}

				PlaylistController::insertPlaylist(new Playlist(
						0,
						$_POST["nameInput"],
						$_POST["songInput"],
						$totalMilliSeconds,
						count($_POST['songInput']),
						$imageName,
						$thumbnailName,
						$_POST["creatorInput"],
						UserController::getUserById($_POST["creatorInput"])->getUsername()
				));

			}
			?>

			<div class="container mt-5">
				<h1>Playlist Einfügen</h1>

				<form action="playlist.php" method="post" id="addPlaylistForm" enctype="multipart/form-data">
					<div class="form-group">
						<label for="nameInput">Playlist title:</label>
						<input type="text" id="nameInput" name="nameInput" class="form-control"
							   placeholder="Enter playlist name"
							   required>
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
						<label for="imageName">Image:</label>
						<input type="file" id="imageInput" name="imageInput" class="form-control" accept="image/*"
							   placeholder="Enter Image Name">
					</div>
					<div class="form-group">
						<label for="creatorField">Creator:</label>
						<div id="creatorField">
							<div class="d-flex mb-2">
								<select name="creatorInput" id="creatorInput" style="width: 100%;" class="form-control" required>
									<option value=none>--Please Select--</option>
									<?php
									for ($i = 0; $i < count($userList); $i++) {
										?>
										<option value="<?php echo $userList[$i]->getUserID() ?>"><?php echo $userList[$i]->getUsername() ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
					</div>
					<input type="submit" class="btn btn-primary mt-3" value="Submit">
				</form>
			</div>

			<!-- Bootstrap JS (optional for some interactive components) -->
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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

</body>

</html>