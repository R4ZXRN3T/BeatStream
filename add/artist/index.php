<!Doctype html>
<html lang="en">

<head>
	<title>Artists</title>
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
	$userList = SongController::getUserList();

	$isValid = true;

	if (!(
		!empty($_POST["nameInput"]) && !empty($_POST["imagePathInput"]) && !empty($_POST["followerInput"]) && !empty($_POST["activeSinceInput"]) && !empty($_POST["userIDInput"])
	)) {
		echo "Error: Please fill all fields";
		$isValid = false;
	}

	if ($isValid) {
		SongController::insertArtist(new Artist(
			12345,
			$_POST["nameInput"],
			$_POST["imagePathInput"],
			$_POST["followerInput"],
			$_POST["activeSinceInput"],
			$_POST["userIDInput"]
		));
	?>
		<h1>Success!</h1>
	<?php
	}
	?>

	<div class="content">
		<h1>Künstler Einfügen</h1>

		<form action="index.php" method="post" id="addArtistForm">
			<div class="form-group">
				<label for="name">Name:</label><br>
				<input type="text" id="name" name="nameInput"><br><br>
			</div>
			<div class="form-group">
				<label for="imagePath">Image Path:</label><br>
				<input type="text" id="imagePath" name="imagePathInput"><br><br>
			</div>
			<div class="form-group">
				<label for="follower">Follower:</label><br>
				<input type="number" id="follower" name="followerInput"><br><br>
			</div>
			<div class="form-group">
				<label for="activeSince">active since:</label><br>
				<input type="date" id="activeSince" name="activeSinceInput"><br><br>
			</div>
			<div class="form-group">
				<label for="user">User:</label><br>
                <label for="userID"></label><select name="userIDInput" id="userID" style="width: 175px;">
					<option value=none>--Please Select--</option>
					<?php
					for ($i = 0; $i < count($userList); $i++) {
					?>
						<option value=<?php echo $userList[$i]->getUserID() ?>><?php echo $userList[$i]->getUsername() ?></option>
					<?php
					}
					?>
				</select><br><br>
			</div>
			<input type="submit" class="btn btn-primary">
		</form>
	</div>
</body>

</html>