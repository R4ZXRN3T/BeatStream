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

require_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/controller/PlaylistController.php";
$playlistList = PlaylistController::getPlaylistList($sortBy);
include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php");
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
					<a href="/BeatStream/admin/" class="nav-link mb-2">Admin</a>
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
				<?php
				$options = [
						'containerClass' => 'col-md-4 mb',
						'showCreator' => true,
						'emptyMessage' => 'No playlists available at the moment.'
				];
				include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/playlist-list.php");
				?>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>