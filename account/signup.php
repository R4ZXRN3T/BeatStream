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
	<link href="../mainStyle.css" rel="stylesheet">
</head>

<body>
<?php include("../topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2">Home</a>
				<a href="../search/" class="nav-link mb-2">Search</a>
				<a href="../discover/" class="nav-link mb-2">Discover</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="col-md ms-sm-auto px-0 py-0">

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link" href="login.php">login</a></li>
					<li class="nav-item"><a class="nav-link active" href="signup.php">sign up</a></li>
				</ul>
			</div>

			<?php
			include("../DataController.php");

			$isValid = true;
			$loginOk = true;
			$errorMessage = "";

			if (!(
				!empty($_POST["usernameInput"]) && !empty($_POST["emailInput"]) && !empty($_POST["userPasswordInput"])
			)) {
				$isValid = false;
			}

			if ($isValid) {
				$uploadOk = true;
				$targetFile = null;

				if (!empty($_FILES["imageToUpload"]["name"])) {
					$targetDir = "../images/user/";
					$fileExtension = pathinfo($_FILES["imageToUpload"]["name"], PATHINFO_EXTENSION);
					$targetFile = $targetDir . basename(pathinfo($_FILES["imageToUpload"]["name"], PATHINFO_FILENAME) . time() . "." . $fileExtension);
					$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));// Check if image file is an actual image or fake image
					if (isset($_POST["submit"])) {
						$check = getimagesize($_FILES["imageToUpload"]["tmp_name"]);
						if ($check !== false) {
							$uploadOk = true;
						} else {
							echo "File is not an image.";
							$uploadOk = false;
						}
					}
					echo $targetFile;
					if (file_exists($targetFile)) {
						$errorMessage = "Sorry, file already exists.";
						$uploadOk = false;
					}
					if ($_FILES["imageToUpload"]["size"] > 500000) {
						$errorMessage = "Sorry, your file is too large.";
						$uploadOk = false;
					}
					if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
						&& $imageFileType != "gif" && $imageFileType != "webp") {
						$errorMessage = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
						$uploadOk = false;
					}

					if ($uploadOk) {
						move_uploaded_file($_FILES["imageToUpload"]["tmp_name"], $targetFile);
					}
				}

				$usernameList = array();
				$emailList = array();

				for ($i = 0; $i < count(DataController::getUserList()); $i++) {
					$usernameList[] = DataController::getUserList()[$i]->getUsername();
					$emailList[] = DataController::getUserList()[$i]->getEmail();
				}

				if (in_array($_POST['usernameInput'], $usernameList)) {
					$errorMessage = "Username already exists.";
					$loginOk = false;
					$uploadOk = false;
				} elseif (in_array($_POST['emailInput'], $emailList)) {
					$errorMessage = "Email already exists.";
					$loginOk = false;
					$uploadOk = false;
				}

				if ($loginOk) {
					DataController::insertUser(new User(
						"",
						$_POST["usernameInput"],
						$_POST["emailInput"],
						$_POST["userPasswordInput"],
						"",
						FALSE,
						FALSE,
						isset($_FILES['imageToUpload']) ? pathinfo($targetFile, PATHINFO_BASENAME) : null
					));
					$_SESSION['account_loggedin'] = true;// Set session variable to indicate user is logged in
					$_SESSION['email'] = $_POST['emailInput'];
					$_SESSION['username'] = $_POST['usernameInput'];
					$_SESSION['imagePath'] = pathinfo($targetFile, PATHINFO_BASENAME);
					$_SESSION['isAdmin'] = false;// Default to false for new users
					$stmt = DBConn::getConn()->prepare("SELECT userID FROM user WHERE email = ?");
					$stmt->bind_param("s", $_POST['emailInput']);
					$stmt->execute();
					$_SESSION['userID'] = $stmt->get_result()->fetch_assoc()['userID'];
					$stmt->close();
					header("location: loginSuccess.php");
				}
			}
			?>

			<div class="container mt-5">
				<h1>Sign Up</h1>

				<?php
				if (!$loginOk) {
					echo '<div class="alert alert-danger" role="alert">' . $errorMessage . '</div>';
				}
				?>

				<form action="signup.php" method="post" id="addUserForm" enctype="multipart/form-data">
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
						<input type="password" id="userPassword" name="userPasswordInput" class="form-control"
							   placeholder="Enter password" required>
					</div>
					<div class="form-group">
						<label for="imagePath">Profile Picture:&nbsp;&nbsp;&nbsp;&nbsp;(not required)</label>
						<input type="file" id="imageUpload" name="imageToUpload" class="form-control"
							   placeholder="Upload a profile picture!">
					</div>
					<input type="submit" class="btn btn-primary mt-3" value="Join BeatStream" name="submit">
				</form>
			</div>

			<!-- Bootstrap JS (optional for some interactive components) -->
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
		</main>
	</div>
</div>
</body>
</html>