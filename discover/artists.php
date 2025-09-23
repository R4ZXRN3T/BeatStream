<?php
session_start();
?>

<!Doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - Discover Songs</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
	<link href="../mainStyle.css" rel="stylesheet">
</head>

<body>
<?php
$sortBy = $_POST['sortInput'] ?? 'artist.name ASC';

require_once  $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/ArtistController.php";
$artistList = ArtistController::getArtistList($sortBy);
include  $GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php";
?>

<script>
	if (window.history.replaceState) {
		window.history.replaceState(null, null, window.location.href);
	}
</script>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2">Home</a>
				<a href="../search/" class="nav-link mb-2">Search</a>
				<a href="../discover/" class="nav-link mb-2 active">Discover</a>
				<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>

		<div class="tab">
			<ul class="nav nav-tabs justify-content-center">
				<li class="nav-item"><a class="nav-link" href="songs.php">Songs</a></li>
				<li class="nav-item"><a class="nav-link active" href="artists.php">Artists</a></li>
				<li class="nav-item"><a class="nav-link" href="playlists.php">Playlists</a></li>
				<li class="nav-item"><a class="nav-link" href="albums.php">Albums</a></li>
			</ul>
		</div>

		<main class="main col-md ms-sm-auto px-0 py-0 justify-content-center">
			<!-- Discover Songs Header -->
			<div class="container mt-4">
				<h1 class="text-center" style=" font-weight: bold">Discover Artists</h1>
				<p class="text-center">Explore all artists on our platform</p>
			</div>

			<div class="container mt-4 justify-content-center" style="width: 600px">
				<form class="d-flex" action="artists.php" method="post">
					<label for="sortInput" class="form-label me-2" style="width: 70px; align-content: center">Sort
						by:</label>
					<select class="form-select" id="sortInput" name="sortInput" style="align-content: center"
							onchange='this.form.submit();'>
						<option value='artist.name ASC'>Name ascending</option>
						<option value='artist.name DESC'>Name descending</option>
					</select>
				</form>
			</div>


			<script>document.getElementById("sortInput").value = "<?php echo $sortBy ?>";</script>

			<!-- Artist List -->
			<div class="container mt-4">
				<div class="row" style="justify-content: center; width: 100%; margin: auto;">
					<?php if (!empty($artistList)): ?>
						<?php foreach ($artistList as $artist): ?>
							<div class="col-md-4 mb-4">
								<a href="../view/artist.php?id=<?php echo $artist->getArtistID(); ?>"
								   class="text-decoration-none">
									<div class="card shadow-sm border-0" style="border-radius: 10px;">
										<div class="card-body d-flex align-items-center p-3">
											<?php if (!empty($artist->getImageName())): ?>
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
					<?php else: ?>
						<p class="text-center">No artists available at the moment.</p>
					<?php endif; ?>
				</div>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/player.php"); ?>
</body>
</html>
