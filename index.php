<!Doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeatStream - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="favicon.ico" rel="icon">
</head>

<style>
    body {
        background-color: #f8f9fa;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 7px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #343a40;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #c7c7c7;
    }
</style>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <div class="collapse navbar-collapse myNavbar">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="../beatstream">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="./add/song">Add content</a></li>
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