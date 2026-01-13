<?php
session_start();
require_once($GLOBALS['PROJECT_ROOT_DIR'] . "/controller/ArtistController.php");
require_once($GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php");
require_once($GLOBALS['PROJECT_ROOT_DIR'] . "/controller/AlbumController.php");

// Get artist ID from URL parameter
$artistID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to home
if ($artistID <= 0) {
	header("Location: ../album.php");
	exit;
}

// Get artist details
$artist = ArtistController::getArtistByID($artistID);

// If artist not found, redirect
if (!$artist) {
	header("Location: {$GLOBALS['PROJECT_ROOT']}/");
	exit;
}

// Get all songs by this artist (filter by artistID)
$artistSongs = SongController::getArtistSongs($artistID);

// Get all albums by this artist (filter by artistID)
$artistAlbums = AlbumController::getArtistAlbums($artistID);

// Separate singles and albums
$singles = [];
$albums = [];
foreach ($artistAlbums as $album) {
	if ($album->isSingle()) {
		$singles[] = $album;
	} else {
		$albums[] = $album;
	}
}

$songQueueData = array_map(function ($song) {
	return [
			'songID' => $song->getSongID(),
			'title' => $song->getTitle(),
			'artists' => implode(", ", $song->getArtists()),
			'artistIDs' => $song->getArtistIDs(),
			'flacFilename' => $song->getFlacFileName(),
			'opusFilename' => $song->getOpusFileName(),
			'imageName' => $song->getImageName(),
			'thumbnailName' => $song->getThumbnailName(),
	];
}, $artistSongs);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $artist->getName(); ?> - Artist Profile</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
	<link href="../mainStyle.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
</head>
<body>

<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>
<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<?php
		include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/sidebar.php");
		?>
		<main class="main col-md ms-sm-auto px-0 py-0 justify-content-center">
			<div class="container-fluid py-3">
				<!-- Artist Header -->
				<div class="artist-header">
					<div class="container">
						<div class="row align-items-center">
							<div class="col-md-4 text-center">
								<img src="<?php echo $artist->getImageName() ? '../images/artist/large/' . $artist->getImageName() : '../images/defaultArtist.webp'; ?>"
									 alt="<?php echo $artist->getName(); ?>"
									 class="artist-image mb-3">
							</div>
							<div class="col-md-8">
								<h1><?php echo $artist->getName(); ?></h1>
								<p class="text" style="color: #6c757d">Active
									since: <?php echo $artist->getActiveSince()->format('F Y'); ?></p>
								<p>Total songs: <?php echo count($artistSongs); ?></p>
								<p>Total albums: <?php echo count($artistAlbums); ?></p>
							</div>
						</div>
					</div>
				</div>

				<!-- Songs Section -->
				<div class="container mb-5">
					<h2 class="mb-4">Songs</h2>
					<?php
					$songListOptions = [
							'layout' => 'grid',
							'showIndex' => false,
							'showDuration' => true,
							'showArtistLinks' => true,
							'containerClass' => 'col-md-4 mb-2',
							'emptyMessage' => 'No songs available for this artist.'
					];

					$songs = $artistSongs;
					$options = $songListOptions;
					include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/song-list.php");
					?>
				</div>

				<!-- Albums Section -->
				<?php if (!empty($albums)): ?>
					<div class="container mb-5">
						<h2 class="mb-4">Albums</h2>
						<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-3 g-3">
							<?php foreach ($albums as $album): ?>
								<?php
								$options = [
										'containerClass' => 'col-md-3 mb-3',
										'large' => true
								];
								include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/album-card.php");
								?>
							<?php endforeach; ?>
						</div>

					</div>
				<?php endif; ?>

				<!-- Singles Section -->
				<?php if (!empty($singles)): ?>
					<div class="container mb-5">
						<h2 class="mb-4">Singles</h2>
						<?php
						$options = [
								'containerClass' => 'col-md-4 mb-2',
								'compact' => true,
								'emptyMessage' => 'No singles available for this artist.'
						];
						$albumList = $singles;
						include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/album-list.php");
						?>
					</div>
				<?php endif; ?>
			</div>
		</main>
	</div>
</div>

<!-- Include the music player -->
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/player.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $GLOBALS['PROJECT_ROOT'] ?>/addMenuContent.js"></script>
</body>
</html>

