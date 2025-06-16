<!Doctype html>
<html lang="en">

<head>
	<title>dies ist ein titel</title>
</head>

<body>
	<h1>Ich bin ein header</h1>
	<p>dies ist text</p>

	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="container-fluid">
			<div class="collapse navbar-collapse myNavbar">
				<ul class="navbar-nav">
					<li class="nav-item"><a class="nav-link" href="../Arian_temp">Home</a></li>
					<li class="nav-item"><a class="nav-link" href="./add/song">add songs</a></li>
					<li class="nav-item"><a class="nav-link" href="./add/artist">add artist</a></li>
					<li class="nav-item"><a class="nav-link" href="./add/user">add user</a></li>
					<li class="nav-item"><a class="nav-link" href="./add/playlist">add playlist</a></li>
					<li class="nav-item"><a class="nav-link" href="./add/album">add album</a></li>
				</ul>
			</div>
		</div>
	</nav>

	<?php
	include("SongController.php");
	$songListe = SongController::getSongList();
	?>



	<table style="width:100%; font-family:segoe UI,serif;">
		<colgroup>
			<col span="9" style="background-color:lightgray">
		</colgroup>
		<tr>
			<th style="width:11.1%;">Song ID</th>
			<th style="width:11.1%;">Title</th>
			<th style="width:11.1%;">Artists</th>
			<th style="width:11.1%;">Genre</th>
			<th style="width:11.1%;">Release Date</th>
			<th style="width:11.1%;">Rating</th>
			<th style="width:11.1%;">Song Length</th>
			<th style="width:11.1%;">File Path</th>
			<th style="width:11.1%;">Image Path</th>
		</tr>
		<?php
		for ($i = 0; $i < count($songListe); $i++) {
		?>
			<tr>
				<td><?php echo $songListe[$i]->getSongID() ?></td>
				<td><?php echo $songListe[$i]->getTitle() ?></td>
				<td><?php echo $songListe[$i]->getArtists() ?></td>
				<td><?php echo $songListe[$i]->getGenre() ?></td>
				<td><?php echo $songListe[$i]->getReleaseDate()->format('d.m.Y') ?></td>
				<td><?php echo $songListe[$i]->getRating() ?></td>
				<td><?php echo $songListe[$i]->getSongLength()->format('i:s') ?></td>
				<td><?php echo $songListe[$i]->getFilePath() ?></td>
				<td><?php echo $songListe[$i]->getImagePath() ?></td>
			</tr>
		<?php
		}
		?>
	</table>
</body>

</html>