<?php
session_start();
// If the user is logged in, redirect to the home page
if (!isset($_SESSION['account_loggedin'])) {
	header("location: ../");
	exit();
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Successful!"</title>
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

	<div class="container mt-5 text-center">
		<h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
		<p>You have successfully logged in.</p>
	</div>

<div>
	<div class="container mt-5 text-center">
		<a href="../admin/view/songs" class="btn btn-primary">Go to Home</a>
		<a href="../account/logout.php" class="btn btn-secondary">Logout</a>
	</div>
</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
