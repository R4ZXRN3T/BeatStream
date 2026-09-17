<?php
header('Content-Type: application/json');

session_start();
$_SESSION = null; // Unset all session variables
session_unset();
session_destroy();

echo json_encode(["success" => true, "message" => "Logged out successfully."]);
http_response_code(200);
