<?php
include("../dbConnection.php");
include("../DataController.php");

header('Content-Type: application/json');

if (isset($_GET['id'])) {
	$songId = intval($_GET['id']);

	$songList = DataController::getSongList();

	$song = null;
	foreach ($songList as $s) {
		if ($s->getSongID() == $songId) {
			$song = $s;
			break;
		}
	}

	if ($song) {
		echo json_encode([
			'id' => $song->getSongID(),
			'title' => $song->getTitle(),
			'artists' => $song->getArtists(),
			'fileName' => $song->getfileName(),
			'imageName' => $song->getimageName()
		]);
	} else {
		http_response_code(404);
		echo json_encode(['error' => 'Song not found']);
	}
} else {
	http_response_code(400);
	echo json_encode(['error' => 'Song ID is required']);
}