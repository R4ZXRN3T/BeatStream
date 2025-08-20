<?php
session_start();

// Check if playlist ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	header('Location: ../discover/playlists.php');
	exit;
}

$playlistId = (int)$_GET['id'];

// Include data controller
include_once("../DataController.php");

// Get playlist data
$playlistList = DataController::getPlaylistList();
$playlist = null;

$userList = DataController::getUserList();

$usernames = [];
foreach ($userList as $user) {
	$usernames[$user->getUserID()] = $user->getUsername();
}

// Find the requested playlist
foreach ($playlistList as $p) {
	if ($p->getPlaylistID() == $playlistId) {
		$playlist = $p;
		break;
	}
}

// If playlist not found, redirect
if ($playlist === null) {
	header('Location: ../discover/playlists.php');
	exit;
}

// Get songs in the playlist
$songList = DataController::getSongList();
$playlistSongs = [];

foreach ($playlist->getSongIDs() as $songId) {
	foreach ($songList as $song) {
		if ($song->getSongID() == $songId) {
			$playlistSongs[] = $song;
			break;
		}
	}
}

// Prepare song queue data for player
$songQueueData = array_map(function ($song) use ($playlist) {
	return [
		'songID' => $song->getSongID(),
		'title' => $song->getTitle(),
		'artists' => $song->getArtists(),
		'fileName' => $song->getFileName(),
		'imageName' => $song->getImageName()
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
<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php"); ?>

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
					<a href="/BeatStream/admin/" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>

		<main class="main col-md ms-sm-auto px-0 py-0 justify-content-center">
			<!-- Playlist Header -->
			<div class="container mt-4">
				<div class="row">
					<div class="col-md-4 text-center">
						<?php if (!empty($playlist->getimageName())): ?>
							<img src="<?php echo "/BeatStream/images/playlist/" . htmlspecialchars($playlist->getimageName()); ?>"
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
							<?php echo $playlist->getDuration()->format("H") > 0 ? $playlist->getDuration()->format("H:i:s") : $playlist->getDuration()->format("i:s"); ?></p>

						<?php if (isset($_SESSION['userID']) && $_SESSION['userID'] == $playlist->getCreatorID()): ?>
							<a href="../create/editPlaylist.php?id=<?php echo $playlistId; ?>"
							   class="btn btn-primary mb-3">
								Edit Playlist
							</a>
						<?php else: ?>
							<p class="text-start mb-3" style="color: #96a3af">Created by: <?php
								$creatorID = $playlist->getCreatorID();
								echo isset($usernames[$creatorID]) ? htmlspecialchars($usernames[$creatorID]) : 'Unknown User';
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
<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/player.php"); ?>
</body>
</html>