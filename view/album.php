<?php
session_start();

// Check if album ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	header('Location: ../discover/albums.php');
	exit;
}

$albumId = (int)$_GET['id'];

// Include data controller
include_once("../DataController.php");

// Get album data
$albumList = DataController::getAlbumList();
$album = null;

// Find the requested album
foreach ($albumList as $a) {
	if ($a->getAlbumID() == $albumId) {
		$album = $a;
		break;
	}
}

// If album not found, redirect
if ($album === null) {
	header('Location: ../discover/albums.php');
	exit;
}

// Get songs in the album
$songList = DataController::getSongList();
$albumSongs = [];

// Use the order from album->getSongIDs() to arrange songs
foreach ($album->getSongIDs() as $songId) {
	foreach ($songList as $song) {
		if ($song->getSongID() == $songId) {
			$albumSongs[] = $song;
			break;
		}
	}
}

// Prepare song queue data for player
$songQueueData = array_map(function ($song) use ($album) {
	return [
		'songID' => $song->getSongID(),
		'title' => $song->getTitle(),
		'artists' => $song->getArtists(),
		'fileName' => $song->getFileName(),
		'imageName' => "../album/" . $album->getImageName() // Use album image for all songs
	];
}, $albumSongs);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - <?php echo htmlspecialchars($album->getName()); ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
	<link href="../mainStyle.css" rel="stylesheet">
</head>

<body>
<?php include("../topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2">Home</a>
				<a href="../search/" class="nav-link mb-2">Search</a>
				<a href="../discover/" class="nav-link mb-2">Discover</a>
				<a href="/BeatStream/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>

		<main class="main col-md ms-sm-auto px-0 py-0 justify-content-center">
			<!-- Album Header -->
			<div class="container mt-4">
				<div class="row">
					<div class="col-md-4 text-center">
						<?php if (!empty($album->getImageName())): ?>
							<img src="<?php echo "/BeatStream/images/album/" . htmlspecialchars($album->getImageName()); ?>"
								 class="img-fluid rounded shadow"
								 alt="<?php echo htmlspecialchars($album->getName()); ?>"
								 style="max-width: 300px;">
						<?php else: ?>
							<img src="../images/defaultAlbum.webp" class="img-fluid rounded shadow"
								 alt="Default Album Cover"
								 style="max-width: 300px;">
						<?php endif; ?>
					</div>
					<div class="col-md-8">
						<h1 class="mb-2"><?php echo htmlspecialchars($album->getName()); ?></h1>
						<p class="text mb-2"
						   style="color: #6c757d"><?php echo htmlspecialchars($album->getArtists()); ?></p>
						<p><?php echo count($albumSongs); ?> songs ·
							<?php echo $album->getDuration()->format("H") > 0 ? $album->getDuration()->format("H:i:s") : $album->getDuration()->format("i:s"); ?></p>
					</div>
				</div>
			</div>

			<!-- Song List -->
			<div class="container" style="max-width: 800px; margin-top: 50px;">
				<?php if (!empty($albumSongs)): ?>
					<?php foreach ($albumSongs as $index => $song): ?>
						<div class="card shadow-sm border-0 mb-3">
							<div class="card-body d-flex align-items-center p-3 position-relative"
								 data-song-id="<?php echo $song->getSongID(); ?>"
								 data-song-queue='<?php echo htmlspecialchars(json_encode($songQueueData)); ?>'>
								<div class="position-relative me-3">
									<?php if (!empty($album->getImageName())): ?>
										<img src="<?php echo "/BeatStream/images/album/" . htmlspecialchars($album->getImageName()); ?>"
											 class="rounded"
											 alt="<?php echo htmlspecialchars($album->getName()); ?>"
											 style="width: 60px; height: 60px; object-fit: cover;">
									<?php else: ?>
										<img src="../images/defaultAlbum.webp" class="rounded"
											 alt="Default Album Cover"
											 style="width: 60px; height: 60px; object-fit: cover;">
									<?php endif; ?>
									<div class="position-absolute"
										 style="left: -10px; top: 20px; background: rgba(0,0,0,0.6); color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 0.8rem;">
										<?php echo $index + 1; ?>
									</div>
								</div>
								<div>
									<h5 class="card-title mb-1"
										style="font-size: 1.1rem; font-weight: bold;"><?php echo htmlspecialchars($song->getTitle()); ?></h5>
									<p class="card-text mb-0"
									   style="font-size: 0.9rem; color: #6c757d;"><?php echo htmlspecialchars($song->getArtists()); ?></p>
								</div>
								<div class="ms-auto">
									<p class="card-text mb-0"
									   style="font-size: 0.8rem; color: #6c757d;"><?php echo $song->getSongLength()->format("i:s"); ?></p>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<p class="text-center">No songs available in this album.</p>
				<?php endif; ?>
			</div>
		</main>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php include("../player.php"); ?>
</body>
</html>