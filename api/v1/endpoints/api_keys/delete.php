<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::API_CONTROLLER);

try {
	$apiKey = $_POST['api_key'] ?? null;

	if ($apiKey === null) {
		http_response_code(400);
		echo json_encode(["success" => false, "message" => "Missing api_key parameter"]);
		exit();
	}

	if (ApiController::apiKeyBelongsToUser($apiKey, $_SESSION['userID'])) {
		requireAdminAccess();
	}

	$deleted = ApiController::deleteApiKey($apiKey);
	if ($deleted) {
		http_response_code(200);
		echo json_encode(["success" => true, "message" => "API key deleted successfully"]);
	} else {
		http_response_code(404);
		echo json_encode(["success" => false, "message" => "API key not found"]);
	}
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Failed to delete API key: " . $e->getMessage()]);
}
