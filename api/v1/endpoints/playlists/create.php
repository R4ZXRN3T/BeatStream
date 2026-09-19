<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::PLAYLIST_CONTROLLER);
includeController(ControllerType::SONG_CONTROLLER);
includeConverter();

try {
	$name = $_POST['name'] ?? null;
	$songIDs = $_POST['songIDs'] ?? null;
	$image = $_FILES['image'] ?? null;
	$creatorID = $_POST['creatorID'] ?? null;

	if ($creatorID !== null) {
		requireAdminAccess();
	} else {
		$creatorID = $_SESSION['userID'];
	}

	if ($name === null || $songIDs === null) {
		http_response_code(400);
		echo json_encode(["error" => "Missing required parameters"]);
		exit();
	}

	if ($image !== null && $image['error'] === UPLOAD_ERR_OK) {
		$imageNames = Converter::uploadImage($image, ImageType::PLAYLIST);

		if ($imageNames['success'] === false) {
			http_response_code(400);
			echo json_encode(["error" => "Image upload failed: " . htmlspecialchars($imageNames['error'])]);
			exit();
		}
	}

	PlaylistController::insertPlaylist(new Playlist(
			0,
			$name,
			$songIDs,
			array_reduce($songIDs, fn($carry, $id) => $carry + SongController::getSongByID($id)->getSongLength(), 0),
			count($songIDs),
			$imageNames['large_filename'] ?? '',
			$imageNames['thumbnail_filename'] ?? '',
			$creatorID,
			UserController::getUserById($creatorID)->getUsername()
		)
	);
	echo json_encode(['success' => true, 'message' => 'Playlist created successfully.']);
	http_response_code(201);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["success" => false, "error" => "Failed to create playlist: " . $e->getMessage()]);
	exit();
}
