<?php
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}
@session_write_close();

require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php";
$id = $_GET['id'] ?? null;
$imageType = $_GET['type'] ?? null;
$resolution = $_GET['res'] ?? null;
if (!$id || !$imageType) {
	http_response_code(400);
	exit('Missing image ID or type');
}

$validTypes = ['song', 'album', 'artist', 'playlist', 'user'];
if (!in_array($imageType, $validTypes)) {
	http_response_code(400);
	exit('Invalid image type');
}
$validResolutions = ['original', 'large', 'thumbnail'];
if ($resolution && !in_array($resolution, $validResolutions)) {
	http_response_code(400);
	exit('Invalid resolution');
}

if ($resolution === 'original' && !in_array($imageType, ['song', 'album'])) {
	http_response_code(400);
	exit('Original resolution is only allowed for song or album images');
}

$imageDir = $GLOBALS['PROJECT_ROOT_DIR'] . "/images/$imageType/$resolution";
$name = null;

$stmt = DBConn::getConn()->prepare("SELECT imageName, originalImageName, thumbnailName FROM $imageType WHERE " . $imageType . "ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
	if ($resolution === 'original') {
		$name = $row['originalImageName'];
	} elseif ($resolution === 'large') {
		$name = $row['imageName'];
	} elseif ($resolution === 'thumbnail') {
		$name = $row['thumbnailName'];
	} else {
		$name = $row['imageName'];
	}
}
$stmt->close();

if (!$name) {
	http_response_code(404);
	exit('Image not found');
}
$imagePath = $imageDir . '/' . $name;
if (!file_exists($imagePath)) {
	http_response_code(404);
	exit('Image file not found');
}
$mimeType = mime_content_type($imagePath);
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($imagePath));
header('Content-Disposition: attachment; filename="' . basename($imagePath) . '"');
ob_clean();
flush();
readfile($imagePath);