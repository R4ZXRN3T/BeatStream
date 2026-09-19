<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::SONG_CONTROLLER);

try {
	$artistID = $params['id'] ?? null;

	if ($artistID === null) {
		http_response_code(400);
		echo json_encode(['error' => 'Missing artist ID parameter']);
		exit();
	}

	$songList = SongController::getArtistSongs($artistID);
	$jsonReady = array_map(fn(Song $s) => $s->json_encode(), $songList);
	echo json_encode($jsonReady);
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve songs for artist ID $artistID: " . $e->getMessage()]);
}
