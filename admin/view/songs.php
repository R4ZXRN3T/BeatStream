<?php
ob_start();
include($GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php");
session_start();
$isAdmin = false;
if (isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true) {
	$stmt = DBConn::getConn()->prepare("SELECT isAdmin FROM user WHERE userID = ?;");
	$stmt->bind_param("i", $_SESSION['userID']);
	$stmt->execute();
	$isAdmin = $stmt->get_result()->fetch_assoc()['isAdmin'] ?? false;
	$stmt->close();
	if (!$isAdmin) {
		$_SESSION['isAdmin'] = $isAdmin;
		header("Location: {$GLOBALS['PROJECT_ROOT']}/admin/blocked.php");
		exit();
	}
	$_SESSION['isAdmin'] = $isAdmin;
} else {
	header("Location: {$GLOBALS['PROJECT_ROOT']}/account/login.php");
	exit();
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - view songs</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/mainStyle.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/favicon.ico" rel="icon">
</head>

<body>

<script>
	if (window.history.replaceState) {
		window.history.replaceState(null, null, window.location.href);
	}
</script>

<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<?php
		$activePage = 'admin';
		include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/sidebar.php");
		?>
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">

			<!-- Admin Navigation Bar -->
			<nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
				<div class="container-fluid">
					<ul class="navbar-nav">
						<li class="nav-item"><a class="nav-link active"
												href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/songs.php">View</a>
						</li>
						<li class="nav-item"><a class="nav-link"
												href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/song.php">Add
								content</a></li>
					</ul>
				</div>
			</nav>

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link active" href="">Songs</a></li>
					<li class="nav-item"><a class="nav-link"
											href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/artists.php">Artists</a>
					</li>
					<li class="nav-item"><a class="nav-link"
											href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/users.php">Users</a></li>
					<li class="nav-item"><a class="nav-link"
											href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/playlists.php">Playlists</a>
					</li>
					<li class="nav-item"><a class="nav-link"
											href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/albums.php">Albums</a></li>
				</ul>
			</div>

			<?php
			include($GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php");
			$songList = SongController::getSongList();
			?>

			<?php
			if (array_key_exists('removeButton', $_POST)) {
				SongController::deleteSong($_POST['removeButton']);
				header("Refresh:0");
			}
			?>


			<table style="width:100%; font-family:segoe UI,serif;">
				<colgroup>
					<col span="10">
				</colgroup>
				<tr>
					<th style="width:10%;">Song ID</th>
					<th style="width:10%;">Title</th>
					<th style="width:10%;">Artists</th>
					<th style="width:10%;">Genre</th>
					<th style="width:10%;">Release Date</th>
					<th style="width:10%;">Song Length</th>
					<th style="width:10%;">File Name</th>
					<th style="width:10%;">Image Name</th>
					<th style="width:1%;"></th>
				</tr>
				<?php
				for ($i = 0; $i < count($songList); $i++) {
					?>
					<tr>
						<td><?php echo $songList[$i]->getSongID() ?></td>
						<td><?php echo $songList[$i]->getTitle() ?></td>
						<td><?php echo implode(", ", $songList[$i]->getArtists()) ?></td>
						<td><?php echo $songList[$i]->getGenre() ?></td>
						<td><?php echo $songList[$i]->getReleaseDate()->format('d.m.Y') ?></td>
						<td><?php echo $songList[$i]->getFormattedDuration() ?></td>
						<td><?php echo $songList[$i]->getFlacFileName() ?></td>
						<td><?php echo $songList[$i]->getImageName() ?></td>
						<td>
							<form method="post" action="">
								<button name="removeButton" id="remove" value="<?php echo $songList[$i]->getSongID() ?>"
										class="btn btn-danger" type="submit" title="Remove Song">🗑️
								</button>
							</form>
						</td>
					</tr>
					<?php
				}
				?>
			</table>

			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
		</main>
	</div>
</div>
</body>
<?php ob_end_flush(); ?>

</html>