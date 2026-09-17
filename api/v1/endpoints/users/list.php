<?php
header('Content-Type: application/json');
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/UserController.php';

try {
	$userList = UserController::getUserList();
	$jsonReady = array_map(fn(User $u) => $u->json_encode(), $userList);
	echo json_encode($jsonReady);
	http_response_code(200);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => "Failed to retrieve user list: " . $e->getMessage()]);
}
