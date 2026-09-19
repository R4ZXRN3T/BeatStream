<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::ARTIST_CONTROLLER);
includeConverter();

try {
	$name = $_POST['name'] ?? null;
	$userID = $_POST['userID'] ?? null;
	$image = $_FILES['image'] ?? null;

	if ($name === null || $userID === null) {
		http_response_code(400);
		echo json_encode(["error" => "Missing required parameters"]);
		exit();
	}

	if ($image !== null && $image['error'] === UPLOAD_ERR_OK) {
		$imageNames = Converter::uploadImage($image, ImageType::ARTIST);

		if ($imageNames['success'] === false) {
			http_response_code(400);
			echo json_encode(["error" => "Image upload failed: " . htmlspecialchars($imageNames['error'])]);
			exit();
		}
	}

	ArtistController::insertArtist(new Artist(
		0,
		$name,
		$imageNames['large_filename'] ?? '',
		$imageNames['thumbnail_filename'] ?? '',
		"",
		$userID,
	));

	echo json_encode(["success" => true, "message" => "Artist created successfully"]);
	http_response_code(201);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Error creating artist: " . $e->getMessage()]);
}
