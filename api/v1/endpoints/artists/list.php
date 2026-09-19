<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::ARTIST_CONTROLLER);

try {
	$artistList = ArtistController::getArtistList();
	$jsonReady = array_map(fn(Artist $a) => $a->json_encode(), $artistList);
	echo json_encode($jsonReady);
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve artist list: " . $e->getMessage()]);
}
