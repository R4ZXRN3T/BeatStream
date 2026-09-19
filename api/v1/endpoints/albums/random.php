<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::ALBUM_CONTROLLER);

try {
	$limit = $_POST['limit'] ?? $_GET['limit'] ?? 3;
	$albumList = AlbumController::getRandomAlbums($limit);
	$jsonReady = array_map(fn(Album $a) => $a->json_encode(), $albumList);
	echo json_encode($jsonReady);
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve random albums: " . $e->getMessage()]);
}
