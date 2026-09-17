<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/SongController.php';

header('Content-Type: application/json');

$artistID = $params['id'] ?? null;

if ($artistID === null) {
	http_response_code(400);
	echo json_encode(['error' => 'Missing artist ID parameter']);
	exit;
}

try {
	$songList = SongController::getArtistSongs($artistID);
	$jsonReady = array_map(fn(Song $s) => $s->json_encode(), $songList);
	echo json_encode($jsonReady);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve songs for artist ID $artistID: " . $e->getMessage()]);
}
