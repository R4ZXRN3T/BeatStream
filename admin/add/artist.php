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
	<title>BeatStream - add an artist</title>
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
						<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/view/songs.php">View</a></li>
						<li class="nav-item"><a class="nav-link active" href="/BeatStream/admin/add/song.php">Add
								content</a></li>
					</ul>
				</div>
			</nav>

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/song.php">Song</a></li>
					<li class="nav-item"><a class="nav-link active" href="">Artist</a></li>
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/user.php">User</a></li>
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/playlist.php">Playlist</a></li>
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/album.php">Album</a></li>
				</ul>
			</div>

			<?php
			require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/ArtistController.php";
			require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/UserController.php";
			$userList = UserController::getUserList();

			$isValid = true;
			$imageName = '';
			$thumbnailName = '';

			if (!(!empty($_POST["nameInput"]) && !empty($_POST["activeSinceInput"]) && !empty($_POST["userIDInput"]))) {
				$isValid = false;
			}

			// Process file upload if form fields are valid
			if ($isValid && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK && $_FILES['imageFile']['size'] > 0) {
				require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/converter.php";
				$result = Converter::uploadImage($_FILES["imageFile"], ImageType::ARTIST);
				if ($result['success']) {
					$imageName = $result['large_filename'];
					$thumbnailName = $result['thumbnail_filename'];
				} else {
					$isValid = false;
					echo '<div class="alert alert-danger"><h3>Error!</h3><p>' . htmlspecialchars($result['error']) . '</p></div>';
				}
			}

			if ($isValid) {
				ArtistController::insertArtist(new Artist(
						12345,
						$_POST["nameInput"],
						$imageName,
						$thumbnailName,
						$_POST["activeSinceInput"],
						$_POST["userIDInput"]
				));
				?>
				<div class="alert alert-success">
					<h3>Success!</h3>
					<p>Artist has been added successfully.</p>
				</div>
				<?php
			}
			?>

			<div class="container mt-5">
				<h1>Add Artist</h1>

				<form action="artist.php" method="post" id="addArtistForm" enctype="multipart/form-data">
					<div class="form-group">
						<label for="name">Name:</label>
						<input type="text" id="name" name="nameInput" class="form-control"
							   placeholder="Enter artist name" required>
					</div>
					<div class="form-group">
						<label for="imageFile">Artist Image:</label>
						<input type="file" id="imageFile" name="imageFile" class="form-control" accept="image/*">
					</div>
					<div class="form-group">
						<label for="activeSince">active since:</label>
						<input type="date" id="activeSince" name="activeSinceInput" class="form-control"
							   placeholder="Enter creation date" required>
					</div>
					<div class="form-group">
						<label for="userID">User:</label>
						<select name="userIDInput" id="userID" class="form-control" required>
							<option value="">--Please Select--</option>
							<?php foreach ($userList as $user): ?>
								<option value="<?php echo $user->getUserID(); ?>"><?php echo $user->getUsername(); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<input type="submit" class="btn btn-primary mt-3" value="Submit">
				</form>
			</div>

			<!-- Bootstrap JS (optional for some interactive components) -->
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
		</main>
	</div>
</div>
</body>

</html>

