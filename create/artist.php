<?php
ob_start();
include("../dbConnection.php");
session_start();
$userID = $_SESSION['userID'];
$stmt = DBConn::getConn()->prepare("SELECT isArtist FROM user WHERE userID = $userID;");
$stmt->execute();
$result = $stmt->get_result();
if (!(isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true && !$result->fetch_assoc()['isArtist'])) {
	header("Location: ../account/login.php");
	exit();
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - become an artist</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
	<link href="../mainStyle.css" rel="stylesheet">
</head>

<body>

<?php
include("../DataController.php");

$isValid = true;
$imageName = '';

if (!(
	!empty($_POST["albumName"]) && !empty($_POST["activeSince"])
)) {
	$isValid = false;
}

// Process file upload if form fields are valid
if ($isValid && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK && $_FILES['imageFile']['size'] > 0) {
	$uploadDir = "../images/artist/";

	// Create directory if it doesn't exist
	if (!file_exists($uploadDir)) {
		mkdir($uploadDir, 0777, true);
	}

	$fileExtension = pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION);
	$imageName = uniqid() . '.' . $fileExtension;
	$targetFile = $uploadDir . $imageName;

	// Check if file is an actual image
	$validImage = getimagesize($_FILES['imageFile']['tmp_name']) !== false;

	if (!$validImage) {
		$isValid = false;
		echo "<div class='alert alert-danger'>Uploaded file is not a valid image.</div>";
	} else if ($_FILES['imageFile']['size'] > 5000000) { // 5MB limit
		$isValid = false;
		echo "<div class='alert alert-danger'>File is too large. Maximum size is 5MB.</div>";
	} else if (!move_uploaded_file($_FILES['imageFile']['tmp_name'], $targetFile)) {
		$isValid = false;
		echo "<div class='alert alert-danger'>Failed to upload the image.</div>";
	}
}

if ($isValid) {

	DataController::insertArtist(new Artist(
		12345,
		$_POST["albumName"],
		$imageName, // Use the new uploaded image name
		$_POST["activeSince"],
		$_SESSION['userID']
	));
	header("Location: ../account/profile.php");
}
?>

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
					<a href="/BeatStream/admin" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="main col-md" style="min-height: 80vh; margin-left: 150px; padding: 2rem;">

			<div class="container" style="max-width: 1700px;">
				<h1 class="mb-4">Become an Artist</h1>
				<form action="artist.php" method="post" enctype="multipart/form-data">
					<div class="mb-3">
						<label for="albumName" class="form-label">Your Name:</label>
						<input type="text" class="form-control" id="albumName" name="albumName" required>
					</div>
					<div class="form-group">
						<label for="activeSince" class="form-label">When did you start making music?</label>
						<input type="date" class="form-control" id="activeSince" name="activeSince" required>
					</div>
					<div class="form-group">
						<label for="image" class="form-label">Your image:</label>
						<input type="file" class="form-control" id="image" name="imageFile" accept="image/*">
					</div>
					<input type="submit" class="btn btn-primary mt-3" value="Become an artist!">
				</form>
			</div>
		</main>
	</div>
</div>
</body>

</html>