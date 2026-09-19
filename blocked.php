<?php
session_start();
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>⛔Access Denied</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/mainStyle.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/favicon.ico" rel="icon">
</head>

<body>

<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">
			<div class="container mt-4">
				<h1 class="mb-4">Access Denied</h1>
				<p class="lead">You do not have permission to access this page.</p>
				<p>Please make sure the administrator gave you access to the page and you are logged into the correct
					account.</p>
				<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/account/logout.php" class="btn btn-primary">Logout</a>
			</div>

			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
		</main>
	</div>
</div>
</body>
</html>
