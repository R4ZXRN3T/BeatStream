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
	<title>BeatStream - add a user</title>
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
						<li class="nav-item"><a class="nav-link active" href="/BeatStream/admin/add/song.php">Add content</a></li>
					</ul>
				</div>
			</nav>

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/song.php">Song</a></li>
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/artist.php">Artist</a></li>
					<li class="nav-item"><a class="nav-link active" href="">User</a></li>
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/playlist.php">Playlist</a></li>
					<li class="nav-item"><a class="nav-link" href="/BeatStream/admin/add/album.php">Album</a></li>
				</ul>
			</div>

			<?php
			include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/DataController.php");

			$uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/images/user/"; // Define upload directory
			$errorMessage = "";

			// Create directory if it doesn't exist
			if (!file_exists($uploadDir)) {
				mkdir($uploadDir, 0777, true);
			}

			if (!(
				!empty($_POST["usernameInput"]) && !empty($_POST["emailInput"]) && !empty($_POST["userPasswordInput"])
			)) {
				$isValid = false;
			}

			if ($isValid) {
				// Handle file upload
				$newFileName = "";
				if (isset($_FILES["userImage"]) && $_FILES["userImage"]["error"] == UPLOAD_ERR_OK) {
					$fileName = $_FILES["userImage"]["name"];
					$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
					$newFileName = uniqid() . 'user' . $extension;// Generate unique filename
					$targetFilePath = $uploadDir . $newFileName;// Check file size (limit to 5MB)
					if ($_FILES["userImage"]["size"] > 5000000) {
						$isValid = false;
						$errorMessage = "File is too large. Maximum size is 2MB.";
					} else {
						move_uploaded_file($_FILES["userImage"]["tmp_name"], $targetFilePath);
					}
				}

				if ($isValid) {
					// Upload file
					// Insert user with the new file path
					$isAdmin = isset($_POST["isAdminInput"]);
					DataController::insertUser(new User(
						0,
						$_POST["usernameInput"],
						$_POST["emailInput"],
						$_POST["userPasswordInput"],
						"",
						$isAdmin, // Use the correctly processed boolean value
						FALSE,
						$newFileName
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