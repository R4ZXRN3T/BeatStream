<?php

require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/ArtistController.php';

header('Content-Type: application/json');

try {
	$artistID = $params['id'] ?? null;

	if ($artistID === null) {
		$artistID = $_GET['id'] ?? null;
		if ($artistID === null) {
			http_response_code(400);
			echo json_encode(["error" => "Missing id parameter"]);
			exit;
		}
	}

	$artist = ArtistController::getArtistByID($artistID);
	if ($artist === null) {
		http_response_code(404);
		echo json_encode(["error" => "Artist not found"]);
		exit;
	}

	echo json_encode($artist->json_encode());
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve albums for artist: " . $e->getMessage()]);
}
