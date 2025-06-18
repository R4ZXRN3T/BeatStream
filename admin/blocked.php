<?php
session_start();
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>⛔Access Denied</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../favicon.ico" rel="icon">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container-fluid">
		<div class="collapse navbar-collapse myNavbar">
			<ul class="navbar-nav">
				<li class="nav-item"><a class="nav-link" href="view/songs">View</a></li>
				<li class="nav-item"><a class="nav-link" href="add/song">Add content</a></li>
			</ul>
			<div class="dropdown ms-auto">
				<button class="btn d-flex align-items-center dropdown-toggle p-0 bg-transparent border-0"
						type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
					<div class="text-end">
						<div class="fw-bold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
						<div class="small text-white-50"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
					</div>
					<img src="<?php echo $_SESSION['imagePath'] ? '../images/user/' . $_SESSION['imagePath'] : '../images/default.webp'; ?>"
						 alt="Profile" class="rounded-circle me-2"
						 style="width:40px; height:40px; object-fit:cover; margin-left: 15px; margin-right: 15px;">
				</button>
				<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
					<li><a class="dropdown-item" href="../account/profile.php">View Profile</a></li>
					<li>
						<hr class="dropdown-divider">
					</li>
					<li><a class="dropdown-item text-danger" href="../account/logout.php">Log Out</a></li>
				</ul>
			</div>
		</div>
	</div>
</nav>

<div class="container mt-4">
	<h1 class="mb-4">Access Denied</h1>
	<p class="lead">You do not have permission to access this page.</p>
	<p>Please contact the site administrator if you believe this is an error.</p>
	<a href="../home" class="btn btn-primary">Go to home</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>