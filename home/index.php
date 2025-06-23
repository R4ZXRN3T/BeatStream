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
$songList = [];
include("../DataController.php");
$songList = DataController::getSongList();
$albumList = DataController::getAlbumList();
$playlistList = DataController::getPlaylistList();
$userList = DataController::getUserList();

$usernames = [];
foreach ($userList as $user) {
	$usernames[$user->getUserID()] = $user->getUsername();
}

$recommendedSongs = [];
$IDsToRecommend = [];

if (!empty($songList)) {
	for ($i = 0; $i < ((count($songList) > 20) ? 20 : count($songList)); $i++) {
		$randomSongIndex = rand(0, count($songList) - 1);
		$songID = $songList[$randomSongIndex]->getSongID();
		if (!in_array($songID, $IDsToRecommend)) {
			$IDsToRecommend[] = $songID;
			$recommendedSongs[] = $songList[$randomSongIndex];
		} else {
			if ($i > 0) {
				$i--;
			} else {
				break;
			}
		}
	}
}


$recommendedAlbums = [];
$recommendedPlaylists = [];
$albumIDsToRecommend = [];
$playlistIDsToRecommend = [];

if (!empty($albumList)) {
	for ($i = 0; $i < ((count($albumList) > 3) ? 3 : count($albumList)); $i++) {
		$randomAlbumIndex = rand(0, count($albumList) - 1);
		$albumID = $albumList[$randomAlbumIndex]->getAlbumID();
		if (!in_array($albumID, $albumIDsToRecommend)) {
			$albumIDsToRecommend[] = $albumID;
			$recommendedAlbums[] = $albumList[$randomAlbumIndex];
		} else {
			if ($i > 0) {
				$i--;
			} else {
				break;
			}
		}
	}
}

if (!empty($playlistList)) {
	for ($i = 0; $i < ((count($playlistList) > 3) ? 3 : count($playlistList)); $i++) {
		$randomPlaylistIndex = rand(0, count($playlistList) - 1);
		$playlistID = $playlistList[$randomPlaylistIndex]->getPlaylistID();
		if (!in_array($playlistID, $playlistIDsToRecommend)) {
			$playlistIDsToRecommend[] = $playlistID;
			$recommendedPlaylists[] = $playlistList[$randomPlaylistIndex];
		} else {
			if ($i > 0) {
				$i--;
			} else {
				break;
			}
		}
	}
}

$songQueueData = array_map(function ($song) {
	return [
		'songID' => $song->getSongID(),
		'title' => $song->getTitle(),
		'artists' => $song->getArtists(),
		'fileName' => $song->getFileName(),
		'imageName' => $song->getImageName()
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

include("../topBar.php"); ?>

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
					<a href="/BeatStream/admin" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="col-md" style="min-height: 80vh; margin-left: 0; padding: 2rem;">
			<div class="container" style="max-width: 1700px;">
				<h1 class="text-center mb-4"
					style="font-weight: bold;"><?php echo "Good " . $timeOfDay . "!"; ?></h1>

				<div class="row">
					<!-- Songs Section -->
					<div class="col-md-8">
						<section class="mb-5">
							<h2 class="align-left mb-4 h-1" style="margin-left: 30px">Recommended Songs:</h2>
							<div class="row g-4">
								<?php foreach ($recommendedSongs as $song): ?>
									<div class="col-12 col-md-6">
										<div class="card shadow-sm border-0"
											 style="border-radius: 10px; cursor: pointer;">
											<div class="card-body d-flex align-items-center p-3"
												 data-song-id="<?php echo $song->getSongID(); ?>"
												 data-song-queue='<?php echo htmlspecialchars(json_encode($songQueueData)); ?>'>
												<?php if (!empty($song->getimageName())): ?>
													<img src="<?php echo "/BeatStream/images/song/" . htmlspecialchars($song->getimageName()); ?>"
														 class="me-3 rounded"
														 alt="<?php echo htmlspecialchars($song->getimageName()); ?>"
														 style="width: 50px; height: 50px; object-fit: cover;">
												<?php else: ?>
													<img src="../images/defaultSong.webp" class="me-3 rounded"
														 alt="Default Album Cover"
														 style="width: 50px; height: 50px; object-fit: cover;">
												<?php endif; ?>
												<div>
													<h5 class="card-title mb-1"
														style="font-size: 1.1rem; font-weight: bold;"><?php echo htmlspecialchars($song->getTitle()); ?></h5>
													<p class="card-text mb-0"
													   style="font-size: 0.9rem; color: #6c757d;"><?php echo htmlspecialchars($song->getArtists()); ?></p>
												</div>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</section>
					</div>

					<!-- Albums and Playlists Section -->
					<div class="col-md-4">
						<section class="mb-5 g-4">
							<h2 class="align-left mb-4" style="margin-left: 30px; ">Recommended Albums:</h2>
							<div class="row g-4">
								<?php foreach ($recommendedAlbums as $album): ?>
									<div class="col-12">
										<div class="card shadow-sm border-0" style="border-radius: 10px;">
											<div class="card-body d-flex align-items-center p-3">
												<?php if (!empty($album->getimageName())): ?>
													<img src="<?php echo "/BeatStream/images/playlist/" . htmlspecialchars($album->getimageName()); ?>"
														 class="me-3 rounded"
														 alt="<?php echo htmlspecialchars($album->getimageName()); ?>"
														 style="width: 50px; height: 50px; object-fit: cover;">
												<?php else: ?>
													<img src="../images/defaultAlbum.webp" class="me-3 rounded"
														 alt="Default Album Cover"
														 style="width: 50px; height: 50px; object-fit: cover;">
												<?php endif; ?>
												<div class="card-body">
													<h5 class="card-title"
														style="font-weight: bold;"><?php echo htmlspecialchars($album->getTitle()); ?></h5>
													<p class="card-text"
													   style="color: #6c757d;"><?php echo htmlspecialchars($album->getArtists()); ?></p>
												</div>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</section>

						<section>
							<h2 class="align-left mb-4" style="margin-left: 30px">Recommended Playlists:</h2>
							<div class="row g-4">
								<?php foreach ($recommendedPlaylists as $playlist): ?>
									<div class="col-12" style="padding-left: 2rem;">
										<div class="card shadow-sm border-0" style="border-radius: 10px;">
											<div class="card-body d-flex align-items-center p-2">
												<?php if (!empty($playlist->getimageName())): ?>
													<img src="<?php echo "/BeatStream/images/playlist/" . htmlspecialchars($playlist->getimageName()); ?>"
														 class="me-3 rounded"
														 alt="<?php echo htmlspecialchars($playlist->getimageName()); ?>"
														 style="width: 50px; height: 50px; object-fit: cover;">
												<?php else: ?>
													<img src="../images/defaultPlaylist.webp" class="me-3 rounded"
														 alt="Default Playlist Cover"
														 style="width: 50px; height: 50px; object-fit: cover;">
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
<?php include("../player.php"); ?>
</body>
</html>
