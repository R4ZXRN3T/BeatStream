<?php
header('Content-Type: application/json');
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/ArtistController.php';

try {
	$artistList = ArtistController::getArtistList();
	$jsonReady = array_map(fn(Artist $a) => $a->json_encode(), $artistList);
	echo json_encode($jsonReady);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve artist list: " . $e->getMessage()]);
}
