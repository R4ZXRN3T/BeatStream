<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::ALBUM_CONTROLLER);

try {
	$albumList = AlbumController::getAlbumList();
	$jsonReady = array_map(fn(Album $a) => $a->json_encode(), $albumList);
	echo json_encode($jsonReady);
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve album list: " . $e->getMessage()]);
}
