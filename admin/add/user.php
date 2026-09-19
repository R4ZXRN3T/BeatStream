<?php requireAdminAccess($GLOBALS['PROJECT_ROOT'] . "/admin/blocked.php"); ?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Add a user - BeatStream</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/mainStyle.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/favicon.ico" rel="icon">
</head>

<body>

<?php includeComponent('topBar.php'); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<?php includeComponent('sidebar.php', ['activePage' => 'admin']); ?>
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">

			<!-- Admin Navigation Bar -->
			<?php
			includeComponent('adminNavBar.php', ['activePage' => 'add']);
			includeComponent('adminTabBar.php', ['activePage' => 'user']);
			?>

			<div class="container mt-5">
				<h1>Add User</h1>

				<form action="<?= $GLOBALS['PROJECT_ROOT'] ?>/api/v1/users" method="post" id="addUserForm"
					  enctype="multipart/form-data">
					<div class="form-group">
						<label for="username">Username:</label>
						<input type="text" id="username" name="username" class="form-control"
							   placeholder="Enter username" required>
					</div>
					<div class="form-group">
						<label for="email">E-Mail address:</label>
						<input type="text" id="email" name="email" class="form-control" placeholder="Enter email"
							   required>
					</div>
					<div class="form-group">
						<label for="userPassword">Password:</label>
						<input type="text" id="userPassword" name="password" class="form-control"
							   placeholder="Enter password" required>
					</div>
					<div class="form-group">
						<label for="isAdminInput">Admin</label>
						<input type="checkbox" id="isAdminInput" name="is_admin" value=TRUE>
					</div>
					<div class="form-group">
						<label for="userImage">Profile Image:</label>
						<input type="file" id="userImage" name="image" class="form-control" accept="image/*">
					</div>

					<input type="submit" class="btn btn-primary mt-3" value="Submit">
				</form>
				<div id="toast-container" class="position-fixed bottom-10 start-50 translate-middle-x p-3"
					 style="z-index:1200;"></div>
				<div id="spinner"
					 class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark opacity-75 d-none"
					 style="pointer-events:none;">
					<div class="spinner-border text-primary" role="status">
						<span class="visually-hidden">Loading…</span>
					</div>
				</div>
			</div>
		</main>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
	<script type="module">
		import {initForm} from '<?= $GLOBALS['PROJECT_ROOT'] ?>/components/formHandler.js';

		initForm({
			selector: '#addUserForm',
			spinnerSelector: '#spinner'
		});
	</script>
</div>
</body>
</html>
