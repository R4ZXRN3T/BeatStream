<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::SONG_CONTROLLER);
includeController(ControllerType::USER_CONTROLLER);
includeController(ControllerType::ARTIST_CONTROLLER);
includeConverter();

try {
	$title = $_POST['title'] ?? null;
	$artistIDs = $_POST['artistIDs'] ?? null;
	$genre = $_POST['genre'] ?? null;
	$releaseDate = $_POST['releaseDate'] ?? null;
	$audioFile = $_FILES['audioFile'] ?? null;
	$imageFile = $_FILES['imageFile'] ?? null;

	if (!$title || !$artistIDs || !$genre || !$releaseDate || !$audioFile) {
		http_response_code(400);
		echo json_encode(["error" => "Missing required parameters"]);
		exit();
	}

	if (in_array(UserController::getUserArtistID($_SESSION['userID']), $artistIDs) === false) {
		requireAdminAccess();
	}

	if ($imageFile !== null && $imageFile['error'] === UPLOAD_ERR_OK) {
		$imageNames = Converter::uploadImage($imageFile, ImageType::SONG);

		if ($imageNames['success'] === false) {
			http_response_code(400);
			echo json_encode(["success" => false, "error" => "Image upload failed: " . $imageNames['error']]);
			exit();
		}
	}

	if ($audioFile['error'] === UPLOAD_ERR_OK) {
		$audioFileNames = Converter::uploadAudio($audioFile);

		if ($audioFileNames['success'] === false) {
			http_response_code(400);
			echo json_encode(["success" => false, "error" => "Audio upload failed: " . $audioFileNames['error']]);
			exit();
		}
	} else {
		http_response_code(400);
		echo json_encode(["success" => false, "error" => "Audio file upload error"]);
		exit();
	}

	SongController::insertSong(new Song(
		0,
		$title,
		array_map(fn($id) => ArtistController::getArtistByID($id)->getName(), $artistIDs),
		$artistIDs,
		$genre,
		$releaseDate,
		$audioFileNames['duration'] ?? 0,
		$audioFileNames['flac_filename'] ?? '',
		$audioFileNames['opus_filename'] ?? '',
		$imageNames['large_filename'] ?? '',
		$imageNames['thumbnail_filename'] ?? '',
		$imageNames['original_filename'] ?? ''
	));
	http_response_code(201);
	echo json_encode(["success" => true, "message" => "Song created successfully"]);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["success" => false, "error" => "Error creating song: " . $e->getMessage()]);
}
