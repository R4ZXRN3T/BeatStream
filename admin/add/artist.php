<?php requireAdminAccess($GLOBALS['PROJECT_ROOT'] . '/admin/blocked.php'); ?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - add an artist</title>
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
			includeComponent('adminTabBar.php', ['activePage' => 'artist']);
			?>

			<div class="container mt-5">
				<h1>Add Artist</h1>
				<form action="<?= $GLOBALS['PROJECT_ROOT'] ?>/api/v1/artists" method="post" id="addArtistForm"
					  enctype="multipart/form-data">
					<div class="form-group">
						<label for="name">Name:</label>
						<input type="text" id="name" name="name" class="form-control" placeholder="Enter artist name"
							   required>
					</div>
					<div class="form-group">
						<label for="imageFile">Artist Image:</label>
						<input type="file" id="imageFile" name="image" class="form-control" accept="image/*">
					</div>
					<div class="form-group">
						<div id="userFields">
							<label for="userID">User:</label>
							<select name="userID" id="userID" class="form-control" required>
								<option value="">--Please Select--</option>
							</select>
						</div>
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
		import {populateSelect} from '<?= $GLOBALS['PROJECT_ROOT'] ?>/components/dynamicSelect.js';

		const API = '<?= $GLOBALS["PROJECT_ROOT"] ?>/api/v1';

		initForm({
			selector: '#addArtistForm',
			spinnerSelector: '#spinner'
		});

		populateSelect({
			selector: '#userFields select',
			url: `${API}/users`,
			valueKey: 'userID',
			textFormatter: user => user.username
		});
	</script>
</div>
</body>
</html>
