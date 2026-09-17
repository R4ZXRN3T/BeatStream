<?php
header('Content-Type: application/json');
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/PlaylistController.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/SongController.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/converter.php';

try {
	$name = $_POST['name'] ?? null;
	$songIDs = $_POST['songIDs'] ?? null;
	$image = $_FILES['image'] ?? null;
	$creatorID = $_POST['creatorID'] ?? null;

	if ($creatorID !== null) {
		require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_admin_access.php';
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
