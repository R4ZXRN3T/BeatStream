<?php
include($GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php");
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
	<title>BeatStream - add a user</title>
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
			<nav class="navbar navbar-expand-lg navbar-dark bg-secondary admin-nav">
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
					<li class="nav-item"><a class="nav-link active" href="">User</a></li>
					<li class="nav-item"><a class="nav-link"
											href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/playlist.php">Playlist</a>
					</li>
					<li class="nav-item"><a class="nav-link" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/album.php">Album</a>
					</li>
				</ul>
			</div>

			<?php
			require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/UserController.php";
			$errorMessage = "";
			$isValid = true;

			if (!(!empty($_POST["usernameInput"]) && !empty($_POST["emailInput"]) && !empty($_POST["userPasswordInput"]))) {
				$isValid = false;
			}

			if ($isValid) {
				// Handle file upload
				$imageName = "";
				$thumbnailName = "";

				require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/converter.php";
				if (isset($_FILES["userImage"]) && $_FILES["userImage"]["error"] == UPLOAD_ERR_OK) {
					$result = Converter::uploadImage($_FILES["userImage"], ImageType::USER);
					if ($result['success']) {
						$imageName = $result['large_filename'];
						$thumbnailName = $result['thumbnail_filename'];
					} else {
						$isValid = false;
						$errorMessage = $result['error'];
					}
				}

				if ($isValid) {
					$isAdmin = isset($_POST["isAdminInput"]);
					UserController::insertUser(new User(
							0,
							$_POST["usernameInput"],
							$_POST["emailInput"],
							$_POST["userPasswordInput"],
							"",
							$isAdmin, // Use the correctly processed boolean value
							FALSE,
							$imageName,
							$thumbnailName
					));
				} else {
					echo "<h1 class='text-center mt-4 text-danger'>Error uploading file!</h1>";
				}
			} else {
				echo "<h1 class='text-center mt-4 text-danger'>$errorMessage</h1>";
			}

			// Display error message if validation failed
			if (!$isValid && !empty($errorMessage)) {
				echo "<div class='alert alert-danger mt-3'>$errorMessage</div>";
			}
			?>

			<div class="container mt-5">
				<h1>User Einfügen</h1>

				<form action="user.php" method="post" id="addUserForm" enctype="multipart/form-data">
					<div class="form-group">
						<label for="username">Username:</label>
						<input type="text" id="username" name="usernameInput" class="form-control"
							   placeholder="Enter username"
							   required>
					</div>
					<div class="form-group">
						<label for="email">E-Mail:</label>
						<input type="text" id="email" name="emailInput" class="form-control" placeholder="Enter email"
							   required>
					</div>
					<div class="form-group">
						<label for="userPassword">Password:</label>
						<input type="text" id="userPassword" name="userPasswordInput" class="form-control"
							   placeholder="Enter password" required>
					</div>
					<div class="form-group">
						<label for="isAdminInput">Admin</label>
						<input type="checkbox" id="isAdminInput" name="isAdminInput" value=TRUE>
					</div>
					<div class="form-group">
						<label for="userImage">Profile Image:</label>
						<input type="file" id="userImage" name="userImage" class="form-control" accept="image/*">
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