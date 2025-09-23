<?php
session_start();
require_once( $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/UserController.php");
require_once( $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/ArtistController.php");
require_once( $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/PlaylistController.php");

// Fetch user info
$userID = $_SESSION['userID'];
$user = UserController::getUserById($userID);

if (!$user) {
	header("Location: {$GLOBALS['PROJECT_ROOT']}/auth/login.php");
	exit();
}

$artistID = -1;
$artistList = ArtistController::getArtistList();

foreach ($artistList as $a) {
	if ($a->getUserID() == $userID) {
		$artistID = $a->getArtistID();
		break;
	}
}

// Fetch playlists created by user
$allPlaylists = PlaylistController::getPlaylistList();
$userPlaylists = array_filter($allPlaylists, fn($p) => $p->getCreatorID() == $userID);

// Fetch favorite songs (example: store favorite song IDs in session or DB)
$favSongIDs = $_SESSION['favoriteSongs'] ?? [];
// Note: You'll need to implement SongController::getSongList() if you want to display favorite songs
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Profile - BeatStream</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/mainStyle.css" rel="stylesheet">
</head>
<body>
<?php include( $GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>
<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/" class="nav-link mb-2">Home</a>
				<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/search/" class="nav-link mb-2">Search</a>
				<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/discover/" class="nav-link mb-2">Discover</a>
				<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<main class="main col-md" style="min-height: 80vh; margin-left: 150px; padding: 2rem;">
			<div class="container mt-5">
				<div class="row">
					<!-- Profile Info -->
					<div class="col-md-4">
						<div class="card">
							<img src="<?= htmlspecialchars("/BeatStream/images/user/large/" . ($user->getImageName() ?: 'defaultUser.webp')) ?>"
								 class="card-img-top" alt="Profile Image"
								 style="object-fit: cover; height: 300px; width: 100%;">
							<div class="card-body">
								<h4 class="card-title"><?= htmlspecialchars($user->getUsername()) ?></h4>
								<p class="card-text"><?= htmlspecialchars($user->getEmail()) ?></p>
								<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/account/edit.php" class="btn btn-primary">Edit Profile</a>
							</div>
						</div>
						<?php if ($artistID != -1): ?>
							<div class="card mt-3">
								<div class="card-body">
									<h5 class="card-title">Your Artist profile:</h5>
									<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/view/artist.php?id=<?php echo $artistID ?>" class="btn btn-primary">View Artist Profile</a>
								</div>
							</div>
						<?php else: ?>
							<div class="card mt-3">
								<div class="card-body">
									<h5 class="card-title">Become an Artist</h5>
									<p class="card-text">Create your artist profile to share your music with the world.</p>
									<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/create/artist.php" class="btn btn-primary">Create Artist Profile</a>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<!-- Playlists and Favorites -->
					<div class="col-md-8">
						<div class="d-flex justify-content-between align-items-center mb-3">
							<h3>Your Playlists</h3>
							<?php if (!empty($userPlaylists)): ?>
								<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/create/playlist.php" class="btn btn-primary">Create New Playlist</a>
							<?php endif; ?>
						</div>
						<?php
						// Set up options for playlist list component
						$playlistList = $userPlaylists;
						$options = [
								'containerClass' => 'col-md-6 col-lg-4',
								'showCreator' => false, // Don't show creator since it's the user's own playlists
								'emptyMessage' => 'No playlists created yet.'
						];
						include( $GLOBALS['PROJECT_ROOT_DIR'] . "/components/playlist-list.php");
						?>
						<?php if (empty($userPlaylists)): ?>
							<div class="text-center mt-3">
								<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/create/playlist.php" class="btn btn-primary">Create Your First Playlist</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>