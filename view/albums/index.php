<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - view albums</title>
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
		<li class="nav-item"><a class="nav-link" href="../playlists">Playlists</a></li>
		<li class="nav-item"><a class="nav-link active" href="../albums">Albums</a></li>
	</ul>
</div>

<?php
include("../../SongController.php");
$albumList = SongController::getAlbumList();

if (array_key_exists('removeButton', $_POST)) {
	SongController::deleteAlbum($_POST['removeButton']);
	header("Refresh:0");
}
?>


<table style="width:100%; font-family:segoe UI,serif;">
	<colgroup>
		<col span="9" style="background-color:lightgray">
	</colgroup>
	<tr>
		<th style="width:14.3%;">Album ID</th>
		<th style="width:14.3%;">Name</th>
		<th style="width:14.3%;">Artists</th>
		<th style="width:14.3%;">Image Path</th>
		<th style="width:14.3%;">Album Length</th>
		<th style="width:14.3%;">Album Duration</th>
		<th style="width:1%;"></th>
	</tr>
	<?php
	for ($i = 0; $i < count($albumList); $i++) {
		?>
		<tr>
			<td><?php echo $albumList[$i]->getAlbumID() ?></td>
			<td><?php echo $albumList[$i]->getName() ?></td>
			<td><?php echo $albumList[$i]->getArtists() ?></td>
			<td><?php echo $albumList[$i]->getImagePath() ?></td>
			<td><?php echo $albumList[$i]->getAlbumLength() ?></td>
			<td><?php echo $albumList[$i]->getAlbumDuration()->format('i:s') ?></td>
			<td>
				<form method="post" action="">
					<button name="removeButton" id="remove" value="<?php echo $albumList[$i]->getAlbumID() ?>"
							class="btn btn-danger" type="submit" title="Remove Album">🗑️
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