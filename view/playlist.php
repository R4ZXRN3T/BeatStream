<?php
session_start();

// Check if playlist ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	header('Location: ../discover/playlists.php');
	exit;
}

$playlistId = (int)$_GET['id'];

// Include data controller
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/PlaylistController.php";
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php";

$playlist = PlaylistController::getPlaylistById($playlistId);

// If playlist not found, redirect
if ($playlist === null) {
	header('Location: ../discover/playlists.php');
	exit;
}

// Get songs in the playlist
$playlistSongs = SongController::getPlaylistSongs($playlistId);

// Prepare song queue data for player
$songQueueData = array_map(function ($song) use ($playlist) {
	return [
			'songID' => $song->getSongID(),
			'title' => $song->getTitle(),
			'artists' => $song->getArtists(),
			'flacFilename' => $song->getFlacFileName(),
			'opusFilename' => $song->getOpusFileName(),
			'imageName' => $song->getImageName(),
			'thumbnailName' => $song->getThumbnailName(),
	];
}, $playlistSongs);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - <?php echo htmlspecialchars($playlist->getName()); ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
	<link href="../mainStyle.css" rel="stylesheet">
</head>

<body>
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

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
			<!-- Playlist Header -->
			<div class="container mt-4">
				<div class="row">
					<div class="col-md-4 text-center">
						<?php if (!empty($playlist->getImageName())): ?>
							<img src="<?php echo "{$GLOBALS['PROJECT_ROOT']}/images/playlist/large/" . htmlspecialchars($playlist->getImageName()); ?>"
								 class="img-fluid rounded shadow"
								 alt="<?php echo htmlspecialchars($playlist->getName()); ?>"
								 style="max-width: 300px;">
						<?php else: ?>
							<img src="../images/defaultPlaylist.webp" class="img-fluid rounded shadow"
								 alt="Default Playlist Cover"
								 style="max-width: 300px;">
						<?php endif; ?>
					</div>
					<div class="col-md-8">
						<h1 class="mb-2"><?php echo htmlspecialchars($playlist->getName()); ?></h1>
						<p><?php echo count($playlistSongs); ?> songs ·
							<?php echo $playlist->getFormattedDuration() ?></p>

						<?php if (isset($_SESSION['userID']) && $_SESSION['userID'] == $playlist->getCreatorID()): ?>
							<a href="../create/editPlaylist.php?id=<?php echo $playlistId; ?>"
							   class="btn btn-primary mb-3">
								Edit Playlist
							</a>
						<?php else: ?>
							<p class="text-start mb-3" style="color: #96a3af">Created by: <?php
								echo htmlspecialchars($playlist->getCreatorName());
								?></p>
						<?php endif; ?>
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
						'showArtistLinks' => false,
						'containerClass' => 'col-12',
						'emptyMessage' => 'No songs available in this playlist.'
				];

				$songs = $playlistSongs;
				$options = $songListOptions;
				include('../components/song-list.php');
				?>
			</div>
		</main>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/player.php"); ?>
<script src="<?= $GLOBALS['PROJECT_ROOT'] ?>/addMenuContent.js"></script>
</body>
</html>