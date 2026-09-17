<?php
header('Content-Type: application/json');
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/AlbumController.php';

try {
	$albumList = AlbumController::getAlbumList();
	$jsonReady = array_map(fn(Album $a) => $a->json_encode(), $albumList);
	echo json_encode($jsonReady);
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve album list: " . $e->getMessage()]);
}
