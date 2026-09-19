<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::ALBUM_CONTROLLER);
includeController(ControllerType::ARTIST_CONTROLLER);
includeController(ControllerType::SONG_CONTROLLER);
includeController(ControllerType::USER_CONTROLLER);
includeConverter();

try {
	$title = $_POST['name'] ?? null;
	$artistIDs = $_POST['artistIDs'] ?? null;
	$songIDs = $_POST['songIDs'] ?? null;
	$image = $_FILES['image'] ?? null;
	$releaseDate = $_POST['releaseDate'] ?? null;
	$single = $_POST['single'] ?? false;

	if ($title === null || $artistIDs === null || $songIDs === null || $releaseDate === null || $single === null) {
		http_response_code(400);
		echo json_encode(["success" => false, "message" => "Missing required parameters"]);
		exit();
	}

	if (in_array(UserController::getUserArtistID($_SESSION['userID']), $artistIDs) === false) {
		requireAdminAccess();
	}

	if ($image !== null && $image['error'] === UPLOAD_ERR_OK) {
		$imageNames = Converter::uploadImage($image, ImageType::ALBUM);

		if ($imageNames['success'] === false) {
			http_response_code(400);
			echo json_encode(["success" => false, "message" => "Image upload failed: " . $imageNames['error']]);
			exit();
		}
	}

	AlbumController::insertAlbum(new Album(
			0,
			$title,
			$songIDs,
			array_map(fn($id) => ArtistController::getArtistByID($id)->getName(), $artistIDs),
			$artistIDs,
			$imageNames['large_filename'] ?? '',
			$imageNames['thumbnail_filename'] ?? '',
			count($songIDs),
			array_reduce($songIDs, fn($carry, $id) => $carry + SongController::getSongByID($id)->getSongLength(), 0),
			$releaseDate,
			$single === 'true',
			$imageNames['original_filename'] ?? ''
		)
	);

	echo json_encode(["success" => true, "message" => "Album created successfully"]);
	http_response_code(201);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Failed to create album: " . $e->getMessage()]);
	exit();
}
