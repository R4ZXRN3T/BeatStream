<?php
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}
@session_write_close();

require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php";

$songID = $_GET['id'] ?? null;
if (!$songID) {
	http_response_code(400);
	exit('Missing song ID');
}

$song = SongController::getSongByID((int)$songID);
if (!$song) {
	http_response_code(404);
	exit('Song not found');
}

$flacPath = $GLOBALS['PROJECT_ROOT_DIR'] . "/audio/flac/" . $song->getFlacFileName();
$originalImagePath = $GLOBALS['PROJECT_ROOT_DIR'] . "/images/song/original/" . $song->getOriginalImageName();
$tempImagePath = sys_get_temp_dir() . '/' . uniqid('cover_') . '.jpg';
$tempFlacPath = sys_get_temp_dir() . '/' . uniqid('song_') . '.flac';

if (!file_exists($flacPath) || !file_exists($originalImagePath)) {
	http_response_code(404);
	exit('Required files not found');
}

// Convert image to 640x640 JPEG
try {
	$imagick = new Imagick($originalImagePath);
	$imagick->resizeImage(640, 640, Imagick::FILTER_LANCZOS, 1, true);
	$imagick->setImageFormat('jpeg');
	$imagick->setImageCompressionQuality(90);
	$imagick->writeImage($tempImagePath);
	$imagick->clear();
} catch (Exception $e) {
	http_response_code(500);
	echo $e->getMessage();
	exit('Image conversion failed');
}

// Copy FLAC to temp for editing
copy($flacPath, $tempFlacPath);

// Prepare metadata
$title = $song->getTitle();
$artist = implode(', ', $song->getArtists());
$genre = $song->getGenre();
$date = $song->getReleaseDate()->format('Y');
$albumResult = SongController::getSongAlbum($songID);
$album = $albumResult["album"];
$albumTitle = $album ? $album->getName() : 'Unknown Album';
$albumArtists = $album ? implode(', ', $album->getArtists()) : $artist;
$digits = max(2, strlen((string)$album->getLength()));
$totalTracks = str_pad((string)$album->getLength(), $digits, '0', STR_PAD_LEFT);
$trackNumber = str_pad((string)($albumResult['index'] + 1), $digits, '0', STR_PAD_LEFT);


// Use ffmpeg to embed image and metadata
$outputFlacPath = sys_get_temp_dir() . '/' . uniqid('out_') . '.flac';
$ffmpegCmd = sprintf(
	'ffmpeg -y -i %s -i %s -map 0 -map 1 -c copy -metadata TITLE=%s -metadata ARTIST=%s -metadata ALBUM=%s -metadata ALBUMARTIST=%s -metadata TRACK=%s -metadata TRACKTOTAL=%s -metadata GENRE=%s -metadata DATE=%s -metadata:s:v title="Cover" -metadata:s:v comment="Cover (front)" -disposition:v:0 attached_pic %s 2>&1',
	escapeshellarg($tempFlacPath),
	escapeshellarg($tempImagePath),
	escapeshellarg($title),
	escapeshellarg($artist),
	escapeshellarg($albumTitle),
	escapeshellarg($albumArtists),
	escapeshellarg($trackNumber),
	escapeshellarg($totalTracks),
	escapeshellarg($genre),
	escapeshellarg($date),
	escapeshellarg($outputFlacPath)
);
exec($ffmpegCmd, $output, $returnCode);

if ($returnCode !== 0 || !file_exists($outputFlacPath)) {
	unlink($tempImagePath);
	unlink($tempFlacPath);
	if (file_exists($outputFlacPath)) unlink($outputFlacPath);
	http_response_code(500);
	exit('Failed to embed metadata');
}

// Send the FLAC file as download
header('Content-Type: audio/flac');
$forbidden = '/[\/:*?"<>|]/';
$filename = preg_replace($forbidden, '', $artist . ' - ' . $title) . '.flac';
header('Content-Disposition: attachment; filename="' . $filename . '"');
readfile($outputFlacPath);

// Clean up
unlink($tempImagePath);
unlink($tempFlacPath);
unlink($outputFlacPath);