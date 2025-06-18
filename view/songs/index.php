<?php
session_start();
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - view songs</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../viewStyle.css" rel="stylesheet">
	<link href="../../favicon.ico" rel="icon">
</head>

<body>

<script>
	if ( window.history.replaceState ) {
		window.history.replaceState( null, null, window.location.href );
	}
</script>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container-fluid">
		<div class="collapse navbar-collapse myNavbar">
			<ul class="navbar-nav">
				<li class="nav-item"><a class="nav-link" href="../songs">Home</a></li>
				<li class="nav-item"><a class="nav-link" href="../../add/song">Add content</a></li>
				<li class="nav-item"><a class="nav-link" href="../../account/logout.php">logout</a></li>
				<li class="nav-item"><a class="nav-link" href="../../account/signup.php">sign up</a></li>
			</ul>
		</div>
	</div>
</nav>

<?php
if (isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true) {
	echo "User ID: " . $_SESSION['userID'] . "<br>" . "Username: " . $_SESSION['username'] . "<br>" . "Email: " . $_SESSION['email'] . "<br>";
} else {
	echo "not logged in";
}

?>

<div class="tab">
	<ul class="nav nav-tabs justify-content-center">
		<li class="nav-item"><a class="nav-link active" href="../songs">Songs</a></li>
		<li class="nav-item"><a class="nav-link" href="../artists">Artists</a></li>
		<li class="nav-item"><a class="nav-link" href="../users">Users</a></li>
		<li class="nav-item"><a class="nav-link" href="../playlists">Playlists</a></li>
		<li class="nav-item"><a class="nav-link" href="../albums">Albums</a></li>
	</ul>
</div>

<?php
include("../../SongController.php");
$songList = SongController::getSongList();
?>

<?php
if (array_key_exists('removeButton', $_POST)) {
	SongController::deleteSong($_POST['removeButton']);
	header("Refresh:0");
}
?>


<table style="width:100%; font-family:segoe UI,serif;">
	<colgroup>
		<col span="9" style="background-color:lightgray">
	</colgroup>
	<tr>
		<th style="width:10%;">Song ID</th>
		<th style="width:10%;">Title</th>
		<th style="width:10%;">Artists</th>
		<th style="width:10%;">Genre</th>
		<th style="width:10%;">Release Date</th>
		<th style="width:10%;">Rating</th>
		<th style="width:10%;">Song Length</th>
		<th style="width:10%;">File Path</th>
		<th style="width:10%;">Image Path</th>
		<th style="width:1%;"></th>
	</tr>
	<?php
	for ($i = 0; $i < count($songList); $i++) {
		?>
		<tr>
			<td><?php echo $songList[$i]->getSongID() ?></td>
			<td><?php echo $songList[$i]->getTitle() ?></td>
			<td><?php echo $songList[$i]->getArtists() ?></td>
			<td><?php echo $songList[$i]->getGenre() ?></td>
			<td><?php echo $songList[$i]->getReleaseDate()->format('d.m.Y') ?></td>
			<td><?php echo $songList[$i]->getRating() ?></td>
			<td><?php echo $songList[$i]->getSongLength()->format('i:s') ?></td>
			<td><?php echo $songList[$i]->getFilePath() ?></td>
			<td><?php echo $songList[$i]->getImagePath() ?></td>
			<td>
				<form method="post" action="">
					<button name="removeButton" id="remove" value="<?php echo $songList[$i]->getSongID() ?>"
							class="btn btn-danger" type="submit" title="Remove Song">🗑️
					</button>
				</form>
			</td>
		</tr>
		<?php
	}
	?>
</table>
</body>

</html>