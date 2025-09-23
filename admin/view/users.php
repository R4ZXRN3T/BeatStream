<?php
ob_start();
include( $GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php");
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
	<title>BeatStream - view users</title>
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
					<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/" class="nav-link mb-2 active">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">

			<!-- Admin Navigation Bar -->
			<nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
				<div class="container-fluid">
					<ul class="navbar-nav">
						<li class="nav-item"><a class="nav-link active" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/songs.php">View</a></li>
						<li class="nav-item"><a class="nav-link" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/song.php">Add content</a></li>
					</ul>
				</div>
			</nav>

			<div class="tab">
				<ul class="nav nav-tabs justify-content-center">
					<li class="nav-item"><a class="nav-link" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/songs.php">Songs</a></li>
					<li class="nav-item"><a class="nav-link" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/artists.php">Artists</a></li>
					<li class="nav-item"><a class="nav-link active" href="">Users</a></li>
					<li class="nav-item"><a class="nav-link" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/playlists.php">Playlists</a></li>
					<li class="nav-item"><a class="nav-link" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/albums.php">Albums</a></li>
				</ul>
			</div>

			<?php
			include( $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/UserController.php");
			$userList = UserController::getUserList();

			if (array_key_exists('removeButton', $_POST)) {
				UserController::deleteUser(intval($_POST['removeButton']));
				$_POST['removeButton'] = null;
				header("Refresh:0");
			}

			if (array_key_exists('addAdmin', $_POST)) {
				$stmt = DBConn::getConn()->prepare("UPDATE user SET isAdmin = TRUE WHERE userID = ?;");
				$stmt->bind_param("i", $_POST['addAdmin']);
				$stmt->execute();
				$stmt->close();
				$_POST['addAdmin'] = null;
				header("Refresh:0");
			}

			if (array_key_exists('removeAdmin', $_POST)) {
				$stmt = DBConn::getConn()->prepare("UPDATE user SET isAdmin = FALSE WHERE userID = ?;");
				$stmt->bind_param("i", $_POST['removeAdmin']);
				$stmt->execute();
				$stmt->close();
				$_POST['removeAdmin'] = null;
				header("Refresh:0");
			}
			?>


			<table style="width:100%; font-family:segoe UI,serif;">
				<colgroup>
					<col span="10">
				</colgroup>
				<tr>
					<th style="width:16.7%;">User ID</th>
					<th style="width:16.7%;">Username</th>
					<th style="width:16.7%;">E-Mail</th>
					<th style="width:16.7%;">User Password</th>
					<th style="width:16.7%;">Salt</th>
					<th style="width: 1%;">is admin</th>
					<th style="width: 1%;">is artist</th>
					<th style="width:16.7%;">Image Name</th>
					<th style="width:1%;"></th>
					<th style="width:1%;"></th>
				</tr>
				<?php
				for ($i = 0; $i < count($userList); $i++) {
					?>
					<tr>
						<td><?php echo $userList[$i]->getUserID() ?></td>
						<td><?php echo $userList[$i]->getUsername() ?></td>
						<td><?php echo $userList[$i]->getEmail() ?></td>
						<td><?php echo $userList[$i]->getUserPassword() ?></td>
						<td><?php echo $userList[$i]->getSalt() ?></td>
						<td><?php echo $userList[$i]->isAdmin() ? 'Yes' : 'No' ?></td>
						<td><?php echo $userList[$i]->isArtist() ? 'Yes' : 'No' ?></td>
						<td><?php echo $userList[$i]->getImageName() ?></td>
						<?php
						if (!$userList[$i]->isAdmin()) {
							?>
							<td>
								<form method="post" action="">
									<button name="addAdmin" id="addAdmin"
											value="<?php echo $userList[$i]->getUserID() ?>"
											class="btn btn-secondary" type="submit" title="Make admin"
											style="white-space: nowrap; width: auto">+ admin
									</button>
								</form>
							</td>
							<?php
						} else {
							?>
							<td>
							<form method="post" action="">
								<button name="removeAdmin" id="removeAdmin"
										value="<?php echo $userList[$i]->getUserID() ?>"
										class="btn btn-danger" type="submit" title="Remove Artist"
										style="white-space: nowrap; width: auto">- admin
								</button>
							</form>
							</td><?php
						}
						?>
						<td>
							<form method="post" action="">
								<button name="removeButton" id="remove" value='<?php echo $userList[$i]->getUserID() ?>'
										class="btn btn-danger" type="submit" title="Remove User"
										style="white-space: nowrap">🗑️
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
<?php ob_end_flush(); ?>
</body>

</html>