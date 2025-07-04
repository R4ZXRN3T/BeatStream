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
$sortBy = $_POST['sortInput'] ?? 'playlist.name ASC';

include("../DataController.php");
$playlistList = DataController::getPlaylistList($sortBy);
include("../topBar.php");
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
				<a href="/BeatStream/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>

		<div class="tab">
			<ul class="nav nav-tabs justify-content-center">
				<li class="nav-item"><a class="nav-link" href="songs.php">Songs</a></li>
				<li class="nav-item"><a class="nav-link" href="artists.php">Artists</a></li>
				<li class="nav-item"><a class="nav-link active" href="playlists.php">Playlists</a></li>
				<li class="nav-item"><a class="nav-link" href="albums.php">Albums</a></li>
			</ul>
		</div>

		<main class="main col-md ms-sm-auto px-0 py-0 justify-content-center">
			<!-- Discover Songs Header -->
			<div class="container mt-4">
				<h1 class="text-center" style=" font-weight: bold">Discover Playlists</h1>
				<p class="text-center">Explore all playlists on our platform</p>
			</div>

			<div class="container mt-4 justify-content-center" style="width: 600px">
				<form class="d-flex" action="playlists.php" method="post">
					<label for="sortInput" class="form-label me-2" style="width: 70px; align-content: center">Sort
						by:</label>
					<select class="form-select" id="sortInput" name="sortInput" style="align-content: center"
							onchange='this.form.submit();'>
						<option value='playlist.name ASC'>Name ascending</option>
						<option value='playlist.name DESC'>Name descending</option>
					</select>
				</form>
			</div>


			<script>document.getElementById("sortInput").value = "<?php echo $sortBy ?>";</script>

			<!-- Song List -->
			<div class="container mt-4">
				<div class="row" style="justify-content: center; width: 100%; margin: auto;">
					<?php if (!empty($playlistList)): ?>
						<?php foreach ($playlistList as $playlist): ?>
							<div class="col-md-4 mb-4">
								<div class="card shadow-sm border-0" style="border-radius: 10px;">
									<div class="card-body d-flex align-items-center p-3">
										<?php if (!empty($playlist->getimageName())): ?>
											<img src="<?php echo "/BeatStream/images/playlist/" . htmlspecialchars($playlist->getimageName()); ?>"
												 class="me-3 rounded"
												 alt="<?php echo htmlspecialchars($playlist->getimageName()); ?>"
												 style="width: 60px; height: 60px; object-fit: cover;">
										<?php else: ?>
											<img src="../images/defaultSong.webp" class="me-3"
												 alt="Default Album Cover"
												 style="width: 50px; height: 50px; object-fit: cover;">
										<?php endif; ?>
										<div>
											<h5 class="card-title mb-1"
												style="font-size: 1.1rem; font-weight: bold;"><?php echo htmlspecialchars($playlist->getName()); ?></h5>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else: ?>
						<p class="text-center">No playlists available at the moment.</p>
					<?php endif; ?>
				</div>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
