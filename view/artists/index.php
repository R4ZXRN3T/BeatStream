<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - Home</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../viewStyle.css" rel="stylesheet">
	<link href="../../favicon.ico" rel="icon">
</head>

<body>

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
		<li class="nav-item"><a class="nav-link active" href="../artists">Artists</a></li>
		<li class="nav-item"><a class="nav-link" href="../users">Users</a></li>
		<li class="nav-item"><a class="nav-link" href="../playlists">Playlists</a></li>
		<li class="nav-item"><a class="nav-link" href="../albums">Albums</a></li>
	</ul>
</div>

<?php
include("../../SongController.php");
$artistList = SongController::getArtistList();
?>


<table style="width:100%; font-family:segoe UI,serif;">
	<colgroup>
		<col span="9" style="background-color:lightgray">
	</colgroup>
	<tr>
		<th style="width:14.3%;">Artist ID</th>
		<th style="width:14.3%;">Name</th>
		<th style="width:14.3%;">Image Path</th>
		<th style="width:14.3%;">Follower</th>
		<th style="width:14.3%;">Active Since</th>
		<th style="width:14.3%;">User ID</th>
		<th style="width:1%;"></th>
	</tr>
	<?php
	for ($i = 0; $i < count($artistList); $i++) {
		?>
		<tr>
			<td><?php echo $artistList[$i]->getArtistID() ?></td>
			<td><?php echo $artistList[$i]->getName() ?></td>
			<td><?php echo $artistList[$i]->getImagePath() ?></td>
			<td><?php echo $artistList[$i]->getFollower() ?></td>
			<td><?php echo $artistList[$i]->getActiveSince()->format('d.m.Y') ?></td>
			<td><?php echo $artistList[$i]->getUserID() ?></td>
		</tr>
		<?php
	}
	?>
</table>
</body>

</html>