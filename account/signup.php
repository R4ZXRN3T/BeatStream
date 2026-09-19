<?php
session_start();
// If the user is logged in, redirect to the home page
if (isset($_SESSION['account_loggedin'])) {
	header("Location: {$GLOBALS['PROJECT_ROOT']}/");
	exit();
}
?>

<?php
include($GLOBALS['PROJECT_ROOT_DIR'] . "/controller/UserController.php");

$isValid = true;
$signupOkay = true;
$errorMessage = "";

$isValid = !empty($_POST['usernameInput'])
	&& !empty($_POST['emailInput'])
	&& !empty($_POST['userPasswordInput']);

if ($isValid) {
	$uploadOk = true;
	$targetFile = null;
	$largeFileName = "";
	$thumbnailFileName = "";

	if (UserController::usernameExists($_POST['usernameInput'])) {
		$errorMessage = "Username already exists.";
		$signupOkay = false;
		$uploadOk = false;
	} elseif (UserController::emailExists($_POST['emailInput'])) {
		$errorMessage = "Email already exists.";
		$signupOkay = false;
		$uploadOk = false;
	}

	if (!empty($_FILES["imageToUpload"]["name"]) && $_FILES["imageToUpload"]["error"] == UPLOAD_ERR_OK && $uploadOk) {
		require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/converter.php";
		$uploadResult = Converter::uploadImage($_FILES["imageToUpload"], ImageType::USER);
		if ($uploadResult['success']) {
			$largeFileName = $uploadResult['large_filename'];
			$thumbnailFileName = $uploadResult['thumbnail_filename'];
		} else {
			$errorMessage = $uploadResult['error'];
			$signupOkay = false;
		}
	}

	if ($signupOkay) {
		UserController::insertUser(new User(
			0,
			$_POST["usernameInput"],
			$_POST["emailInput"],
			$_POST["userPasswordInput"],
			"",
			FALSE,
			FALSE,
			FALSE,
			$largeFileName,
			$thumbnailFileName
		));
		session_regenerate_id(true);
		$stmt = DBConn::getConn()->prepare("SELECT userID FROM user WHERE email = ?");
		$stmt->bind_param("s", $_POST['emailInput']);
		$stmt->execute();
		$_SESSION += [
			'account_loggedin' => true,
			'userID' => $stmt->get_result()->fetch_assoc()['userID'],
			'email' => $_POST['emailInput'],
			'username' => $_POST['usernameInput'],
			'imageName' => $thumbnailFileName,
			'isAdmin' => false,
		];
		header("Location: {$GLOBALS['PROJECT_ROOT']}/account/loginSuccess.php");
		exit();
	}
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - sign up</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/favicon.ico" rel="icon">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/mainStyle.css" rel="stylesheet">
</head>

<body>
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/account/login.php">login</a>
					</li>
					<li class="nav-item"><a class="nav-link active"
											href="<?= $GLOBALS['PROJECT_ROOT'] ?>/account/signup.php">sign up</a>
					</li>
				</ul>
			</div>

			<div class="container mt-5">
				<h1>Sign Up</h1>

				<?php
				if (!$signupOkay) {
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
						<label for="imageName">Profile Picture:&nbsp;&nbsp;&nbsp;&nbsp;(not required)</label>
						<input type="file" id="imageUpload" name="imageToUpload" class="form-control" accept="Image/*"
							   placeholder="Upload a profile picture!">
					</div>
					<input type="submit" class="btn btn-primary mt-3" value="Join BeatStream" name="submit">
				</form>
			</div>

			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
		</main>
	</div>
</div>
</body>
</html>
