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
				<a href="/BeatStream/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin/" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link active" href="login.php">login</a></li>
					<li class="nav-item"><a class="nav-link" href="signup.php">sign up</a></li>
				</ul>
			</div>

			<?php
			include("../DataController.php");

			$isValid = true;
			$credentialsCorrect = true;

			if (!(
				!empty($_POST["emailInput"]) && !empty($_POST["userPasswordInput"])
			)) {
				$isValid = false;
			}

			if ($isValid) {
				$stmt = DBConn::getConn()->prepare("SELECT userPassword, salt, username, userID, isAdmin, imageName FROM user WHERE email = ?");
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
						$_SESSION['isAdmin'] = $result['isAdmin'] == 1;
						$_SESSION['imageName'] = $result['imageName'];
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