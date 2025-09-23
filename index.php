<?php
include("dbConnection.php");
session_start();
$isAdmin = false;
if (isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true) {
	$stmt = DBConn::getConn()->prepare("SELECT isAdmin FROM user WHERE userID = ?;");
	$stmt->bind_param("i", $_SESSION['userID']);
	$stmt->execute();
	$isAdmin = $stmt->get_result()->fetch_assoc()['isAdmin'] ?? false;
	$stmt->close();
	$_SESSION['isAdmin'] = $isAdmin;
}
?>
<!Doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - Home</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="favicon.ico" rel="icon">
	<link href="mainStyle.css" rel="stylesheet">
</head>

<body>

<?php

require_once($GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php");
require_once($GLOBALS['PROJECT_ROOT_DIR'] . "/controller/AlbumController.php");
require_once($GLOBALS['PROJECT_ROOT_DIR'] . "/controller/PlaylistController.php");
require_once($GLOBALS['PROJECT_ROOT_DIR'] . "/controller/UserController.php");

$userList = UserController::getUserList();
$usernames = [];
foreach ($userList as $user) {
	$usernames[$user->getUserID()] = $user->getUsername();
}

$recommendedSongs = SongController::getRandomSongs();
$recommendedAlbums = AlbumController::getRandomAlbums();
$recommendedPlaylists = PlaylistController::getPlaylistList();

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
}, $recommendedSongs);

$timeOfDay = "Day";

$currentHour = date('H');
switch ($currentHour) {
	case ($currentHour >= 5 && $currentHour < 12):
		$timeOfDay = "Morning";
		break;
	case 12:
		$timeOfDay = "Noon";
		break;
	case ($currentHour > 12 && $currentHour < 18):
		$timeOfDay = "Afternoon";
		break;
	case ($currentHour >= 18 && $currentHour < 21):
		$timeOfDay = "Evening";
		break;
	case ($currentHour >= 21 || $currentHour < 5):
		$timeOfDay = "Night";
		break;
	default:
		$timeOfDay = "Day";
		break;
}

include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="" class="nav-link mb-2 active">Home</a>
				<a href="search/" class="nav-link mb-2">Search</a>
				<a href="discover/" class="nav-link mb-2">Discover</a>
				<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="main col-md">
			<div class="container" style="max-width: 1700px;">
				<h1 class="text-start mb-4"
					style="font-weight: bold;"><?php echo "Good " . $timeOfDay . "!"; ?></h1>

				<div class="row">
					<!-- Songs Section -->
					<div class="col-md-8">
						<section class="mb-5">
							<h2 class="text-start mb-4 recommended-header">Recommended Songs:</h2>
							<?php
							$songListOptions = [
									'layout' => 'grid',
									'showIndex' => false,
									'showDuration' => true,
									'showArtistLinks' => true,
									'containerClass' => 'col-12 col-md-6',
									'emptyMessage' => 'No recommended songs available.'
							];

							$songs = $recommendedSongs;
							$options = $songListOptions;
							include('components/song-list.php');
							?>
						</section>
					</div>

					<!-- Albums and Playlists Section -->
					<div class="col-md-4">
						<section class="mb-5 g-4 a-p-column">
							<h2 class="text-start mb-4 recommended-header">Recommended Albums:</h2>
							<?php
							$albumList = $recommendedAlbums;
							$options = [
									'containerClass' => 'col-12',
									'emptyMessage' => 'No recommended albums available.',
									'compact' => false
							];
							include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/album-list.php");
							?>
						</section>

						<section class="mb-5 g-4 a-p-column">
							<h2 class="text-start mb-4 recommended-header">Recommended Playlists:</h2>
							<?php
							$playlistList = $recommendedPlaylists;
							$options = [
									'containerClass' => 'col-12',
									'showCreator' => true,
									'emptyMessage' => 'No recommended playlists available.',
									'compact' => false,
							];
							include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/playlist-list.php");
							?>
						</section>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/player.php"); ?>
</body>
</html>