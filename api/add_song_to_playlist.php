<?php
// Add a song to a playlist the user owns. Body: application/x-www-form-urlencoded (songID, playlistID)
header('Content-Type: application/json');
header('Cache-Control: no-store');

session_start();

require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php";

$respond = function (int $code, array $data) {
	http_response_code($code);
	echo json_encode($data);
	exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') $respond(405, ['error' => 'Method not allowed']);
if (!isset($_SESSION['account_loggedin']) || $_SESSION['account_loggedin'] !== true) $respond(401, ['error' => 'Not authenticated']);

$songID = isset($_POST['songID']) ? (int)$_POST['songID'] : 0;
$playlistID = isset($_POST['playlistID']) ? (int)$_POST['playlistID'] : 0;

if ($songID <= 0 || $playlistID <= 0) $respond(400, ['error' => 'Invalid song or playlist']);

$conn = DBConn::getConn();

// Ensure the playlist is owned by current user
$check = $conn->prepare("SELECT creatorID FROM playlist WHERE playlistID = ?");
$check->bind_param('i', $playlistID);
$check->execute();
$res = $check->get_result();
$row = $res->fetch_assoc();
$check->close();

if (!$row) $respond(404, ['error' => 'Playlist not found']);
if ((int)$row['creatorID'] !== (int)($_SESSION['userID'] ?? 0)) $respond(403, ['error' => 'You do not have permission to modify this playlist']);

// Ensure song exists
$chkSong = $conn->prepare("SELECT songID FROM song WHERE songID = ?");
$chkSong->bind_param('i', $songID);
$chkSong->execute();
$sr = $chkSong->get_result();
$chkSong->close();
if ($sr->num_rows === 0) $respond(404, ['error' => 'Song not found']);

// Check if already in playlist
$exists = $conn->prepare("SELECT 1 FROM in_playlist WHERE songID = ? AND playlistID = ?");
$exists->bind_param('ii', $songID, $playlistID);
$exists->execute();
$er = $exists->get_result();
$exists->close();
if ($er->num_rows > 0) $respond(200, ['ok' => true, 'message' => 'Song already in playlist']);

// Determine next index
$idxStmt = $conn->prepare("SELECT COALESCE(MAX(songIndex), -1) + 1 AS nextIndex FROM in_playlist WHERE playlistID = ?");
$idxStmt->bind_param('i', $playlistID);
$idxStmt->execute();
$nextIndex = (int)($idxStmt->get_result()->fetch_assoc()['nextIndex'] ?? 0);
$idxStmt->close();

// Insert
$ins = $conn->prepare("INSERT INTO in_playlist (songID, playlistID, songIndex) VALUES (?, ?, ?)");
$ins->bind_param('iii', $songID, $playlistID, $nextIndex);
try {
	$ins->execute();
	$ins->close();
} catch (Throwable $e) {
	$respond(500, ['error' => 'Failed to add song']);
}

// Update playlist length and duration
try {
	// Get song length (ms) from song.songLength
	$lenStmt = $conn->prepare("SELECT songLength FROM song WHERE songID = ?");
	$lenStmt->bind_param('i', $songID);
	$lenStmt->execute();
	$songLenRow = $lenStmt->get_result()->fetch_assoc();
	$lenStmt->close();
	$songLen = (int)($songLenRow['songLength'] ?? 0);

	$upd = $conn->prepare("UPDATE playlist SET length = COALESCE(length,0) + 1, duration = COALESCE(duration,0) + ? WHERE playlistID = ?");
	$upd->bind_param('ii', $songLen, $playlistID);
	$upd->execute();
	$upd->close();
} catch (Throwable $e) {
	// Non-fatal; proceed
}

$respond(200, ['ok' => true, 'playlistID' => $playlistID, 'songID' => $songID, 'index' => $nextIndex]);