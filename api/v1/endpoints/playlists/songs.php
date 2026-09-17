<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/SongController.php';

header('Content-Type: application/json');

$playlistID = $params['id'] ?? null;

if ($playlistID === null) {
	http_response_code(400);
	echo json_encode(['error' => 'Missing playlist ID parameter']);
	exit;
}

try {
	$songList = SongController::getPlaylistSongs($playlistID);
	$jsonReady = array_map(fn(Song $s) => $s->json_encode(), $songList);
	echo json_encode($jsonReady);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve songs for playlist ID $playlistID: " . $e->getMessage()]);
}
