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
	include("../../Objects/Playlist.php");
	$userList = SongController::getUserList();

	$isValid = true;

	if (!(
		!empty($_POST["nameInput"]) && !empty($_POST["lengthInput"]) && !empty($_POST["durationInput"]) && !empty($_POST["imagePathInput"]) && !empty($_POST["creatorInput"])
	)) {
		echo "Error: Please fill all fields";
		$isValid = false;
	}

	if ($isValid) {
		SongController::insertPlaylist(new Playlist(
			"",
			$_POST["imagePathInput"],
			$_POST["nameInput"],
			$_POST["durationInput"],
			$_POST["lengthInput"],
			$_POST["creatorInput"],
		));
	}
	?>

	<div class="content">
		<h1>Playlist Einfügen</h1>

		<form action="index.php" method="post" id="addPlaylistForm">
			<div class="form-group">
				<label for="name">Playlist title:</label><br>
				<input type="text" id="name" name="nameInput"><br><br>
			</div>
			<div class="form-group">
				<label for="length">Länge:</label><br>
				<input type="number" id="length" name="lengthInput"><br><br>
			</div>
			<div class="form-group">
				<label for="duration">Dauer:</label><br>
				<input type="time" id="duration" name="durationInput" style="width: 175px" step="1"><br><br>
			</div>
			<div class="form-group">
				<label for="imagePath">Image path:</label><br>
				<input type="text" id="imagePath" name="imagePathInput"><br><br>
			</div>
			<div class="form-group">
				<label for="creator">Ersteller:</label><br>
				<select name="creatorInput" id="creator" style="width: 175px;">
					<option value=none>--Please Select--</option>
					<?php
					for ($i = 0; $i < count($userList); $i++) {
					?>
						<option value="<?php echo $userList[$i]->getUserID() ?>"><?php echo $userList[$i]->getUsername() ?></option>
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