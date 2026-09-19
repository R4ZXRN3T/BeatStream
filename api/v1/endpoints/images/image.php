<?php
requireAccess();
includeController(ControllerType::IMAGE_CONTROLLER);

$imageSize = $_POST['s'] ?? $_GET['s'] ?? null;
$imageID = $params['id'] ?? null;

if ($imageSize === null || $imageID === null) {
	http_response_code(400);
	echo json_encode(["error" => "Missing size or id parameter"]);
	exit();
}

if ($imageSize === 'large' || $imageSize === 'thumbnail') {
	header('Content-Type: image/webp');
} else {
	header('Content-Type: image/png');
}

$image = ImageController::getImage($imageID);

if ($image === false) {
	http_response_code(404);
	echo json_encode(["error" => "Image not found"]);
	exit();
}

$correctImage = $image[$imageSize] ?? null;

if ($correctImage === null) {
	http_response_code(404);
	echo json_encode(["error" => "Image not found"]);
	exit();
}

echo json_encode($correctImage);
