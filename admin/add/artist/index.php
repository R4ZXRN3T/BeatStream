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
	<title>BeatStream - add an artist</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../addStyle.css" rel="stylesheet">
	<link href="../../../favicon.ico" rel="icon">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container-fluid">
		<div class="collapse navbar-collapse myNavbar">
			<ul class="navbar-nav">
				<li class="nav-item"><a class="nav-link" href="../../view/songs">View</a></li>
				<li class="nav-item"><a class="nav-link active" href="../../add/song">Add content</a></li>
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
		<li class="nav-item"><a class="nav-link" href="../song">Song</a></li>
		<li class="nav-item"><a class="nav-link active" href="">Artist</a></li>
		<li class="nav-item"><a class="nav-link" href="../user">User</a></li>
		<li class="nav-item"><a class="nav-link" href="../playlist">Playlist</a></li>
		<li class="nav-item"><a class="nav-link" href="../album">Album</a></li>
	</ul>
</div>

<?php
include("../../../DataController.php");
$userList = dataController::getUserList();

$isValid = true;

if (!(
	!empty($_POST["nameInput"]) && !empty($_POST["imagePathInput"]) && !empty($_POST["followerInput"]) && !empty($_POST["activeSinceInput"]) && !empty($_POST["userIDInput"])
)) {
	$isValid = false;
}

if ($isValid) {
	dataController::insertArtist(new Artist(
		12345,
		$_POST["nameInput"],
		$_POST["imagePathInput"],
		$_POST["followerInput"],
		$_POST["activeSinceInput"],
		$_POST["userIDInput"]
	));
	?>
	<h1>Success!</h1>
	<?php
}
?>

<div class="container mt-5">
	<h1>Künstler Einfügen</h1>

	<form action="index.php" method="post" id="addArtistForm">
		<div class="form-group">
			<label for="name">Name:</label>
			<input type="text" id="name" name="nameInput" class="form-control" placeholder="Enter artist name" required>
		</div>
		<div class="form-group">
			<label for="imagePath">Image Path:</label>
			<input type="text" id="imagePath" name="imagePathInput" class="form-control" placeholder="Enter image path"
				   required>
		</div>
		<div class="form-group">
			<label for="follower">Follower:</label>
			<input type="number" id="follower" name="followerInput" class="form-control"
				   placeholder="Enter follower count" required>
		</div>
		<div class="form-group">
			<label for="activeSince">active since:</label>
			<input type="date" id="activeSince" name="activeSinceInput" class="form-control"
				   placeholder="Enter creation date" required>
		</div>
		<div class="form-group">
			<label for="user">User:</label>
			<label for="userID"></label><select name="userIDInput" id="userID" style="width: 175px;"
												class="form-control" required>
				<option value=none>--Please Select--</option>
				<?php
				for ($i = 0; $i < count($userList); $i++) {
					?>
					<option value=<?php echo $userList[$i]->getUserID() ?>><?php echo $userList[$i]->getUsername() ?></option>
					<?php
				}
				?>
			</select>
		</div>
		<input type="submit" class="btn btn-primary mt-3" value="Submit">
	</form>
</div>

<!-- Bootstrap JS (optional for some interactive components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>