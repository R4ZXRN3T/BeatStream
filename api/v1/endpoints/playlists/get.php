<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::PLAYLIST_CONTROLLER);

try {
	$playlistID = $params['id'] ?? null;

	if ($playlistID === null) {
		$playlistID = $_GET['id'] ?? null;
		if ($playlistID === null) {
			http_response_code(400);
			echo json_encode(["error" => "Missing id parameter"]);
			exit();
		}
	}
	$playlist = PlaylistController::getPlaylistByID($playlistID);
	if ($playlist === null) {
		http_response_code(404);
		echo json_encode(["error" => "Playlist not found"]);
		exit();
	}

	echo json_encode($playlist->json_encode());
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve albums for artist: " . $e->getMessage()]);
}
