<?php
header('Content-Type: application/json');
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_admin_access.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/PlaylistController.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/converter.php';

try {
	$username = $_POST['username'] ?? null;
	$email = $_POST['email'] ?? null;
	$password = $_POST['password'] ?? null;
	$image = $_FILES['image'] ?? null;
	$hasAccess = $_POST['has_access'] ?? false;
	$isArtist = $_POST['is_artist'] ?? false;
	$isAdmin = $_POST['is_admin'] ?? false;

	if ($username === null || $email === null || $password === null) {
		http_response_code(400);
		echo json_encode(["error" => "Missing required parameters"]);
		exit();
	}

	if ($image !== null && $image['error'] === UPLOAD_ERR_OK) {
		$imageNames = Converter::uploadImage($image, ImageType::USER);

		if ($imageNames['success'] === false) {
			http_response_code(400);
			echo json_encode(["error" => "Image upload failed: " . htmlspecialchars($imageNames['error'])]);
			exit();
		}
	}

	UserController::insertUser(new User(
		0,
		$username,
		$email,
		$password,
		'',
		$hasAccess,
		$isAdmin,
		$isArtist,
		$imageNames['large_filename'] ?? '',
		$imageNames['thumbnail_filename'] ?? ''
	));

	http_response_code(201);
	echo json_encode(["success" => true, "message" => "User created successfully"]);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Error creating user: " . $e->getMessage()]);
}
