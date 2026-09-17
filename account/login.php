<?php
session_start();
// If the user is logged in, redirect to the home page
if (isset($_SESSION['account_loggedin'])) {
	header("Location: {$GLOBALS['PROJECT_ROOT']}/");
	exit();
}
?>

<?php

require_once($GLOBALS['PROJECT_ROOT_DIR'] . "/controller/UserController.php");
require_once($GLOBALS['PROJECT_ROOT_DIR'] . "/Utils.php");

$isValid = true;
$credentialsCorrect = true;

if (isset($_POST['submit'])) {
	$email = $_POST['emailInput'] ?? '';
	$password = $_POST['userPasswordInput'] ?? '';

	if (empty($email) || empty($password)) {
		$isValid = false;
		return;
	}

	$mysqli = DBConn::getConn();

	$stmt = $mysqli->prepare("SELECT userPassword, salt, username, userID,isAdmin, hasAccess, thumbnailName FROM user WHERE email = ?");
	if (!$stmt) {
		throw new RuntimeException('Prepare failed: ' . $mysqli->error);
	}

	$stmt->bind_param('s', $email);
	$stmt->execute();

	$user = $stmt->get_result()->fetch_assoc();
	$stmt->close();

	if ($user && Utils::hashPassword($password, $user['salt']) === $user['userPassword']) {
		session_regenerate_id(true);

		$_SESSION += [
			'account_loggedin' => true,
			'email' => $email,
			'username' => $user['username'],
			'userID' => $user['userID'],
			'hasAccess' => (bool)$user['hasAccess'],
			'isAdmin' => (bool)$user['isAdmin'],
			'imageName' => $user['thumbnailName'],
		];

		header('Location: ' . $GLOBALS['PROJECT_ROOT'] . '/account/loginSuccess.php');
		exit;
	}

	$credentialsCorrect = false;
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - login</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
					<li class="nav-item"><a class="nav-link active"
											href="<?= $GLOBALS['PROJECT_ROOT'] ?>/account/login.php">login</a></li>
					<li class="nav-item"><a class="nav-link" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/account/signup.php">sign
							up</a></li>
				</ul>
			</div>

			<div class="container mt-5">
				<h1>Log in</h1>

				<?php
				if (!$credentialsCorrect) {
					echo '<div class="alert alert-danger" role="alert">Invalid credentials, please try again.</div>';
				}
				?>

				<form action="login.php" method="post" id="addUserForm">
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
					<input type="submit" class="btn btn-primary mt-3" value="Log in" name="submit">
				</form>
			</div>

			<!-- Bootstrap JS (optional for some interactive components) -->
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
		</main>
	</div>
</div>
</body>
</html>
