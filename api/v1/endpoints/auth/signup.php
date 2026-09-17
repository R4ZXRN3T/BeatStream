<?php
session_start();
header("Content-Type: application/json");
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/UserController.php";
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/converter.php";

try {
	$username = $_POST['usernameInput'] ?? null;
	$email = $_POST['emailInput'] ?? null;
	$password = $_POST['userPasswordInput'] ?? null;
	$imageToUpload = $_FILES["imageToUpload"] ?? null;

	if ($username === null || $email === null || $password === null) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
		exit();
	}

	if ($imageToUpload !== null && $imageToUpload['error'] === UPLOAD_ERR_OK) {
		$imageNames = Converter::uploadImage($imageToUpload, ImageType::USER);

		if ($imageNames['success'] === false) {
			http_response_code(400);
			echo json_encode(["error" => "Image upload failed: " . $imageNames['error']]);
			exit();
		}
	}

	if (UserController::usernameExists($username)) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Username already exists.']);
		exit();
	} elseif (UserController::emailExists($email)) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Email already exists.']);
		exit();
	}

	UserController::insertUser(new User(
		0,
		$username,
		$email,
		$password,
		"",
		FALSE,
		FALSE,
		FALSE,
		$imageNames['large_filename'] ?? '',
		$imageNames['thumbnail_filename'] ?? ''
	));
	echo json_encode(['success' => true, 'message' => 'User created successfully.']);
	http_response_code(201);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'error' => 'Failed to create user: ' . $e->getMessage()]);
	exit();
}
