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
	<link href="../viewStyle.css" rel="stylesheet">
	<link href="../../../favicon.ico" rel="icon">
</head>

<body>

<script>
	if ( window.history.replaceState ) {
		window.history.replaceState( null, null, window.location.href );
	}
</script>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container-fluid">
		<div class="collapse navbar-collapse myNavbar">
			<ul class="navbar-nav">
				<li class="nav-item"><a class="nav-link active" href="../songs">View</a></li>
				<li class="nav-item"><a class="nav-link" href="../../add/song">Add content</a></li>
			</ul>
			<?php if (isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true): ?>
				<div class="dropdown ms-auto">
					<button class="btn d-flex align-items-center dropdown-toggle p-0 bg-transparent border-0"
							type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
						<div class="text-end">
							<div class="fw-bold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
							<div class="small text-white-50"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
						</div>
						<img src="<?php echo $_SESSION['imagePath'] ? '../../../images/user/' . $_SESSION['imagePath'] : '../../../images/default.webp'; ?>"
							 alt="Profile" class="rounded-circle me-2"
							 style="width:40px; height:40px; object-fit:cover; margin-left: 15px; margin-right: 15px;">
					</button>
					<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
						<li><a class="dropdown-item" href="../../../account/profile.php">View Profile</a></li>
						<li>
							<hr class="dropdown-divider">
						</li>
						<li><a class="dropdown-item text-danger" href="../../../account/logout.php">Log Out</a></li>
					</ul>
				</div>
			<?php else: ?>
				<div class="ms-auto d-flex">
					<a href="../../../account/login.php" class="btn btn-outline-light me-2">Login</a>
					<a href="../../../account/signup.php" class="btn btn-primary">Sign Up</a>
				</div>
			<?php endif; ?>
		</div>
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
$albumList = dataController::getAlbumList();

if (array_key_exists('removeButton', $_POST)) {
	dataController::deleteAlbum($_POST['removeButton']);
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
			<td><?php echo $albumList[$i]->getAlbumLength() ?></td>
			<td><?php echo $albumList[$i]->getAlbumDuration()->format('i:s') ?></td>
			<td>
				<form method="post" action="">
					<button name="removeButton" id="remove" value="<?php echo $albumList[$i]->getAlbumID() ?>"
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
</body>

</html>