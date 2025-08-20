<?php
session_start();
include_once("../DataController.php");

// Get artist ID from URL parameter
$artistID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to home
if ($artistID <= 0) {
	header("Location: ../album.php");
	exit;
}

// Get artist details
$artistList = DataController::getArtistList();
$artist = null;
foreach ($artistList as $a) {
	if ($a->getArtistID() == $artistID) {
		$artist = $a;
		break;
	}
}

// If artist not found, redirect
if (!$artist) {
	header("Location: ../album.php");
	exit;
}

// Get all songs by this artist
$allSongs = DataController::getSongList();
$artistSongs = [];
foreach ($allSongs as $song) {
	if (in_array($artist->getName(), $song->getArtists())) {
		$artistSongs[] = $song;
	}
}

// Get all albums by this artist
$allAlbums = DataController::getAlbumList();
$artistAlbums = [];
foreach ($allAlbums as $album) {
	if (in_array($artist->getName(), $album->getArtists())) {
		$artistAlbums[] = $album;
	}
}

$songQueueData = array_map(function ($song) {
	return [
		'songID' => $song->getSongID(),
		'title' => $song->getTitle(),
		'artists' => implode(", ", $song->getArtists()),
		'fileName' => $song->getFileName(),
		'imageName' => $song->getImageName()
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

<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php"); ?>
<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
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
			<div class="container-fluid py-3">
				<!-- Artist Header -->
				<div class="artist-header">
					<div class="container">
						<div class="row align-items-center">
							<div class="col-md-4 text-center">
								<img src="<?php echo $artist->getimageName() ? '../images/artist/' . $artist->getimageName() : '../images/defaultArtist.webp'; ?>"
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
					include('../components/song-list.php');
					?>
				</div>

				<!-- Albums Section -->
				<div class="container mb-5">
					<h2 class="mb-4">Albums</h2>
					<?php if (empty($artistAlbums)): ?>
						<div class="alert alert-info">No albums available for this artist.</div>
					<?php else: ?>
						<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-3 g-3">
							<?php foreach ($artistAlbums as $album): ?>
								<div class="col-md-3 mb-3">
									<a class="on-card-link" href="album.php?id=<?php echo $album->getAlbumID(); ?>">
										<div class="card shadow-sm border-0"
											 style="border-radius: 10px; width: 100%; height: auto;">
											<div class="d-flex flex-column">
												<img src="<?php echo $album->getImageName() ? '../images/album/' . $album->getImageName() : '../images/defaultAlbum.webp'; ?>"
													 class="mb-2 rounded"
													 alt="<?php echo $album->getName(); ?>"
													 style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px 10px 0 0;">
												<div class="card-body p-3">
													<h5 class="card-title mb-1"
														style="font-size: 1.1rem; font-weight: bold;"><?php echo $album->getName(); ?></h5>
													<p class="card-text mb-0"
													   style="font-size: 0.9rem; color: #6c757d;"><?php echo implode(", ", $album->getArtists()); ?></p>
													<p class="card-text mb-0"
													   style="font-size: 0.8rem; text-align: left; color: #6c757d;">
														<?php echo $album->getLength(); ?> songs •
														<?php echo $album->getDuration()->format("H") > 0 ? $album->getDuration()->format("H\h i\m s\s") : $album->getDuration()->format("i\m s\s"); ?>
													</p>
												</div>
											</div>
										</div>
									</a>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</main>
	</div>
</div>

<!-- Include the music player -->
<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/player.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>