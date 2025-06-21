<?php
include("../../../dbConnection.php");
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
		header("Location: ../../blocked.php");
		exit();
	}
	$_SESSION['isAdmin'] = $isAdmin;
} else {
	header("Location: ../../../account/login.php");
	exit();
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - view albums</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../../../mainStyle.css" rel="stylesheet">
	<link href="../../../favicon.ico" rel="icon">
</head>

<body>

<script>
	if (window.history.replaceState) {
		window.history.replaceState(null, null, window.location.href);
	}
</script>

<?php include("../../../topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../../../" class="nav-link mb-2">Home</a>
				<a href="../../../search/" class="nav-link mb-2">Search</a>
				<a href="../../../discover/" class="nav-link mb-2">Discover</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/" class="nav-link mb-2 active">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="col-md ms-sm-auto px-0 py-0">

			<!-- Admin Navigation Bar -->
			<nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
				<div class="container-fluid">
					<ul class="navbar-nav">
						<li class="nav-item"><a class="nav-link active" href="../songs">View</a></li>
						<li class="nav-item"><a class="nav-link" href="../../add/song">Add content</a></li>
					</ul>
				</div>
			</nav>

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link" href="../songs">Songs</a></li>
					<li class="nav-item"><a class="nav-link" href="../artists">Artists</a></li>
					<li class="nav-item"><a class="nav-link" href="../users">Users</a></li>
					<li class="nav-item"><a class="nav-link" href="../playlists">Playlists</a></li>
					<li class="nav-item"><a class="nav-link active" href="">Albums</a></li>
				</ul>
			</div>

			<?php
			include("../../../DataController.php");
			$albumList = DataController::getAlbumList();

			if (array_key_exists('removeButton', $_POST)) {
				DataController::deleteAlbum($_POST['removeButton']);
				header("Refresh:0");
			}
			?>


			<table style="width:100%; font-family:segoe UI,serif;">
				<colgroup>
					<col span="9" style="background-color:lightgray">
				</colgroup>
				<tr>
					<th style="width:14.3%;">Album ID</th>
					<th style="width:14.3%;">Name</th>
					<th style="width:14.3%;">Artists</th>
					<th style="width:14.3%;">Image Path</th>
					<th style="width:14.3%;">Album Length</th>
					<th style="width:14.3%;">Album Duration</th>
					<th style="width:1%;"></th>
				</tr>
				<?php
				for ($i = 0; $i < count($albumList); $i++) {
					?>
					<tr>
						<td><?php echo $albumList[$i]->getAlbumID() ?></td>
						<td><?php echo $albumList[$i]->getName() ?></td>
						<td><?php echo $albumList[$i]->getArtists() ?></td>
						<td><?php echo $albumList[$i]->getImagePath() ?></td>
						<td><?php echo $albumList[$i]->getLength() ?></td>
						<td><?php echo $albumList[$i]->getDuration()->format('i:s') ?></td>
						<td>
							<form method="post" action="">
								<button name="removeButton" id="remove"
										value="<?php echo $albumList[$i]->getAlbumID() ?>"
										class="btn btn-danger" type="submit" title="Remove Album">🗑️
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

</html>