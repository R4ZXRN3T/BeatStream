<?php
session_start();
// If the user is logged in, redirect to the home page
if (isset($_SESSION['account_loggedin'])) {
	echo "already logged in";
	header("location: ../");
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - sign up</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container-fluid">
		<div class="collapse navbar-collapse myNavbar">
			<ul class="navbar-nav">
				<li class="nav-item"><a class="nav-link" href="../view/songs">Home</a></li>
				<li class="nav-item"><a class="nav-link" href="../add/song">Add content</a></li>
			</ul>
		</div>
	</div>
</nav>

<div class="tab">
	<ul class="nav nav-tabs justify-content-center">
		<li class="nav-item"><a class="nav-link" href="login.php">login</a></li>
		<li class="nav-item"><a class="nav-link active" href="signup.php">sign up</a></li>
	</ul>
</div>

<?php
include("../SongController.php");

$isValid = true;

if (!(
	!empty($_POST["usernameInput"]) && !empty($_POST["emailInput"]) && !empty($_POST["userPasswordInput"]) && isset($_FILES["imageToUpload"]) && $_FILES["imageToUpload"]["error"] == UPLOAD_ERR_OK
)) {
	$isValid = false;
}

if ($isValid) {
	$targetDir = "../images/user/";
	$targetFile = $targetDir . basename($_FILES["imageToUpload"]["name"]);
	$uploadOk = 1;
	$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
	// Check if image file is an actual image or fake image
	if (isset($_POST["submit"])) {
		$check = getimagesize($_FILES["imageToUpload"]["tmp_name"]);
		if ($check !== false) {
			echo "File is an image - " . $check["mime"] . ".";
			$uploadOk = 1;
		} else {
			echo "File is not an image.";
			$uploadOk = 0;
		}
	}
	if (file_exists($targetFile)) {
		echo "Sorry, file already exists.";
		$uploadOk = 0;
	}

	if ($_FILES["imageToUpload"]["size"] > 500000) {
		echo "Sorry, your file is too large.";
		$uploadOk = 0;
	}

	if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif") {
		echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
		$uploadOk = 0;
	}

	if ($uploadOk == 0) {
		echo "Sorry, your file was not uploaded.";
	} else {
		if (move_uploaded_file($_FILES["imageToUpload"]["tmp_name"], $targetFile)) {
			echo "The file " . htmlspecialchars(basename($_FILES["imageToUpload"]["name"])) . " has been uploaded.";
		} else {
			echo "Sorry, there was an error uploading your file.";
		}
	}

	SongController::insertUser(new User(
		"",
		$_POST["usernameInput"],
		$_POST["emailInput"],
		$_POST["userPasswordInput"],
		"",
		$_FILES["imageToUpload"]["name"]
	));
	$_SESSION['account_loggedin'] = true; // Set session variable to indicate user is logged in
	$_SESSION['email'] = $_POST['emailInput'];
	$_SESSION['username'] = $_POST['usernameInput'];
	$stmt = DBConn::getConn()->prepare("SELECT userID FROM user WHERE email = ?");
	$stmt->bind_param("s", $_POST['emailInput']);
	$stmt->execute();
	$_SESSION['userID'] = $stmt->get_result()->fetch_assoc()['userID'];
	$stmt->close();
	header("location: loginSuccess.php");
	?>
	<?php
}
?>

<div class="container mt-5">
	<h1>Sign Up</h1>

	<form action="signup.php" method="post" id="addUserForm" enctype="multipart/form-data">
		<div class="form-group">
			<label for="username">Username:</label>
			<input type="text" id="username" name="usernameInput" class="form-control" placeholder="Enter username"
				   required>
		</div>
		<div class="form-group">
			<label for="email">E-Mail:</label>
			<input type="text" id="email" name="emailInput" class="form-control" placeholder="Enter email" required>
		</div>
		<div class="form-group">
			<label for="userPassword">Password:</label>
			<input type="text" id="userPassword" name="userPasswordInput" class="form-control"
				   placeholder="Enter password" required>
		</div>
		<div class="form-group">
			<label for="imagePath">Profile Picture:</label>
			<input type="file" id="imageUpload" name="imageToUpload" class="form-control"
				   placeholder="Upload a profile picture!" required>
		</div>
		<input type="submit" class="btn btn-primary mt-3" value="Join BeatStream" name="submit">
	</form>
</div>

<!-- Bootstrap JS (optional for some interactive components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>