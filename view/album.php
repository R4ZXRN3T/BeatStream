<?php
session_start();

// Check if album ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	header('Location: ../discover/albums.php');
	exit;
}

$albumId = (int)$_GET['id'];

// Include controllers
include_once("../controller/AlbumController.php");
include_once("../controller/SongController.php");

$album = AlbumController::getAlbumByID($albumId);

// If album not found, redirect
if ($album === null) {
	header('Location: ../discover/albums.php');
	exit;
}

// Get songs in the album
$albumSongs = SongController::getAlbumSongs($albumId);

// Prepare song queue data for player
$songQueueData = array_map(function ($song) use ($album) {
	return [
			'songID' => $song->getSongID(),
			'title' => $song->getTitle(),
			'artists' => implode(", ", $song->getArtists()),
			'artistIDs' => $song->getArtistIDs(),
			'flacFilename' => $song->getFlacFileName(),
			'opusFilename' => $song->getOpusFileName(),
			'imageName' => "../../album/large/" . $album->getImageName(),
			'thumbnailName' => "../../album/thumbnail/" . $album->getThumbnailName()
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
<?php include( $GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2">Home</a>
				<a href="../search/" class="nav-link mb-2">Search</a>
				<a href="../discover/" class="nav-link mb-2">Discover</a>
				<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>

		<main class="main col-md ms-sm-auto px-0 py-0 justify-content-center">
			<!-- Album Header -->
			<div class="container mt-4">
				<div class="row">
					<div class="col-md-4 text-center">
						<?php if (!empty($album->getImageName())): ?>
							<img src="<?php echo "{$GLOBALS['PROJECT_ROOT']}/images/album/large/" . htmlspecialchars($album->getImageName()); ?>"
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
						<p class="text mb-2" style="color: #6c757d; font-size: 20px"><?php
							$artistLinks = [];
							$artists = $album->getArtists();
							$artistIDs = $album->getArtistIDs();
							for ($i = 0; $i < count($artistIDs); $i++) {
								$artistLinks[$i] = "<a class='custom-link' href='artist.php?id=" . $artistIDs[$i] . "'>" . htmlspecialchars($artists[$i]) . "</a>";
							}
							echo implode(", ", $artistLinks);
							?>
						</p>
						<p><?php echo htmlspecialchars($album->getReleaseDate()->format('F d\, Y')); ?></p>
						<p><?php echo count($albumSongs); ?> songs ·
							<?php echo $album->getFormattedDuration(); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Song List -->
			<div class="container" style="max-width: 800px; margin-top: 50px;">
				<?php
				$songListOptions = [
						'layout' => 'list',
						'showIndex' => true,
						'showDuration' => true,
						'showArtistLinks' => true,
						'containerClass' => 'col-12',
						'emptyMessage' => 'No songs available in this album.',
						'albumView' => true,
						'albumImageName' => $album->getThumbnailName()
				];

				$songs = $albumSongs;
				$options = $songListOptions;
				include('../components/song-list.php');
				?>
			</div>
		</main>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php include( $GLOBALS['PROJECT_ROOT_DIR'] . "/components/player.php"); ?>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		// Prevent song playback when clicking on artist links
		document.querySelectorAll('.card-body a.custom-link').forEach(link => {
			link.addEventListener('click', function (event) {
				event.stopPropagation();
			});
		});
	});
</script>
</body>
</html>