<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::SONG_CONTROLLER);

try {
	$songList = SongController::getSongList();
	$jsonReady = array_map(fn(Song $s) => $s->json_encode(), $songList);
	echo json_encode($jsonReady);
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve song list: " . $e->getMessage()]);
}
