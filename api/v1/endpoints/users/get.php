<?php
header('Content-Type: application/json');
requireAccess();
includeController(ControllerType::USER_CONTROLLER);

try {
	$userID = $params['id'] ?? null;

	if ($userID === null) {
		http_response_code(400);
		echo json_encode(['error' => 'Missing userID parameter']);
		exit();
	}

	$user = UserController::getUserById($userID);

	if ($user === false) {
		http_response_code(404);
		echo json_encode(['error' => 'User not found']);
		exit();
	}

	echo json_encode($user->json_encode());
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to retrieve user: ' . $e->getMessage()]);
}
