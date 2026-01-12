<?php
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}
@session_write_close();

$songID = intval($_GET['id'] ?? null);
if ($songID == null) {
	http_response_code(400);
	echo json_encode(['error' => 'Invalid song ID']);
	exit;
}

require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php";

$albumData = SongController::getSongAlbum($songID);
if ($albumData === null) {
	http_response_code(404);
	echo json_encode(['error' => 'Song or album not found']);
	exit;
}

header('Content-Type: application/json');
echo json_encode([
	'index' => $albumData['index'],
	'albumID' => $albumData['album']->getAlbumID(),
	'albumName' => $albumData['album']->getName()
]);