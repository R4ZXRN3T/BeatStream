<?php
session_start();
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - add a user</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../addStyle.css" rel="stylesheet">
	<link href="../../favicon.ico" rel="icon">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container-fluid">
		<div class="collapse navbar-collapse myNavbar">
			<ul class="navbar-nav">
				<li class="nav-item"><a class="nav-link" href="../../view/songs">Home</a></li>
				<li class="nav-item"><a class="nav-link" href="../">Add content</a></li>
			</ul>
		</div>
	</div>
</nav>

<div class="tab">
	<ul class="nav nav-tabs justify-content-center">
		<li class="nav-item"><a class="nav-link" href="../song">Song</a></li>
		<li class="nav-item"><a class="nav-link" href="../artist">Artist</a></li>
		<li class="nav-item"><a class="nav-link active" href="../user">User</a></li>
		<li class="nav-item"><a class="nav-link" href="../playlist">Playlist</a></li>
		<li class="nav-item"><a class="nav-link" href="../album">Album</a></li>
	</ul>
</div>

<?php
include("../../SongController.php");

$isValid = true;

if (!(
	!empty($_POST["usernameInput"]) && !empty($_POST["emailInput"]) && !empty($_POST["userPasswordInput"]) && !empty($_POST["imagePathInput"])
)) {
	$isValid = false;
}

if ($isValid) {
	SongController::insertUser(new User(
		"",
		$_POST["usernameInput"],
		$_POST["emailInput"],
		$_POST["userPasswordInput"],
		"",
		$_POST["imagePathInput"]
	));
	?>
	<h1>Success!</h1>
	<?php
}
?>

<div class="container mt-5">
	<h1>User Einfügen</h1>

	<form action="index.php" method="post" id="addUserForm">
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
			<label for="imagePath">Image path:</label>
			<input type="text" id="imagePath" name="imagePathInput" class="form-control" placeholder="Enter image path"
				   required>
		</div>
		<input type="submit" class="btn btn-primary mt-3" value="Submit">
	</form>
</div>

<!-- Bootstrap JS (optional for some interactive components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>