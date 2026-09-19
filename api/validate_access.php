<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/ApiController.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/UserController.php';

$redirect = $redirect ?? null;

$authorized = false;
$apiKey = $_POST["api_key"] ?? $_GET["api_key"] ?? null;
if ($apiKey != NULL) {
	$apiKeyStatus = ApiController::apiKeyStatus($apiKey);
	if ($apiKeyStatus["valid"] === false) {
		$authorized = false;
		http_response_code(401);
		echo json_encode(["error" => $apiKeyStatus["status"]]);
		if ($redirect != null) header("Location: $redirect");
		exit();
	} else {
		$authorized = true;
	}
}

if (!$authorized) {
	if (session_status() === PHP_SESSION_NONE) session_start();
	$userID = $_SESSION["userID"] ?? null;
	if ($userID === null) {
		http_response_code(401);
		echo json_encode(["error" => "User is not logged in"]);
		if ($redirect != null) header("Location: $redirect");
		exit();
	} else {
		$authorized = UserController::getUserById($userID)->isHasAccess();
	}
}

if (!$authorized) {
	http_response_code(403);
	echo json_encode(["error" => "User does not have access"]);
	if ($redirect != null) header("Location: $redirect");
	exit();
}
