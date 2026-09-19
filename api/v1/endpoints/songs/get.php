<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::SONG_CONTROLLER);

try {
	$songID = $params['id'] ?? null;

	if ($songID === null) {
		http_response_code(400);
		echo json_encode(['error' => 'Missing songID parameter']);
		exit();
	}

	$song = SongController::getSongByID($songID);

	if ($song === false) {
		http_response_code(404);
		echo json_encode(['error' => 'Song not found']);
		exit();
	}

	echo json_encode($song->json_encode());
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to retrieve song: ' . $e->getMessage()]);
}
