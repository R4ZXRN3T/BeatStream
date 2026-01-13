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
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/ArtistController.php";
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/UserController.php";

$isValid = true;
$imageName = '';
$thumbnailName = '';

if (!(!empty($_POST["artistName"]) && !empty($_POST["activeSince"]))) {
	$isValid = false;
}

// Process file upload if form fields are valid
if ($isValid && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK && $_FILES['imageFile']['size'] > 0) {
	require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/converter.php";#
	$result = Converter::uploadImage($_FILES['imageFile'], ImageType::ARTIST);
	if ($result['success']) {
		$imageName = $result['large_filename'];
		$thumbnailName = $result['thumbnail_filename'];
	} else {
		$isValid = false;
		echo "<div class='alert alert-danger' role='alert'>Image upload failed: " . htmlspecialchars($result['error']) . "</div>";
	}
}

if ($isValid) {

	ArtistController::insertArtist(new Artist(
			0,
			$_POST["artistName"],
			$imageName,
			$thumbnailName,// Use same image for thumbnail for now
			$_POST["activeSince"],
			$_SESSION['userID']
	));
	header("Location: ../account/profile.php");
}
?>

<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<?php
		$activePage = 'create';
		include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/sidebar.php");
		?>
		<!-- Main Content -->
		<main class="main col-md" style="min-height: 80vh; margin-left: 150px; padding: 2rem;">

			<div class="container" style="max-width: 1700px;">
				<h1 class="mb-4">Become an Artist</h1>
				<form action="artist.php" method="post" enctype="multipart/form-data">
					<div class="mb-3">
						<label for="artistName" class="form-label">Your Name:</label>
						<input type="text" class="form-control" id="artistName" name="artistName" required>
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