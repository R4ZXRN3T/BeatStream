<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::ARTIST_CONTROLLER);

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

	$artist = ArtistController::getArtistByID($artistID);
	if ($artist === null) {
		http_response_code(404);
		echo json_encode(["error" => "Artist not found"]);
		exit();
	}

	echo json_encode($artist->json_encode());
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve albums for artist: " . $e->getMessage()]);
}
