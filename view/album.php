<?php
session_start();

// Check if album ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	header('Location: ../discover/albums.php');
	exit;
}

$albumId = (int)$_GET['id'];

// Include controllers
require_once("../controller/AlbumController.php");
require_once("../controller/SongController.php");

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
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<?php
		include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/sidebar.php");
		?>

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
						<?php if ($album->isSingle()): ?>
							<h4 class="text mb-2" style="color: #7c8991">Single</h4>
						<?php endif; ?>
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
						<button class="btn btn-primary" id="downloadAlbumBtn" data-album-id="<?= $albumId ?>">Download Album
						</button>
						<button class="btn btn-secondary" id="cancelDownloadAlbumBtn">Cancel
						</button>
						<div id="downloadProgress" class="mt-3" style="display: none;">
							<div class="progress" style="height: 10px !important; width: 50%;">
								<div class="progress-bar progress-bar-striped progress-bar-animated"
									 role="progressbar"
									 id="downloadProgressBar"
									 style="width: 0; font-size: 10px; line-height: 50px;">0%
								</div>
							</div>
							<small class="text" id="downloadStatus">Preparing download...</small>
						</div>
					</div>
				</div>
			</div>

			<!-- Song List -->
			<div class="container" style="max-width: 800px; margin-top: 50px;">
				<?php
				$songListOptions = [
						'layout' => 'list',
						'showIndex' => false,
						'showDuration' => true,
						'showArtistLinks' => true,
						'containerClass' => 'col-12',
						'emptyMessage' => 'No songs available in this album.',
						'albumView' => true
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
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/player.php"); ?>
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
<script src="<?= $GLOBALS['PROJECT_ROOT'] ?>/addMenuContent.js"></script>
<script>
	// Holds the active download controller (if any)
	let albumDownloadController = null;

	function setDownloadUIState({downloading}) {
		const btnDownload = document.getElementById('downloadAlbumBtn');
		const btnCancel = document.getElementById('cancelDownloadAlbumBtn');
		const progressContainer = document.getElementById('downloadProgress');
		const progressBar = document.getElementById('downloadProgressBar');
		const statusText = document.getElementById('downloadStatus');

		if (downloading) {
			btnDownload.disabled = true;
			btnCancel.style.visibility = 'visible';
			btnCancel.disabled = false;
			progressContainer.style.display = 'block';
			progressBar.classList.add('progress-bar-animated');
			progressBar.classList.remove('bg-success', 'bg-danger');
			progressBar.style.width = '0%';
			progressBar.textContent = '0%';
			statusText.textContent = 'Preparing download...';
		} else {
			btnDownload.disabled = false;
			btnCancel.style.visibility = 'hidden';
			btnCancel.disabled = true;
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		const btnDownload = document.getElementById('downloadAlbumBtn');
		const btnCancel = document.getElementById('cancelDownloadAlbumBtn');
		const progressContainer = document.getElementById('downloadProgress');
		const progressBar = document.getElementById('downloadProgressBar');
		const statusText = document.getElementById('downloadStatus');

		// Disable cancel by default
		btnCancel.style.visibility = 'hidden';

		btnCancel.addEventListener('click', function () {
			if (albumDownloadController) {
				albumDownloadController.abort();
			}
		});

		btnDownload.addEventListener('click', async function () {
			const albumId = this.dataset.albumId;

			// If something is already downloading, abort it before starting a new one
			if (albumDownloadController) {
				albumDownloadController.abort();
			}

			albumDownloadController = new AbortController();
			const {signal} = albumDownloadController;

			setDownloadUIState({downloading: true});

			try {
				const response = await fetch(
					`<?= $GLOBALS['PROJECT_ROOT'] ?>/api/download_album.php?id=${albumId}`,
					{signal}
				);

				if (!response.ok) throw new Error('Download failed');

				const contentLength = response.headers.get('content-length');
				const total = contentLength ? parseInt(contentLength, 10) : 0;
				let loaded = 0;

				const reader = response.body.getReader();
				const chunks = [];

				while (true) {
					const {done, value} = await reader.read();
					if (done) break;

					chunks.push(value);
					loaded += value.length;

					if (total) {
						const progress = Math.round((loaded / total) * 100);
						progressBar.style.width = progress + '%';
						progressBar.textContent = progress + '%';
						statusText.textContent =
							`Downloading... ${(loaded / 1024 / 1024).toFixed(2)} MB / ${(total / 1024 / 1024).toFixed(2)} MB`;
					} else {
						statusText.textContent = 'Downloading...';
					}
				}

				const blob = new Blob(chunks);
				const url = window.URL.createObjectURL(blob);
				const a = document.createElement('a');
				a.href = url;

				const disposition = response.headers.get('content-disposition');
				let filename = 'album.zip';
				if (disposition) {
					const matches = /filename="?([^"]+)"?/.exec(disposition);
					if (matches) filename = matches[1];
				}

				a.download = filename;
				document.body.appendChild(a);
				a.click();
				window.URL.revokeObjectURL(url);
				document.body.removeChild(a);

				progressBar.classList.remove('progress-bar-animated');
				progressBar.classList.add('bg-success');
				statusText.textContent = 'Download complete!';
			} catch (error) {
				const wasAborted = error && (error.name === 'AbortError');

				progressBar.classList.remove('progress-bar-animated');
				progressBar.classList.add(wasAborted ? 'bg-danger' : 'bg-danger');
				statusText.textContent = wasAborted ? 'Download cancelled.' : 'Download failed. Please try again.';
			} finally {
				albumDownloadController = null;
				setDownloadUIState({downloading: false});

				// Hide progress after a moment
				setTimeout(() => {
					progressContainer.style.display = 'none';
				}, 1500);
			}
		});
	});
</script>
</body>
</html>