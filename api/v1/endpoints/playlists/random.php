<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::PLAYLIST_CONTROLLER);

try {
	$limit = $_POST['limit'] ?? $_GET['limit'] ?? 3;
	$playlistList = PlaylistController::getRandomPlaylists($limit);
	$jsonReady = array_map(fn(Playlist $p) => $p->json_encode(), $playlistList);
	echo json_encode($jsonReady);
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve random playlists: " . $e->getMessage()]);
}
