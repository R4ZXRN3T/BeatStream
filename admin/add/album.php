<?php requireAdminAccess($GLOBALS['PROJECT_ROOT'] . "/admin/blocked.php"); ?>

<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Add an album - BeatStream</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/mainStyle.css" rel="stylesheet">
	<link href="<?= $GLOBALS['PROJECT_ROOT'] ?>/favicon.ico" rel="icon">
</head>

<body>

<?php includeComponent('topBar.php'); ?>

<div class="container-fluid">
	<div class="row">
		<!-- Sidebar -->
		<?php includeComponent("sidebar.php", ['activePage' => 'admin']); ?>
		<!-- Main Content -->
		<main class="main col-md ms-sm-auto px-0 py-0">

			<!-- Admin Navigation Bar -->
			<?php
			includeComponent("adminNavBar.php", ['activePage' => 'add']);
			includeComponent("adminTabBar.php", ['activePage' => 'album']);
			?>

			<div class="container mt-5">
				<h1>Add Album</h1>
				<form action="<?= $GLOBALS['PROJECT_ROOT'] ?>/api/v1/albums" method="post" id="addAlbumForm"
					  enctype="multipart/form-data">
					<div class="form-group">
						<label for="name">Album title:</label>
						<input type="text" id="name" name="name" class="form-control"
							   placeholder="Enter album title" required>
					</div>

					<div class="form-group">
						<label for="artist">Artists:</label>
						<div id="artistFields">
							<div class="artist-field d-flex mb-2">
								<label for="artist-select"></label>
								<select id="artist-select" name="artistIDs[]" class="form-control me-2" required>
									<option value="">--Please Select--</option>
								</select>
								<button type="button" class="btn btn-danger remove-artist">-</button>
							</div>
						</div>
						<button type="button" id="addArtistButton" class="btn btn-info mt-2">+</button>
					</div>


					<div class="form-group">
						<label for="song">Songs:</label>
						<div id="songFields">
							<div class="song-field d-flex mb-2">
								<label for="song-select"></label>
								<select id="song-select" name="songIDs[]" class="form-control me-2" required>
									<option value="">--Please Select--</option>
								</select>
								<button type="button" class="btn btn-danger remove-song">-</button>
							</div>
						</div>
						<button type="button" id="addSongButton" class="btn btn-info mt-2">+</button>
					</div>

					<div class="form-group">
						<label for="Image">Image:</label>
						<input type="file" id="image" name="image" class="form-control" accept="image/*" required>
					</div>

					<div class="form-group">
						<label for="releaseDate">Release Date:</label>
						<input type="date" id="releaseDate" name="releaseDate" class="form-control" required>
					</div>

					<div class="form-group">
						<label for="single">Is this a single?</label>
						<input type="checkbox" id="single" name="single" value="1">
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
		import {initDynamicFields} from '<?= $GLOBALS['PROJECT_ROOT'] ?>/components/dynamicFields.js';
		import {populateSelect} from '<?= $GLOBALS['PROJECT_ROOT'] ?>/components/dynamicSelect.js';

		const API = '<?= $GLOBALS["PROJECT_ROOT"] ?>/api/v1';

		initForm({
			selector: '#addAlbumForm',
			spinnerSelector: '#spinner'
		});

		initDynamicFields({
			containerSelector: '#artistFields',
			fieldSelector: '.artist-field',
			removeButtonSelector: '.remove-artist',
			addButtonSelector: '#addArtistButton'
		});

		initDynamicFields({
			containerSelector: '#songFields',
			fieldSelector: '.song-field',
			removeButtonSelector: '.remove-song',
			addButtonSelector: '#addSongButton'
		});

		populateSelect({
			selector: '#artistFields select',
			url: `${API}/artists`,
			valueKey: 'artistID',
			textFormatter: artist => artist.name
		});

		populateSelect({
			selector: '#songFields select',
			url: `${API}/songs`,
			valueKey: 'songID',
			textFormatter: song =>
				`${song.title} – ${song.artists.join(', ')}`
		});
	</script>
</div>
</body>
</html>
