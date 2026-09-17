<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/SongController.php';

header('Content-Type: application/json');

$songID = $params['id'] ?? null;

if ($songID === null) {
	http_response_code(400);
	echo json_encode(['error' => 'Missing songID parameter']);
	exit;
}

$song = SongController::getSongByID($songID);

if ($song === false) {
	http_response_code(404);
	echo json_encode(['error' => 'Song not found']);
	exit;
}

echo json_encode($song->json_encode());
