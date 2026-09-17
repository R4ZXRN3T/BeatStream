<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/ApiController.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/UserController.php';

$authorized = false;
$apiKey = $_POST["api_key"] ?? $_GET["api_key"] ?? null;
if ($apiKey != NULL) {
	$apiKeyStatus = ApiController::apiKeyStatus($apiKey);
	if ($apiKeyStatus["valid"] === false) {
		$authorized = false;
		http_response_code(401);
		echo json_encode(["error" => $apiKeyStatus["status"]]);
	} else {
		$authorized = true;
	}
}

if (!$authorized) {
	session_start();
	$userID = $_SESSION["userID"] ?? null;
	if ($userID === null) {
		http_response_code(401);
		echo json_encode(["error" => "User is not logged in"]);
	} else {
		$authorized = UserController::getUserById($userID)->isHasAccess();
	}
}
