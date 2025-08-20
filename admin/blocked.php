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
	<link href="/BeatStream/mainStyle.css" rel="stylesheet">
	<link href="/BeatStream/favicon.ico" rel="icon">
</head>

<body>

<?php include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<nav class="col-md-2 d-none d-md-block bg-light sidebar py-4 fixed-top">
			<div class="nav flex-column py-4">
				<a href="/BeatStream/" class="nav-link mb-2">Home</a>
				<a href="/BeatStream/search/" class="nav-link mb-2">Search</a>
				<a href="/BeatStream/discover/" class="nav-link mb-2">Discover</a>
				<a href="/BeatStream/create/" class="nav-link mb-2">Create</a>
				<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
					<a href="/BeatStream/admin/" class="nav-link mb-2 active">Admin</a>
				<?php endif; ?>
			</div>
		</nav>
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">
			<div class="container mt-4">
				<h1 class="mb-4">Access Denied</h1>
				<p class="lead">You do not have permission to access this page.</p>
				<p>Please contact the site administrator if you believe this is an error.</p>
				<a href="/BeatStream/home" class="btn btn-primary">Go to home</a>
			</div>

			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
		</main>
	</div>
</div>
</body>
</html>