<?php
// Return JSON list of playlists owned by the logged-in user: [{ playlistID, name }]
header('Content-Type: application/json');
header('Cache-Control: no-store');

session_start();

require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php";

$response = function (int $code, array $data) {
	http_response_code($code);
	echo json_encode($data);
	exit;
};

if (!isset($_SESSION['account_loggedin']) || $_SESSION['account_loggedin'] !== true) $response(401, ['error' => 'Not authenticated']);

$userID = (int)($_SESSION['userID'] ?? 0);
if ($userID <= 0) $response(401, ['error' => 'Invalid session']);

try {
	$conn = DBConn::getConn();
	$stmt = $conn->prepare("SELECT playlistID, name FROM playlist WHERE creatorID = ? ORDER BY name ");
	$stmt->bind_param('i', $userID);
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = [];
	while ($row = $result->fetch_assoc()) {
		$rows[] = [
			'playlistID' => (int)$row['playlistID'],
			'name' => $row['name'],
		];
	}
	$stmt->close();
	echo json_encode($rows);
	exit;
} catch (Throwable $e) {
	$response(500, ['error' => 'Server error']);
}