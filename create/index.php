<?php
session_start();
if (!(isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true)) {
	header("Location: ../account/login.php");
	exit();
}
?>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - Create</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
	<link href="../mainStyle.css" rel="stylesheet">
</head>

<!DOCTYPE html>
<html lang="en">

<body>

<?php
include("../DataController.php");

$stmt = DBConn::getConn()->prepare("SELECT isArtist FROM user WHERE userID = ?;");
$stmt->bind_param("i", $_SESSION['userID']);
$stmt->execute();
$result = $stmt->get_result();
$isArtist = $result->fetch_assoc()['isArtist'] ?? false;

include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php");
?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2">Home</a>
				<a href="../search/" class="nav-link mb-2">Search</a>
				<a href="../discover/" class="nav-link mb-2">Discover</a>
				<a href="/BeatStream/create/" class="nav-link mb-2 active">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="main col-md" style="min-height: 80vh; padding: 2rem;">

			<div class="container mt-5">
				<h2>Create</h2>
				<?php if (!$isArtist): ?>
					<div class="alert alert-info">
						<p>You are not an artist yet. To create albums and songs, please become an artist.</p>
						<a href="artist.php" class="btn btn-primary">Become an Artist</a>
					</div>
					<div class="card mt-4">
						<div class="card-body">
							<h5 class="card-title">Create Playlist</h5>
							<a href="playlist.php" class="btn btn-success">Create Playlist</a>
						</div>
					</div>
				<?php else: ?>
					<div class="row">
						<div class="col-md-4">
							<div class="card mb-3">
								<div class="card-body">
									<h5 class="card-title">Create Album</h5>
									<a href="album.php" class="btn btn-primary">Create Album</a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mb-3">
								<div class="card-body">
									<h5 class="card-title">Create Song</h5>
									<a href="song.php" class="btn btn-primary">Create Song</a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mb-3">
								<div class="card-body">
									<h5 class="card-title">Create Playlist</h5>
									<a href="playlist.php" class="btn btn-success">Create Playlist</a>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>