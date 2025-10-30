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

// Initialize search results
$songResults = [];
$artistResults = [];
$albumResults = [];
$playlistResults = [];
$searchTerm = '';
$searchCategory = 'all';

// Handle search request
if (!empty($_GET['q'])) {
	$searchTerm = trim($_GET['q']);
	$searchCategory = $_GET['c'] ?? 'all';

	if ($searchCategory == 'all' || $searchCategory == 'songs') require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php";
	if ($searchCategory == 'all' || $searchCategory == 'artists') require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/ArtistController.php";
	if ($searchCategory == 'all' || $searchCategory == 'albums') require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/AlbumController.php";
	if ($searchCategory == 'all' || $searchCategory == 'playlists') require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/PlaylistController.php";

	// Only use specific controllers for searching
	if ($searchCategory == 'all' || $searchCategory == 'songs') $songResults = SongController::searchSong($searchTerm);
	if ($searchCategory == 'all' || $searchCategory == 'artists') $artistResults = ArtistController::searchArtist($searchTerm);
	if ($searchCategory == 'all' || $searchCategory == 'albums') $albumResults = AlbumController::searchAlbum($searchTerm);
	if ($searchCategory == 'all' || $searchCategory == 'playlists') $playlistResults = PlaylistController::searchPlaylist($searchTerm);

	// Create song queue data for player
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
	}, $songResults);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - Search</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
	<link href="../mainStyle.css" rel="stylesheet">
</head>
<body>

<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2">Home</a>
				<a href="../search/" class="nav-link mb-2 active">Search</a>
				<a href="../discover/" class="nav-link mb-2">Discover</a>
				<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>

		<!-- Main Content -->
		<main class="main col-md">
			<div class="container" style="max-width: 1700px;">
				<h1 class="text-start mb-4" style="font-weight: bold;">Search</h1>

				<!-- Search Form -->
				<div class="search-container">
					<form action="" method="GET" class="mb-4">
						<div class="input-group mb-3">
							<input type="text" class="form-control"
								   placeholder="Search for songs, artists, albums, or playlists..."
								   name="q" value="<?php echo htmlspecialchars($searchTerm); ?>" required>
							<button class="btn btn-primary" type="submit">Search</button>
						</div>
						<div class="form-group">
							<label class="mb-2">Filter by:</label>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="c" id="all" value="all"
										<?php echo ($searchCategory == 'all') ? 'checked' : ''; ?>>
								<label class="form-check-label" for="all">All</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="c" id="songs" value="songs"
										<?php echo ($searchCategory == 'songs') ? 'checked' : ''; ?>>
								<label class="form-check-label" for="songs">Songs</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="c" id="artists"
									   value="artists"
										<?php echo ($searchCategory == 'artists') ? 'checked' : ''; ?>>
								<label class="form-check-label" for="artists">Artists</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="c" id="albums" value="albums"
										<?php echo ($searchCategory == 'albums') ? 'checked' : ''; ?>>
								<label class="form-check-label" for="albums">Albums</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="c" id="playlists"
									   value="playlists"
										<?php echo ($searchCategory == 'playlists') ? 'checked' : ''; ?>>
								<label class="form-check-label" for="playlists">Playlists</label>
							</div>
						</div>
					</form>
				</div>

				<?php if (!empty($searchTerm)): ?>
					<h2 class="mb-4">Results for "<?php echo htmlspecialchars($searchTerm); ?>"</h2>

					<?php if (empty($songResults) && empty($artistResults) && empty($albumResults) && empty($playlistResults)): ?>
						<div class="no-results">
							<h3>No results found</h3>
							<p>Try a different search term or filter.</p>
						</div>
					<?php else: ?>
						<!-- Songs Results -->
						<?php if (($searchCategory == 'all' || $searchCategory == 'songs') && !empty($songResults)): ?>
							<div class="results-section">
								<h3>Songs</h3>
								<div class="result-count"><?php echo count($songResults); ?> results</div>
								<?php
								$songListOptions = [
										'layout' => 'grid',
										'showIndex' => false,
										'showDuration' => true,
										'showArtistLinks' => true,
										'containerClass' => 'col-12 col-md-6 col-lg-4',
										'emptyMessage' => 'No songs found.'
								];

								$songs = $songResults;
								$options = $songListOptions;
								include('../components/song-list.php');
								?>
							</div>
						<?php endif; ?>

						<!-- Artists Results -->
						<?php if (($searchCategory == 'all' || $searchCategory == 'artists') && !empty($artistResults)): ?>
							<div class="results-section">
								<h3>Artists</h3>
								<div class="result-count"><?php echo count($artistResults); ?> results</div>
								<div class="row g-4">
									<?php foreach ($artistResults as $artist): ?>
										<div class="col-md-4 mb-4">
											<a href="../view/artist.php?id=<?php echo $artist->getArtistID(); ?>"
											   class="text-decoration-none">
												<div class="card shadow-sm border-0" style="border-radius: 10px;">
													<div class="card-body d-flex align-items-center p-3">
														<?php if (!empty($artist->getThumbnailName())): ?>
															<img src="<?php echo "{$GLOBALS['PROJECT_ROOT']}/images/artist/thumbnail/" . htmlspecialchars($artist->getThumbnailName()); ?>"
																 class="me-3 rounded"
																 alt="<?php echo htmlspecialchars($artist->getName()); ?>"
																 style="width: 60px; height: 60px; object-fit: cover;">
														<?php else: ?>
															<img src="../images/defaultArtist.webp" class="me-3 rounded"
																 alt="Default Artist image"
																 style="width: 60px; height: 60px; object-fit: cover;">
														<?php endif; ?>
														<div>
															<h5 class="card-title mb-1"
																style="font-size: 1.1rem; font-weight: bold;"><?php echo htmlspecialchars($artist->getName()); ?></h5>
														</div>
													</div>
												</div>
											</a>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<!-- Albums Results -->
						<?php if (($searchCategory == 'all' || $searchCategory == 'albums') && !empty($albumResults)): ?>
							<div class="results-section">
								<h3>Albums</h3>
								<div class="result-count"><?php echo count($albumResults); ?> results</div>
								<?php
								$albumList = $albumResults;
								$options = [
										'containerClass' => 'col-12 col-md-6 col-lg-4',
										'emptyMessage' => 'No albums found.',
										'large' => false,
										'compact' => true,
								];
								include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/album-list.php");
								?>
							</div>
						<?php endif; ?>

						<!-- Playlists Results -->
						<?php if (($searchCategory == 'all' || $searchCategory == 'playlists') && !empty($playlistResults)): ?>
							<div class="results-section">
								<h3>Playlists</h3>
								<div class="result-count"><?php echo count($playlistResults); ?> results</div>
								<?php
								$options = [
										'containerClass' => 'col-12 col-md-6 col-lg-4',
										'showCreator' => true,
										'emptyMessage' => 'No playlists found.',
										'homepageStyle' => false
								];
								$playlistList = $playlistResults;
								include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/playlist-list.php");
								?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</main>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/player.php"); ?>

<script>
	// Add event listeners for song playback
	document.addEventListener('DOMContentLoaded', function () {
		const songCards = document.querySelectorAll('[data-song-id]');
		songCards.forEach(card => {
			card.addEventListener('click', function () {
				const songId = this.getAttribute('data-song-id');
				const songQueue = JSON.parse(this.getAttribute('data-song-queue'));

				// You'll need to implement the actual player functionality
				// This would connect to your existing player.php functionality
				if (typeof playQueue === 'function') {
					playQueue(songQueue, songId);
				}
			});
		});
	});

	document.addEventListener('DOMContentLoaded', function () {
		const filterRadios = document.querySelectorAll('input[name="c"]');
		filterRadios.forEach(radio => {
			radio.addEventListener('change', function () {
				// Only submit if there's a search term
				if (document.querySelector('input[name="q"]').value.trim() !== '') {
					this.form.submit();
				}
			});
		});
	});
</script>
<script src="<?= $GLOBALS['PROJECT_ROOT'] ?>/addMenuContent.js"></script>
</body>
</html>