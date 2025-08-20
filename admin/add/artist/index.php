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
	<title>BeatStream - add an artist</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../../../mainStyle.css" rel="stylesheet">
	<link href="../../../favicon.ico" rel="icon">
</head>

<body>

<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php"); ?>

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
					<li class="nav-item"><a class="nav-link" href="../song">Song</a></li>
					<li class="nav-item"><a class="nav-link active" href="">Artist</a></li>
					<li class="nav-item"><a class="nav-link" href="../user">User</a></li>
					<li class="nav-item"><a class="nav-link" href="../playlist">Playlist</a></li>
					<li class="nav-item"><a class="nav-link" href="../album">Album</a></li>
				</ul>
			</div>

			<?php
			include("../../../DataController.php");
			$userList = DataController::getUserList();

			$isValid = true;
			$imageName = '';

			if (!(
				!empty($_POST["nameInput"]) && !empty($_POST["activeSinceInput"]) && !empty($_POST["userIDInput"])
			)) {
				$isValid = false;
			}

			// Process file upload if form fields are valid
			if ($isValid && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK && $_FILES['imageFile']['size'] > 0) {
				$uploadDir = "../../../images/artist/";

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
					$_POST["nameInput"],
					$imageName, // Use the new uploaded image name
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

				<form action="index.php" method="post" id="addArtistForm" enctype="multipart/form-data">
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