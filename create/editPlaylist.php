<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
	header('Location: ../login.php');
	exit;
}

// Check if playlist ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	header('Location: ../discover/playlists.php');
	exit;
}

$playlistId = (int)$_GET['id'];

// Include data controller
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/PlaylistController.php";
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php";

// Get playlist data
$playlist = PlaylistController::getPlaylistById($playlistId);

// If playlist not found, redirect
if ($playlist === null) {
	header('Location: ../discover/playlists.php');
	exit;
}

// Check if the user owns this playlist
if ($_SESSION['userID'] != $playlist->getCreatorID()) {
	header('Location: ../view/playlist.php?id=' . $playlistId);
	exit;
}

// Get all songs for selection
$allSongs = SongController::getSongList();

// Get songs currently in the playlist
$playlistSongIds = $playlist->getSongIDs();
$playlistSongs = [];
$availableSongs = [];

foreach ($allSongs as $song) {
	if (in_array($song->getSongID(), $playlistSongIds)) {
		$playlistSongs[] = $song;
	} else {
		$availableSongs[] = $song;
	}
}

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['save_changes'])) {
		$newName = trim($_POST['playlist_name']);

		// Validate name
		if (empty($newName)) {
			$errorMessage = "Playlist name cannot be empty";
		} else {
			// Handle image upload if provided
			$imageName = $playlist->getimageName();

			if (isset($_FILES['playlist_image']) && $_FILES['playlist_image']['size'] > 0) {
				$targetDir = $GLOBALS['PROJECT_ROOT_DIR'] . "/images/playlist/";
				$fileExtension = strtolower(pathinfo($_FILES['playlist_image']['name'], PATHINFO_EXTENSION));

				// Check if file is an actual image
				$check = getimagesize($_FILES['playlist_image']['tmp_name']);
				if ($check === false) {
					$errorMessage = "File is not an image.";
				} else {
					// Generate unique filename
					$newImageName = uniqid() . '.' . $fileExtension;
					$targetFile = $targetDir . $newImageName;

					// Try to upload file
					if (move_uploaded_file($_FILES['playlist_image']['tmp_name'], $targetFile)) {
						// Delete old image if it exists
						if (!empty($imageName) && file_exists($targetDir . $imageName)) {
							unlink($targetDir . $imageName);
						}
						$imageName = $newImageName;
					} else {
						$errorMessage = "Sorry, there was an error uploading your file.";
					}
				}
			}

			// Get selected songs
			$selectedSongs = $_POST['selected_songs'] ?? [];

			if (empty($selectedSongs)) {
				$errorMessage = "Playlist must contain at least one song.";
			} else {
				// Calculate new duration and length
				$totalDuration = new DateTime('00:00:00');
				$length = count($selectedSongs);

				foreach ($allSongs as $song) {
					if (in_array($song->getSongID(), $selectedSongs)) {
						// Add song duration to total
						$songDuration = $song->getSongLength();

						$hours = (int)$songDuration->format('H');
						$minutes = (int)$songDuration->format('i');
						$seconds = (int)$songDuration->format('s');

						$interval = new DateInterval("PT{$hours}H{$minutes}M{$seconds}S");
						$totalDuration->add($interval);
					}
				}

				// Update playlist in database
				try {
					// First delete the old playlist
					PlaylistController::deletePlaylist($playlistId);

					// Create a new playlist with the same ID
					$updatedPlaylist = new Playlist(
							$playlistId,
							$newName,
							$selectedSongs,
							$totalDuration->format('H:i:s'),
							$length,
							$imageName,
							$playlist->getCreatorID()
					);

					PlaylistController::insertPlaylist($updatedPlaylist);

					$successMessage = "Playlist updated successfully!";
					// Reload the playlist data
					$playlist = PlaylistController::getPlaylistById($playlistId);

					// Refresh song lists
					$playlistSongIds = $playlist->getSongIDs();
					$playlistSongs = [];
					$availableSongs = [];

					foreach ($allSongs as $song) {
						if (in_array($song->getSongID(), $playlistSongIds)) {
							$playlistSongs[] = $song;
						} else {
							$availableSongs[] = $song;
						}
					}
				} catch (Exception $e) {
					$errorMessage = "Error updating playlist: " . $e->getMessage();
				}
			}
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - Edit Playlist</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
	<link href="../mainStyle.css" rel="stylesheet">
</head>

<body>
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<?php
			$activePage = 'create';
			include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/sidebar.php");
		?>

		<main class="main col-md ms-sm-auto px-0 py-0 justify-content-center">
			<div class="container mt-4" style="max-width: 800px;">
				<h1 class="mb-4">Edit Playlist</h1>

				<?php if ($successMessage): ?>
					<div class="alert alert-success alert-dismissible fade show" role="alert">
						<?php echo $successMessage; ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php endif; ?>

				<?php if ($errorMessage): ?>
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<?php echo $errorMessage; ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php endif; ?>

				<form method="post" enctype="multipart/form-data">
					<div class="row">
						<div class="col-md-4 text-center">
							<div class="mb-3">
								<?php if (!empty($playlist->getimageName())): ?>
									<img src="<?php echo "/BeatStream/images/playlist/" . htmlspecialchars($playlist->getimageName()); ?>"
										 class="img-fluid rounded shadow mb-2"
										 alt="<?php echo htmlspecialchars($playlist->getName()); ?>"
										 style="max-width: 200px; height: 200px; object-fit: cover;">
								<?php else: ?>
									<img src="../images/defaultPlaylist.webp" class="img-fluid rounded shadow mb-2"
										 alt="Default Playlist Cover"
										 style="max-width: 200px; height: 200px; object-fit: cover;">
								<?php endif; ?>
								<label for="playlist_image" class="form-label">Change Cover Image</label>
								<input type="file" class="form-control" id="playlist_image" name="playlist_image"
									   accept="image/*">
							</div>
						</div>
						<div class="col-md-8">
							<div class="mb-3">
								<label for="playlist_name" class="form-label">Playlist Name</label>
								<input type="text" class="form-control" id="playlist_name" name="playlist_name"
									   value="<?php echo htmlspecialchars($playlist->getName()); ?>" required>
							</div>

							<div class="mb-3">
								<p>Current playlist: <?php echo count($playlistSongs); ?> songs ·
									<?php echo $playlist->getDuration()->format("H") > 0 ? $playlist->getDuration()->format("H:i:s") : $playlist->getDuration()->format("i:s"); ?></p>
							</div>
						</div>
					</div>

					<div class="row mt-4 edit-playlist">
						<div class="col-md-12">
							<h4 class="mb-3">Manage Songs</h4>
							<div class="row">
								<div class="col-md-5">
									<h5>Available Songs</h5>
									<div class="search-box">
										<input type="text" class="form-control" id="search_available"
											   placeholder="Search available songs...">
									</div>
									<div class="song-list" id="available_songs">
										<?php foreach ($availableSongs as $song): ?>
											<div class="song-item" data-song-id="<?php echo $song->getSongID(); ?>"
												 data-title="<?php echo strtolower(htmlspecialchars($song->getTitle())); ?>"
												 data-artist="<?php echo strtolower(htmlspecialchars(implode(', ', $song->getArtists()))); ?>">
												<?php if (!empty($song->getimageName())): ?>
													<img class="song-image"
														 src="<?php echo "/BeatStream/images/song/" . htmlspecialchars($song->getimageName()); ?>"
														 alt="<?php echo htmlspecialchars($song->getTitle()); ?>">
												<?php else: ?>
													<img class="song-image" src="../images/defaultSong.webp"
														 alt="Default Song Cover">
												<?php endif; ?>
												<div class="song-info">
													<p class="song-title"><?php echo htmlspecialchars($song->getTitle()); ?></p>
													<p class="song-artist"><?php echo htmlspecialchars(implode(', ', $song->getArtists())); ?></p>
												</div>
												<span class="song-duration"><?php echo $song->getSongLength()->format("i:s"); ?></span>
											</div>
										<?php endforeach; ?>

										<?php if (empty($availableSongs)): ?>
											<p class="text-muted text-center mt-3" id="no_available_songs">No more songs
												available</p>
										<?php endif; ?>
									</div>
								</div>

								<div class="col-md-2 d-flex flex-column align-items-center justify-content-center">
									<button type="button" id="add_song" class="btn btn-primary mb-2">&rarr;</button>
									<button type="button" id="remove_song" class="btn btn-secondary">&larr;</button>
								</div>

								<div class="col-md-5">
									<h5>Selected Songs</h5>
									<div class="search-box">
										<input type="text" class="form-control" id="search_selected"
											   placeholder="Search selected songs...">
									</div>
									<div class="song-list" id="selected_songs">
										<?php foreach ($playlistSongs as $song): ?>
											<div class="song-item" data-song-id="<?php echo $song->getSongID(); ?>"
												 data-title="<?php echo strtolower(htmlspecialchars($song->getTitle())); ?>"
												 data-artist="<?php echo strtolower(htmlspecialchars(implode(', ', $song->getArtists()))); ?>">
												<input type="hidden" name="selected_songs[]"
													   value="<?php echo $song->getSongID(); ?>">
												<?php if (!empty($song->getimageName())): ?>
													<img class="song-image"
														 src="<?php echo "/BeatStream/images/song/" . htmlspecialchars($song->getimageName()); ?>"
														 alt="<?php echo htmlspecialchars($song->getTitle()); ?>">
												<?php else: ?>
													<img class="song-image" src="../images/defaultSong.webp"
														 alt="Default Song Cover">
												<?php endif; ?>
												<div class="song-info">
													<p class="song-title"><?php echo htmlspecialchars($song->getTitle()); ?></p>
													<p class="song-artist"><?php echo htmlspecialchars(implode(', ', $song->getArtists())); ?></p>
												</div>
												<span class="song-duration"><?php echo $song->getSongLength()->format("i:s"); ?></span>
											</div>
										<?php endforeach; ?>

										<?php if (empty($playlistSongs)): ?>
											<p class="text-muted text-center mt-3" id="no_selected_songs">No songs
												selected</p>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row mt-4 mb-5">
						<div class="col-md-12">
							<button type="submit" name="save_changes" class="btn btn-success">Save Changes</button>
							<a href="../view/playlist.php?id=<?php echo $playlistId; ?>" class="btn btn-secondary">Cancel</a>
						</div>
					</div>
				</form>
			</div>
		</main>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		const availableSongs = document.getElementById('available_songs');
		const selectedSongs = document.getElementById('selected_songs');
		const addButton = document.getElementById('add_song');
		const removeButton = document.getElementById('remove_song');

		let selectedAvailableSong = null;
		let selectedPlaylistSong = null;

		// Handle selecting songs in available list
		availableSongs.addEventListener('click', function (e) {
			const songItem = findSongItem(e.target);
			if (songItem) {
				if (selectedAvailableSong) {
					selectedAvailableSong.classList.remove('selected');
				}
				songItem.classList.add('selected');
				selectedAvailableSong = songItem;

				if (selectedPlaylistSong) {
					selectedPlaylistSong.classList.remove('selected');
					selectedPlaylistSong = null;
				}
			}
		});

		// Handle selecting songs in selected list
		selectedSongs.addEventListener('click', function (e) {
			const songItem = findSongItem(e.target);
			if (songItem) {
				if (selectedPlaylistSong) {
					selectedPlaylistSong.classList.remove('selected');
				}
				songItem.classList.add('selected');
				selectedPlaylistSong = songItem;

				if (selectedAvailableSong) {
					selectedAvailableSong.classList.remove('selected');
					selectedAvailableSong = null;
				}
			}
		});

		// Add song to playlist
		addButton.addEventListener('click', function () {
			if (selectedAvailableSong) {
				const songId = selectedAvailableSong.getAttribute('data-song-id');

				// Create a copy of the song for selected list
				const newSelectedSong = selectedAvailableSong.cloneNode(true);

				// Add hidden input for form submission
				const hiddenInput = document.createElement('input');
				hiddenInput.type = 'hidden';
				hiddenInput.name = 'selected_songs[]';
				hiddenInput.value = songId;
				newSelectedSong.prepend(hiddenInput);

				// Remove selection highlight
				newSelectedSong.classList.remove('selected');

				// Add to selected songs
				selectedSongs.appendChild(newSelectedSong);

				// Remove from available songs
				availableSongs.removeChild(selectedAvailableSong);

				// Reset selection
				selectedAvailableSong = null;

				// Remove "no songs selected" message if present
				const noSongsMsg = document.getElementById('no_selected_songs');
				if (noSongsMsg) {
					noSongsMsg.style.display = 'none';
				}
			}
		});

		// Remove song from playlist
		removeButton.addEventListener('click', function () {
			if (selectedPlaylistSong) {
				const songId = selectedPlaylistSong.getAttribute('data-song-id');

				// Create a copy for available list
				const newAvailableSong = selectedPlaylistSong.cloneNode(true);

				// Remove hidden input
				const hiddenInput = newAvailableSong.querySelector('input[type="hidden"]');
				if (hiddenInput) {
					newAvailableSong.removeChild(hiddenInput);
				}

				// Remove selection highlight
				newAvailableSong.classList.remove('selected');

				// Add to available songs
				availableSongs.appendChild(newAvailableSong);

				// Remove from selected songs
				selectedSongs.removeChild(selectedPlaylistSong);

				// Reset selection
				selectedPlaylistSong = null;

				// Show "no songs selected" message if playlist is empty
				if (selectedSongs.querySelectorAll('.song-item').length === 0) {
					const noSongsMsg = document.getElementById('no_selected_songs');
					if (noSongsMsg) {
						noSongsMsg.style.display = 'block';
					} else {
						const newMsg = document.createElement('p');
						newMsg.id = 'no_selected_songs';
						newMsg.className = 'text-muted text-center mt-3';
						newMsg.textContent = 'No songs selected';
						selectedSongs.appendChild(newMsg);
					}
				}
			}
		});

		// Helper function to find the song item element
		function findSongItem(element) {
			while (element && !element.classList.contains('song-item')) {
				element = element.parentElement;
			}
			return element;
		}
	});
</script>
</body>
</html>

