<?php
session_start();
include("../dbConnection.php");
include("../DataController.php");

// Redirect if not logged in
if (!isset($_SESSION['account_loggedin']) || !$_SESSION['account_loggedin']) {
	header("Location: login.php");
	exit();
}

$userID = $_SESSION['userID'];
// Fetch user data
$userList = DataController::getUserList();
$currentUser = null;
foreach ($userList as $user) {
	if ($user->getUserID() == $userID) {
		$currentUser = $user;
		break;
	}
}
if (!$currentUser) {
	echo "<div class='alert alert-danger'>User not found.</div>";
	exit();
}

$success = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username']);
	$email = trim($_POST['email']);
	$password = $_POST['newPassword'];
	$imageName = $currentUser->getimageName();

	// Handle image upload
	if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
		unlink("../images/user/" . $currentUser->getimageName());
		$uploadDir = "../images/user/";
		$ext = pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION);
		$newFileName = uniqid() . "." . $ext;
		$destPath = $uploadDir . $newFileName;
		if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $destPath)) {
			$imageName = $newFileName;
		} else {
			$error = "Image upload failed.";
		}
	}

	// Update user in DB
	if (!$error) {

		$wrongPassword = false;
		$conn = DBConn::getConn();
		$result = $conn->query("SELECT userPassword FROM user WHERE userID = '$userID'");
		if ($result && $result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$oldPassword = $_POST['oldPassword'];
			$hashedOldPassword = hash("sha256", $oldPassword . $currentUser->getSalt());

			// Check if old password matches
			if ($hashedOldPassword !== $row['userPassword']) {
				$wrongPassword = true;
				$error = "Old password is incorrect.";
			}
		} else {
			$error = "User not found.";
		}

		if (!empty($password) && !$wrongPassword) {
			// Update password with new salt
			$salt = DataController::generateRandomString(16);
			$hashed = hash("sha256", $password . $salt);
			$stmt = $conn->prepare("UPDATE user SET username=?, email=?, userPassword=?, salt=?, imageName=? WHERE userID=?");
			$stmt->bind_param("sssssi", $username, $email, $hashed, $salt, $imageName, $userID);
		} else {
			$stmt = $conn->prepare("UPDATE user SET username=?, email=?, imageName=? WHERE userID=?");
			$stmt->bind_param("sssi", $username, $email, $imageName, $userID);
		}
		if ($stmt->execute()) {
			$success = "Profile updated successfully.";
			// Update session username if changed
			$_SESSION['username'] = $username;
			$_SESSION['email'] = $email;
			if (!empty($imageName)) {
				$_SESSION['imageName'] = $imageName;
			}
		} else {
			$error = "Failed to update profile.";
		}
		$stmt->close();
	}
	// Refresh user data
	$userList = DataController::getUserList();
	foreach ($userList as $user) {
		if ($user->getUserID() == $userID) {
			$currentUser = $user;
			break;
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Edit Profile</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../mainStyle.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php"); ?>
<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="../" class="nav-link mb-2">Home</a>
				<a href="../search/" class="nav-link mb-2">Search</a>
				<a href="../discover/" class="nav-link mb-2">Discover</a>
				<a href="/BeatStream/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin" class="nav-link mb-2">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="main col-md" style="min-height: 80vh; padding: 2rem;">
			<div class="container mt-5" style="max-width: 600px;">
				<h1>Edit Profile</h1>
				<?php if ($success): ?>
					<div class="alert alert-success"><?= $success ?></div>
				<?php endif; ?>
				<?php if ($error): ?>
					<div class="alert alert-danger"><?= $error ?></div>
				<?php endif; ?>
				<form method="post" enctype="multipart/form-data">
					<div class="mb-3">
						<label for="username" class="form-label">Username</label>
						<input type="text" class="form-control" id="username" name="username"
							   value="<?= htmlspecialchars($currentUser->getUsername()) ?>" required>
					</div>
					<div class="mb-3">
						<label for="email" class="form-label">Email</label>
						<input type="email" class="form-control" id="email" name="email"
							   value="<?= htmlspecialchars($currentUser->getEmail()) ?>" required>
					</div>
					<div class="mb-3">
						<label for="oldPassword" class="form-label">old Password (leave blank to keep current)</label>
						<input type="password" class="form-control" id="oldPassword" name="oldPassword"
							   autocomplete="new-password">
					</div>
					<div class="mb-3">
						<label for="newPassword" class="form-label">New Password (leave blank to keep current)</label>
						<input type="password" class="form-control" id="newPassword" name="newPassword"
							   autocomplete="new-password">
					</div>
					<div class="mb-3">
						<label for="imageFile" class="form-label">Profile Image</label>
						<input type="file" class="form-control" id="imageFile" name="imageFile" accept="image/*">

					</div>
					<button type="submit" class="btn btn-primary">Save Changes</button>
				</form>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>