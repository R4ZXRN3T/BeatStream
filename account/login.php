<?php
session_start();
// If the user is logged in, redirect to the home page
if (isset($_SESSION['account_loggedin'])) {
	header("location: ../");
	exit();
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - login</title>
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
		<li class="nav-item"><a class="nav-link active" href="login.php">login</a></li>
		<li class="nav-item"><a class="nav-link" href="signup.php">sign up</a></li>
	</ul>
</div>

<?php
include("../SongController.php");

$isValid = true;
$credentialsCorrect = true;

if (!(
	!empty($_POST["emailInput"]) && !empty($_POST["userPasswordInput"])
)) {
	$isValid = false;
}

if ($isValid) {
	$stmt = DBConn::getConn()->prepare("SELECT userPassword, salt, username, userID FROM user WHERE email = ?");
	$stmt->bind_param("s", $_POST['emailInput']);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();
	if ($result) {
		$hashedPassword = $result['userPassword'];
		$salt = $result['salt'];
		if (hash("sha256", $_POST['userPasswordInput'] . $salt) == $hashedPassword) {
			$credentialsCorrect = true;
			$_SESSION['account_loggedin'] = true;
			$_SESSION['email'] = $_POST['emailInput'];
			$_SESSION['username'] = $result['username'];
			$_SESSION['userID'] = $result['userID'];
			header("location: loginSuccess.php");
		} else {
			$credentialsCorrect = false;
		}
	} else {
		$credentialsCorrect = false;
	}
	$stmt->close();
}
?>

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
			<input type="text" id="email" name="emailInput" class="form-control" placeholder="Enter email" required>
		</div>
		<div class="form-group">
			<label for="userPassword">Password:</label>
			<input type="text" id="userPassword" name="userPasswordInput" class="form-control"
				   placeholder="Enter password" required>
		</div>
		<input type="submit" class="btn btn-primary mt-3" value="Log in" name="submit">
	</form>
</div>

<!-- Bootstrap JS (optional for some interactive components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>