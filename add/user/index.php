<!Doctype html>
<html lang="en">

<head>
	<title>Songs</title>
</head>

<body>

	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="container-fluid">
			<div class="collapse navbar-collapse myNavbar">
				<ul class="navbar-nav">
					<li class="nav-item"><a class="nav-link" href="../../">Home</a></li>
					<li class="nav-item"><a class="nav-link" href="../song">add songs</a></li>
					<li class="nav-item"><a class="nav-link" href="../artist">add artist</a></li>
					<li class="nav-item"><a class="nav-link" href="../user">add user</a></li>
					<li class="nav-item"><a class="nav-link" href="../playlist">add playlist</a></li>
					<li class="nav-item"><a class="nav-link" href="../album">add album</a></li>
				</ul>
			</div>
		</div>
	</nav>

	<?php
	include("../../SongController.php");
	$artistList = SongController::getArtistList();

	$isValid = true;

	if (!(
		!empty($_POST["usernameInput"]) && !empty($_POST["emailInput"]) && !empty($_POST["userPasswordInput"]) && !empty($_POST["imagePathInput"])
	)) {
		echo "Error: Please fill all fields";
		$isValid = false;
	}

	if ($isValid) {
		SongController::insertUser(new User(
			"",
			$_POST["usernameInput"],
			$_POST["emailInput"],
			$_POST["userPasswordInput"],
			$_POST["imagePathInput"]
		));
	?>
		<h1>Success!</h1>
	<?php
	}
	?>

	<div class="content">
		<h1>User Einfügen</h1>

		<form action="index.php" method="post" id="addUserForm">
			<div class="form-group">
				<label for="username">Username:</label><br>
				<input type="text" id="username" name="usernameInput"><br><br>
			</div>
			<div class="form-group">
				<label for="email">E-Mail:</label><br>
				<input type="text" id="email" name="emailInput"><br><br>
			</div>
			<div class="form-group">
				<label for="userPassword">Password:</label><br>
				<input type="text" id="userPassword" name="userPasswordInput"><br><br>
			</div>
			<div class="form-group">
				<label for="imagePath">Image path:</label><br>
				<input type="text" id="imagePath" name="imagePathInput"><br><br>
			</div>
			<input type="submit" class="btn btn-primary">
		</form>
	</div>
</body>

</html>