<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/UserController.php';

header('Content-Type: application/json');

$userID = $params['id'] ?? null;

if ($userID === null) {
	http_response_code(400);
	echo json_encode(['error' => 'Missing userID parameter']);
	exit;
}

$user = UserController::getUserById($userID);

if ($user === false) {
	http_response_code(404);
	echo json_encode(['error' => 'Song not found']);
	exit;
}

echo json_encode($user->json_encode());
