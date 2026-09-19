<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::ALBUM_CONTROLLER);

try {
	$artistID = $params['id'] ?? null;

	if ($artistID === null) {
		$artistID = $_GET['id'] ?? null;
		if ($artistID === null) {
			http_response_code(400);
			echo json_encode(["error" => "Missing id parameter"]);
			exit();
		}
	}

	$albumList = AlbumController::getArtistAlbums($artistID);
	$jsonReady = array_map(fn(Album $a) => $a->json_encode(), $albumList);
	echo json_encode($jsonReady);
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve albums for artist: " . $e->getMessage()]);
}
