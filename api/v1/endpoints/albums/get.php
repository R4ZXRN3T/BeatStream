<?php
header('Content-Type: application/json');
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/AlbumController.php';

try {
	$albumID = $params['id'] ?? null;

	if ($albumID === null) {
		$albumID = $_GET['id'] ?? null;
		if ($albumID === null) {
			http_response_code(400);
			echo json_encode(["error" => "Missing id parameter"]);
			exit();
		}
	}

	$album = AlbumController::getAlbumByID($albumID);
	if ($album === null) {
		http_response_code(404);
		echo json_encode(["error" => "Album not found"]);
		exit();
	}

	echo json_encode($album->json_encode());
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve albums for artist: " . $e->getMessage()]);
}
