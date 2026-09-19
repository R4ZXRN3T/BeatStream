<?php
session_start();
header('Content-Type: application/json');
includeController(ControllerType::USER_CONTROLLER);

try {
	$email = $_POST['email'] ?? '';
	$password = $_POST['password'] ?? '';

	if (empty($email) || empty($password)) {
		echo json_encode(["success" => false, "error" => "Email and password are required."]);
		http_response_code(400);
		exit();
	}

	$stmt = DBConn::getConn()->prepare("SELECT userPassword, salt, username, userID, isAdmin, hasAccess, thumbnailName FROM user WHERE email = ?");
	if (!$stmt) {
		throw new Exception('Prepare failed: ' . DBConn::getConn()->error);
	}
	$stmt->bind_param('s', $email);
	$stmt->execute();
	$user = $stmt->get_result()->fetch_assoc();
	$stmt->close();

	if ($user && Utils::hashPassword($password, $user['salt']) === $user['userPassword']) {
		session_regenerate_id(true);
		$_SESSION += [
			'account_loggedin' => true,
			'email' => $email,
			'username' => $user['username'],
			'userID' => $user['userID'],
			'hasAccess' => (bool)$user['hasAccess'],
			'isAdmin' => (bool)$user['isAdmin'],
			'imageName' => $user['thumbnailName'],
		];
		echo json_encode(["success" => true]);
		http_response_code(200);
	} else {
		http_response_code(401);
		echo json_encode(["success" => false, "error" => "Invalid email or password."]);
	}
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
