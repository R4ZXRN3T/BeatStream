<?php
include("../dbConnection.php");
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
	<link href="../favicon.ico" rel="icon">
	<link href="../mainStyle.css" rel="stylesheet">
</head>

<body>

<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/SongController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/AlbumController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/PlaylistController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/UserController.php");

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

include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2 active">Home</a>
				<a href="../search/" class="nav-link mb-2">Search</a>
				<a href="../discover/" class="nav-link mb-2">Discover</a>
				<a href="/BeatStream/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin/" class="nav-link mb-2">Admin</a>
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
							include('../components/song-list.php');
							?>
						</section>
					</div>

					<!-- Albums and Playlists Section -->
					<div class="col-md-4">
						<section class="mb-5 g-4 a-p-column">
							<h2 class="text-start mb-4 recommended-header">Recommended Albums:</h2>
							<div class="row g-4">
								<?php foreach ($recommendedAlbums as $album): ?>
									<div class="col-12">
										<div class="card shadow-sm border-0" style="border-radius: 10px;">
											<a class="on-card-link"
											   href="../view/album.php?id=<?php echo $album->getAlbumID() ?>">
												<div class="card-body d-flex align-items-center p-2"
													 data-song-id="album-<?php echo $album->getAlbumID(); ?>">
													<?php if (!empty($album->getimageName())): ?>
														<img src="<?php echo "/BeatStream/images/album/thumnail/" . htmlspecialchars($album->getThumbnailName()); ?>"
															 class="me-3 rounded"
															 alt="<?php echo htmlspecialchars($album->getThumbnailName()); ?>"
															 style="width: 80px; height: 80px; object-fit: cover;">
													<?php else: ?>
														<img src="../images/defaultAlbum.webp" class="me-3 rounded"
															 alt="Default Album Cover"
															 style="width: 80px; height: 80px; object-fit: cover;">
													<?php endif; ?>
													<div class="card-body">
														<h5 class="card-title" style="font-weight: bold;">
															<?php echo htmlspecialchars($album->getName()); ?>
														</h5>
														<p class="card-text" style="color: #6c757d;">
															<?php echo htmlspecialchars(implode(", ", $album->getArtists())); ?>
														</p>
													</div>
												</div>
											</a>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</section>

						<section class="mb-5 g-4 a-p-column">
							<h2 class="text-start mb-4 recommended-header">Recommended Playlists:</h2>
							<div class="row g-4">
								<?php foreach ($recommendedPlaylists as $playlist): ?>
									<div class="col-12">
										<a class="on-card-link"
										   href="../view/playlist.php?id=<?php echo $playlist->getPlaylistID() ?>">
											<div class="card shadow-sm border-0" style="border-radius: 10px;">
												<div class="card-body d-flex align-items-center p-2">
													<?php if (!empty($playlist->getThumbnailName())): ?>
														<img src="<?php echo "/BeatStream/images/playlist/thumbnail/" . htmlspecialchars($playlist->getThumbnailName()); ?>"
															 class="me-3 rounded"
															 alt="<?php echo htmlspecialchars($playlist->getThumbnailName()); ?>"
															 style="width: 64px; height: 64px; object-fit: cover;">
													<?php else: ?>
														<img src="../images/defaultPlaylist.webp" class="me-3 rounded"
															 alt="Default Playlist Cover"
															 style="width: 80px; height: 80px; object-fit: cover;">
													<?php endif; ?>
													<div class="card-body">
														<h5 class="card-title"
															style="font-weight: bold;"><?php echo htmlspecialchars($playlist->getName()); ?></h5>
														<p class="card-text" style="color: #6c757d;">
															<?php
															$creatorID = $playlist->getCreatorID();
															echo isset($usernames[$creatorID]) ? htmlspecialchars($usernames[$creatorID]) : 'Unknown User';
															?>
														</p>
													</div>
												</div>
											</div>
										</a>
									</div>
								<?php endforeach; ?>
							</div>
						</section>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/player.php"); ?>
</body>
</html>
