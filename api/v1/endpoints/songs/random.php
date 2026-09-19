<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::SONG_CONTROLLER);

try {
	$limit = $_POST['limit'] ?? $_GET['limit'] ?? 3;
	$songList = SongController::getRandomSongs($limit);
	$jsonReady = array_map(fn(Song $s) => $s->json_encode(), $songList);
	echo json_encode($jsonReady);
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve random songs: " . $e->getMessage()]);
}
