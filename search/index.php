<?php
include("../dbConnection.php");
include("../DataController.php");
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
if (!empty($_GET['search'])) {
	$searchTerm = trim($_GET['search']);
	$searchCategory = $_GET['category'] ?? 'all';

	// Get all data lists
	$allSongs = DataController::getSongList();
	$allArtists = DataController::getArtistList();
	$allAlbums = DataController::getAlbumList();
	$allPlaylists = DataController::getPlaylistList();
	$allUsers = DataController::getUserList();

	// Create user lookup array for playlist creators
	$usernames = [];
	foreach ($allUsers as $user) {
		$usernames[$user->getUserID()] = $user->getUsername();
	}

	// Search in songs
	if ($searchCategory == 'all' || $searchCategory == 'songs') {
		foreach ($allSongs as $song) {
			if (stripos($song->getTitle(), $searchTerm) !== false ||
				stripos($song->getArtists(), $searchTerm) !== false ||
				stripos($song->getGenre(), $searchTerm) !== false) {
				$songResults[] = $song;
			}
		}
	}

	// Search in artists
	if ($searchCategory == 'all' || $searchCategory == 'artists') {
		foreach ($allArtists as $artist) {
			if (stripos($artist->getName(), $searchTerm) !== false) {
				$artistResults[] = $artist;
			}
		}
	}

	// Search in albums
	if ($searchCategory == 'all' || $searchCategory == 'albums') {
		foreach ($allAlbums as $album) {
			if (stripos($album->getName(), $searchTerm) !== false ||
				stripos($album->getArtists(), $searchTerm) !== false) {
				$albumResults[] = $album;
			}
		}
	}

	// Search in playlists
	if ($searchCategory == 'all' || $searchCategory == 'playlists') {
		foreach ($allPlaylists as $playlist) {
			if (stripos($playlist->getName(), $searchTerm) !== false) {
				$playlistResults[] = $playlist;
			}
		}
	}

	// Create song queue data for player
	$songQueueData = array_map(function ($song) {
		return [
			'songID' => $song->getSongID(),
			'title' => $song->getTitle(),
			'artists' => $song->getArtists(),
			'fileName' => $song->getFileName(),
			'imageName' => $song->getImageName()
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

<?php include("../topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2">Home</a>
				<a href="../search/" class="nav-link mb-2 active">Search</a>
				<a href="../discover/" class="nav-link mb-2">Discover</a>
				<a href="/BeatStream/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin" class="nav-link mb-2">Admin</a>
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
								   name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" required>
							<button class="btn btn-primary" type="submit">Search</button>
						</div>
						<div class="form-group">
							<label class="mb-2">Filter by:</label>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="category" id="all" value="all"
									<?php echo ($searchCategory == 'all') ? 'checked' : ''; ?>>
								<label class="form-check-label" for="all">All</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="category" id="songs" value="songs"
									<?php echo ($searchCategory == 'songs') ? 'checked' : ''; ?>>
								<label class="form-check-label" for="songs">Songs</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="category" id="artists"
									   value="artists"
									<?php echo ($searchCategory == 'artists') ? 'checked' : ''; ?>>
								<label class="form-check-label" for="artists">Artists</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="category" id="albums" value="albums"
									<?php echo ($searchCategory == 'albums') ? 'checked' : ''; ?>>
								<label class="form-check-label" for="albums">Albums</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="category" id="playlists"
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
								<div class="row g-4">
									<?php foreach ($songResults as $song): ?>
										<div class="col-12 col-md-6 col-lg-4">
											<div class="card shadow-sm border-0"
												 style="border-radius: 10px; cursor: pointer;">
												<div class="card-body d-flex align-items-center p-3"
													 data-song-id="<?php echo $song->getSongID(); ?>"
													 data-song-queue='<?php echo htmlspecialchars(json_encode($songQueueData)); ?>'>
													<?php if (!empty($song->getimageName())): ?>
														<img src="<?php echo "/BeatStream/images/song/" . htmlspecialchars($song->getimageName()); ?>"
															 class="me-3 rounded"
															 alt="<?php echo htmlspecialchars($song->getTitle()); ?>"
															 style="width: 50px; height: 50px; object-fit: cover;">
													<?php else: ?>
														<img src="../images/defaultSong.webp" class="me-3 rounded"
															 alt="Default Song Cover"
															 style="width: 50px; height: 50px; object-fit: cover;">
													<?php endif; ?>
													<div>
														<h5 class="card-title mb-1"
															style="font-size: 1.1rem; font-weight: bold;">
															<?php echo htmlspecialchars($song->getTitle()); ?>
														</h5>
														<p class="card-text mb-0"
														   style="font-size: 0.9rem; color: #6c757d;">
															<?php echo htmlspecialchars($song->getArtists()); ?>
														</p>
													</div>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<!-- Artists Results -->
						<?php if (($searchCategory == 'all' || $searchCategory == 'artists') && !empty($artistResults)): ?>
							<div class="results-section">
								<h3>Artists</h3>
								<div class="result-count"><?php echo count($artistResults); ?> results</div>
								<div class="row g-4">
									<?php foreach ($artistResults as $artist): ?>
										<div class="col-12 col-md-6 col-lg-4">
											<a class="customLink"
											   href="../view/artist.php?id=<?php echo $artist->getArtistID(); ?>">
												<div class="card shadow-sm border-0" style="border-radius: 10px;">
													<div class="card-body d-flex align-items-center p-3">
														<?php if (!empty($artist->getimageName())): ?>
															<img src="<?php echo "/BeatStream/images/artist/" . htmlspecialchars($artist->getimageName()); ?>"
																 class="me-3 rounded"
																 alt="<?php echo htmlspecialchars($artist->getName()); ?>"
																 style="width: 50px; height: 50px; object-fit: cover;">
														<?php else: ?>
															<img src="../images/defaultArtist.webp" class="me-3 rounded"
																 alt="Default Artist Image"
																 style="width: 50px; height: 50px; object-fit: cover;">
														<?php endif; ?>
														<div>
															<h5 class="card-title mb-1"
																style="font-size: 1.1rem; font-weight: bold;">
																<?php echo htmlspecialchars($artist->getName()); ?>
															</h5>
															<p class="card-text mb-0"
															   style="font-size: 0.9rem; color: #6c757d;">
																Artist
															</p>
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
								<div class="row g-4">
									<?php foreach ($albumResults as $album): ?>
										<div class="col-12 col-md-6 col-lg-4">
											<a class="customLink"
											   href="../view/album.php?id=<?php echo $album->getAlbumID(); ?>">
												<div class="card shadow-sm border-0" style="border-radius: 10px;">
													<div class="card-body d-flex align-items-center p-3">
														<?php if (!empty($album->getimageName())): ?>
															<img src="<?php echo "/BeatStream/images/album/" . htmlspecialchars($album->getimageName()); ?>"
																 class="me-3 rounded"
																 alt="<?php echo htmlspecialchars($album->getName()); ?>"
																 style="width: 50px; height: 50px; object-fit: cover;">
														<?php else: ?>
															<img src="../images/defaultAlbum.webp" class="me-3 rounded"
																 alt="Default Album Cover"
																 style="width: 50px; height: 50px; object-fit: cover;">
														<?php endif; ?>
														<div>
															<h5 class="card-title mb-1"
																style="font-size: 1.1rem; font-weight: bold;">
																<?php echo htmlspecialchars($album->getName()); ?>
															</h5>
															<p class="card-text mb-0"
															   style="font-size: 0.9rem; color: #6c757d;">
																<?php echo htmlspecialchars($album->getArtists()); ?>
															</p>
														</div>
													</div>
												</div>
											</a>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<!-- Playlists Results -->
						<?php if (($searchCategory == 'all' || $searchCategory == 'playlists') && !empty($playlistResults)): ?>
							<div class="results-section">
								<h3>Playlists</h3>
								<div class="result-count"><?php echo count($playlistResults); ?> results</div>
								<div class="row g-4">
									<?php foreach ($playlistResults as $playlist): ?>
										<div class="col-12 col-md-6 col-lg-4">
											<a class="customLink"
											   href="../view/playlist.php?id=<?php echo $playlist->getPlaylistID(); ?>">
												<div class="card shadow-sm border-0" style="border-radius: 10px;">
													<div class="card-body d-flex align-items-center p-3">
														<?php if (!empty($playlist->getimageName())): ?>
															<img src="<?php echo "/BeatStream/images/playlist/" . htmlspecialchars($playlist->getimageName()); ?>"
																 class="me-3 rounded"
																 alt="<?php echo htmlspecialchars($playlist->getName()); ?>"
																 style="width: 50px; height: 50px; object-fit: cover;">
														<?php else: ?>
															<img src="../images/defaultPlaylist.webp"
																 class="me-3 rounded"
																 alt="Default Playlist Cover"
																 style="width: 50px; height: 50px; object-fit: cover;">
														<?php endif; ?>
														<div>
															<h5 class="card-title mb-1"
																style="font-size: 1.1rem; font-weight: bold;">
																<?php echo htmlspecialchars($playlist->getName()); ?>
															</h5>
															<p class="card-text mb-0"
															   style="font-size: 0.9rem; color: #6c757d;">
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
							</div>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</main>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php include("../player.php"); ?>

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
		const filterRadios = document.querySelectorAll('input[name="category"]');
		filterRadios.forEach(radio => {
			radio.addEventListener('change', function () {
				// Only submit if there's a search term
				if (document.querySelector('input[name="search"]').value.trim() !== '') {
					this.form.submit();
				}
			});
		});
	});
</script>
</body>
</html>