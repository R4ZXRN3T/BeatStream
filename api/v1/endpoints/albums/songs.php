<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/SongController.php';

header('Content-Type: application/json');

$albumID = $params['id'] ?? null;

if ($albumID === null) {
	http_response_code(400);
	echo json_encode(['error' => 'Missing album ID parameter']);
	exit;
}

try {
	$songList = SongController::getAlbumSongs($albumID);
	$jsonReady = array_map(fn(Song $s) => $s->json_encode(), $songList);
	echo json_encode($jsonReady);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve songs for album ID $albumID: " . $e->getMessage()]);
}
