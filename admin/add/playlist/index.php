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
	<title>BeatStream - add a playlist</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../../../mainStyle.css" rel="stylesheet">
	<link href="../../../favicon.ico" rel="icon">
</head>

<body>

<?php include("../../../topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
			<div class="nav flex-column py-4">
				<a href="../../../" class="nav-link mb-2">Home</a>
				<a href="../../../search.php" class="nav-link mb-2">Search</a>
				<a href="../../../discover.php" class="nav-link mb-2">Discover</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="" class="nav-link mb-2 active">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="col-md ms-sm-auto px-0 py-0">

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
					<li class="nav-item"><a class="nav-link active" href="">Playlist</a></li>
					<li class="nav-item"><a class="nav-link" href="../album">Album</a></li>
				</ul>
			</div>

			<?php
			include("../../../DataController.php");
			include("../../../Objects/Playlist.php");
			$userList = DataController::getUserList();

			$isValid = true;

			if (!(
				!empty($_POST["nameInput"]) && !empty($_POST["lengthInput"]) && !empty($_POST["durationInput"]) && !empty($_POST["imagePathInput"]) && !empty($_POST["creatorInput"])
			)) {
				$isValid = false;
			}

			if ($isValid) {
				DataController::insertPlaylist(new Playlist(
					"",
					$_POST["imagePathInput"],
					$_POST["nameInput"],
					$_POST["durationInput"],
					$_POST["lengthInput"],
					$_POST["creatorInput"],
				));
			}
			?>

			<div class="container mt-5">
				<h1>Playlist Einfügen</h1>

				<form action="index.php" method="post" id="addPlaylistForm">
					<div class="form-group">
						<label for="name">Playlist title:</label>
						<input type="text" id="name" name="nameInput" class="form-control"
							   placeholder="Enter playlist name"
							   required>
					</div>

					<div class="form-group">
						<label for="imagePath">Image path:</label>
						<input type="text" id="imagePath" name="imagePathInput" class="form-control"
							   placeholder="Enter image path"
							   required>
					</div>
					<div class="form-group">
						<label for="creator">Ersteller:</label>
						<select name="creatorInput" id="creator" style="width: 175px;" class="form-control"
								style="width: 100%" required>
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