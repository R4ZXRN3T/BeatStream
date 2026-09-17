<?php
header('Content-Type: application/json');
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/ApiController.php';

try {
	$apiKey = $_POST['api_key'] ?? null;

	if ($apiKey === null) {
		http_response_code(400);
		echo json_encode(["success" => false, "message" => "Missing api_key parameter"]);
		exit();
	}

	if (ApiController::apiKeyBelongsToUser($apiKey, $_SESSION['userID'])) {
		require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_admin_access.php';
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
