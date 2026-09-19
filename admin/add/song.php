<?php requireAdminAccess($GLOBALS['PROJECT_ROOT_DIR']); ?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - add a song</title>
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

			<?php
			includeComponent('adminNavBar.php', ['activePage' => 'add']);
			includeComponent('adminTabBar.php', ['activePage' => 'song']);
			?>

			<!-- Song Form -->
			<div class="container mt-5">
				<h1>Add Song</h1>
				<form action="<?= $GLOBALS['PROJECT_ROOT'] ?>/api/v1/songs" method="post" id="addSongForm"
					  enctype="multipart/form-data">
					<div class="form-group">
						<label for="title">Title:</label>
						<input type="text" id="title" name="title" class="form-control"
							   placeholder="Enter song title" required>
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
						<label for="genre">Genre:</label>
						<input type="text" id="genre" name="genre" class="form-control" placeholder="Enter genre"
							   required>
					</div>

					<div class="form-group">
						<label for="releaseDate">Release Date:</label>

						<input type="date" id="releaseDate" name="releaseDate" class="form-control"
							   placeholder="Enter release date" required>
					</div>

					<div class="form-group">
						<label for="songFile">File:</label>
						<input type="file" id="songFile" name="audioFile" class="form-control" accept="audio/*"
							   placeholder="Upload song file" required>
					</div>

					<div class="form-group">
						<label for="songImage">Image:&nbsp;&nbsp;&nbsp;&nbsp;(not required)</label>
						<input type="file" id="songImage" name="imageFile" class="form-control" accept="image/*">
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
			selector: '#addSongForm',
			spinnerSelector: '#spinner'
		});

		initDynamicFields({
			containerSelector: '#artistFields',
			fieldSelector: '.artist-field',
			removeButtonSelector: '.remove-artist',
			addButtonSelector: '#addArtistButton'
		});

		populateSelect({
			selector: '#artistFields select',
			url: `${API}/artists`,
			valueKey: 'artistID',
			textFormatter: artist => artist.name
		});
	</script>
</div>
</body>
</html>
