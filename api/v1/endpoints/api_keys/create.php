<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::API_CONTROLLER);

try {
	$userID = $_SESSION['userID'];
	$canExpire = $_POST['canExpire'] ?? $_GET['canExpire'] ?? null;
	$expiresAt = $_POST['expiresAt'] ?? $_GET['expiresAt'] ?? null;
	$isAdmin = $_POST['isAdmin'] ?? $_GET['isAdmin'] ?? false;

	if ($canExpire === null || $expiresAt === null) {
		http_response_code(400);
		echo json_encode(["success" => false, "message" => "Missing required parameters"]);
		exit();
	}

	if ($isAdmin === true) {
		requireAdminAccess();
	}

	$apiKey = ApiController::createApiKey($userID, $canExpire, $expiresAt ? new DateTime($expiresAt) : null, $isAdmin);
	http_response_code(201);
	echo json_encode(["success" => true, "apiKey" => $apiKey]);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Failed to create API key: " . $e->getMessage()]);
}
