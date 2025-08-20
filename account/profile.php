<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/DataController.php");

// Fetch user info
$userID = $_SESSION['userID'];
$userList = DataController::getUserList();
$artistList = DataController::getArtistList();
$user = null;
foreach ($userList as $u) {
	if ($u->getUserID() == $userID) {
		$user = $u;
		break;
	}
}

$artistID = -1;

foreach ($artistList as $a) {
	if ($a->getUserID() == $userID) {
		$artistID = $a->getArtistID();
		break;
	}
}

// Fetch playlists created by user
$allPlaylists = DataController::getPlaylistList();
$userPlaylists = array_filter($allPlaylists, fn($p) => $p->getCreatorID() == $userID);

// Fetch favorite songs (example: store favorite song IDs in session or DB)
$favSongIDs = $_SESSION['favoriteSongs'] ?? [];
$allSongs = DataController::getSongList();
$favSongs = array_filter($allSongs, fn($s) => in_array($s->getSongID(), $favSongIDs));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Profile - BeatStream</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="/BeatStream/mainStyle.css" rel="stylesheet">
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php"); ?>
<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="/BeatStream/" class="nav-link mb-2">Home</a>
				<a href="/BeatStream/search/" class="nav-link mb-2">Search</a>
				<a href="/BeatStream/discover/" class="nav-link mb-2">Discover</a>
				<a href="/BeatStream/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin/" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<main class="main col-md" style="min-height: 80vh; margin-left: 150px; padding: 2rem;">
			<div class="container mt-5">
				<div class="row">
					<!-- Profile Info -->
					<div class="col-md-4">
						<div class="card">
							<img src="<?= htmlspecialchars("/BeatStream/images/user/" . $user->getimageName() ?: '/BeatStream/images/defaultUser.webp') ?>"
								 class="card-img-top" alt="Profile Image"
								 style="object-fit: cover; height: 100%; width: 100%;">
							<div class="card-body">
								<h4 class="card-title"><?= htmlspecialchars($user->getUsername()) ?></h4>
								<p class="card-text"><?= htmlspecialchars($user->getEmail()) ?></p>
								<a href="/BeatStream/account/edit.php" class="btn btn-primary">Edit Profile</a>
							</div>
						</div>
						<?php if ($artistID != -1): ?>
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Your Artist profile:</h5>
									<a href="/BeatStream/view/artist.php?id=<?php echo $artistID ?>" class="btn btn-primary">View Artist Profile</a>
								</div>
							</div>
						<?php else: ?>
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Become an Artist</h5>
									<p class="card-text">Create your artist profile to share your music with the world.</p>
									<a href="/BeatStream/create/artist.php" class="btn btn-primary">Create Artist Profile</a>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<!-- Playlists and Favorites -->
					<div class="col-md-8">
						<h3>Your Playlists:</h3>
						<div class="row">
							<?php foreach ($userPlaylists as $playlist): ?>
								<div class="col-md-4 mb-4" style="height: 100%;">
									<div class="card h-auto">
										<img src="<?= htmlspecialchars($playlist->getimageName() ? "/BeatStream/images/playlist/" . $playlist->getimageName() : "/BeatStream/images/defaultPlaylist.webp") ?>"
											 class="card-img-top" alt="Playlist Image"
											 style="object-fit: cover; height: 100%;">
										<div class="card-body d-flex flex-column">
											<h5 class="card-title"><?= htmlspecialchars($playlist->getName()) ?></h5>
											<p class="card-text"><?= $playlist->getLength() ?> songs
												- <?= $playlist->getDuration()->format("i:s") ?></p>
											<a href="/BeatStream/view/playlist.php?id=<?= $playlist->getPlaylistID() ?>"
											   class="btn btn-outline-secondary btn-sm mt-auto">View</a>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
							<?php if (empty($userPlaylists)): ?>
								<p class="text">No playlists created yet.</p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>