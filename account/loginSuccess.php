<?php
session_start();
// If the user is logged in, redirect to the home page
if (!isset($_SESSION['account_loggedin'])) {
	header("location: ../");
	exit();
}
?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Successful!</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/favicon.ico" rel="icon">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/mainStyle.css" rel="stylesheet">
</head>

<body>
<?php include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/topBar.php"); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<?php
		include($GLOBALS['PROJECT_ROOT_DIR'] . "/components/sidebar.php");
		?>
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">

			<div class="container mt-5 text-center">
				<h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
				<p>You have successfully logged in.</p>
			</div>

			<div>
				<div class="container mt-5 text-center">
					<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/" class="btn btn-primary">Go to Home</a>
					<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/account/logout.php" class="btn btn-secondary">Logout</a>
				</div>
			</div>
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
		</main>
	</div>
</div>
</body>
</html>
