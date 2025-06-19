<?php
session_start();
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
$recommendedSongs = [];
$IDsToRecommend = [];

for ($i = 0; $i < count($songList); $i++) {
	$randomIndex = rand(0, count($songList) - 1);
	$songID = $songList[$randomIndex]->getSongID();
	if (!in_array($songID, $IDsToRecommend)) {
		$IDsToRecommend[] = $songID;
		$recommendedSongs[] = $songList[$randomIndex];
	} else {
		$i--;
	}
}

$timeOfDay = "Day";
if (isset($_SESSION['timeOfDay'])) {
	$timeOfDay = $_SESSION['timeOfDay'];
} else {
	$currentHour = date('H');
	if ($currentHour < 12) {
		$timeOfDay = "Morning";
	} elseif ($currentHour < 18) {
		$timeOfDay = "Afternoon";
	} else {
		$timeOfDay = "Evening";
	}
}

include("../topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2 active">Home</a>
				<a href="../search.php" class="nav-link mb-2">Search</a>
				<a href="../discover.php" class="nav-link mb-2">Discover</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="col-md d-flex justify-content-center align-items-center" style="min-height: 80vh;">
			<div class="w-100" style="max-width: 1100px;">
				<h1 class="text-center"><?php echo "Good " . $timeOfDay . "!"; ?></h1>
				<h2 class="mt-4 mb-3 text-center">Recommended for you</h2>
				<div class="row justify-content-center g-3">
					<?php foreach ($recommendedSongs as $song): ?>
						<div class="col-12 col-md-6 d-flex justify-content-center">
							<div class="card shadow-sm" style="width: 100%; max-width: 500px; padding: 0.5rem 0.75rem;">
								<div class="card-body d-flex align-items-center p-2">
									<?php if (!empty($song->getImagePath())): ?>
										<img src="<?php echo "/BeatStream/images/song/" . htmlspecialchars($song->getImagePath()); ?>"
											 class="me-3 rounded"
											 alt="<?php echo htmlspecialchars($song->getImagePath()); ?>"
											 style="width: 40px; height: 40px; object-fit: cover;">
									<?php else: ?>
										<img src="../images/defaultSong.webp" class="me-3 rounded" alt="Default Album Cover"
											 style="width: 40px; height: 40px; object-fit: cover;">
									<?php endif; ?>
									<div>
										<h5 class="card-title song-title mb-1" style="font-size: 1rem;"><?php echo htmlspecialchars($song->getTitle()); ?></h5>
										<p class="card-text song-artist mb-0" style="font-size: 0.9rem;"><?php echo htmlspecialchars($song->getArtists()); ?></p>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
