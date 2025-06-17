<?php
session_start();
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - view playlists</title>
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
			</ul>
		</div>
	</div>
</nav>

<div class="tab">
	<ul class="nav nav-tabs justify-content-center">
		<li class="nav-item"><a class="nav-link" href="../songs">Songs</a></li>
		<li class="nav-item"><a class="nav-link" href="../artists">Artists</a></li>
		<li class="nav-item"><a class="nav-link" href="../users">Users</a></li>
		<li class="nav-item"><a class="nav-link active" href="../playlists">Playlists</a></li>
		<li class="nav-item"><a class="nav-link" href="../albums">Albums</a></li>
	</ul>
</div>

<?php
include("../../SongController.php");
$playlistList = SongController::getPlaylistList();

if (array_key_exists('removeButton', $_POST)) {
	SongController::deletePlaylist($_POST['removeButton']);
	header("Refresh:0");
}
?>


<table style="width:100%; font-family:segoe UI,serif;">
	<colgroup>
		<col span="9" style="background-color:lightgray">
	</colgroup>
	<tr>
		<th style="width:14.3%;">Playlist ID</th>
		<th style="width:14.3%;">Image Path</th>
		<th style="width:14.3%;">Name</th>
		<th style="width:14.3%;">Duration</th>
		<th style="width:14.3%;">Length</th>
		<th style="width:14.3%;">Creator ID</th>
		<th style="width:1%;"></th>
	</tr>
	<?php
	for ($i = 0; $i < count($playlistList); $i++) {
		?>
		<tr>
			<td><?php echo $playlistList[$i]->getPlaylistID() ?></td>
			<td><?php echo $playlistList[$i]->getImagePath() ?></td>
			<td><?php echo $playlistList[$i]->getName() ?></td>
			<td><?php echo $playlistList[$i]->getDuration()->format('i:s') ?></td>
			<td><?php echo $playlistList[$i]->getLength() ?></td>
			<td><?php echo $playlistList[$i]->getCreatorID() ?></td>
			<td>
				<form method="post" action="">
					<button name="removeButton" id="remove" value="<?php echo $playlistList[$i]->getPlaylistID() ?>"
							class="btn btn-danger" type="submit" title="Remove Playlist">🗑️
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