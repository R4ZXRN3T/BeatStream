<?php
header('Content-Type: application/json');
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/PlaylistController.php';

try {
	$playlistList = PlaylistController::getPlaylistList();
	$jsonReady = array_map(fn(Playlist $p) => $p->json_encode(), $playlistList);
	echo json_encode($jsonReady);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve playlist list: " . $e->getMessage()]);
}
