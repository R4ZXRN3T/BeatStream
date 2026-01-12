<?php
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}
@session_write_close();
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/AlbumController.php";
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php";

$albumID = $_GET['id'] ?? null;
if (!$albumID) {
	http_response_code(400);
	exit('Missing album ID');
}

$album = AlbumController::getAlbumByID((int)$albumID);
if (!$album) {
	http_response_code(404);
	exit('Album not found');
}

$songIDs = $album->getSongIDs();
if (empty($songIDs)) {
	http_response_code(404);
	exit('No songs found for album');
}

$originalCoverPath = $GLOBALS['PROJECT_ROOT_DIR'] . "/images/album/original/" . $album->getOriginalImageName();
if (!file_exists($originalCoverPath)) {
	http_response_code(404);
	exit('Original album cover not found');
}

// Create temp workspace
$workDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . uniqid('alb_', true);
$tracksDir = $workDir . DIRECTORY_SEPARATOR . 'tracks';
@mkdir($workDir, 0777, true);
@mkdir($tracksDir, 0777, true);

// Helper to cleanup temp directory recursively
$cleanup = function () use ($workDir) {
	if (!is_dir($workDir)) return;
	$it = new RecursiveDirectoryIterator($workDir, FilesystemIterator::SKIP_DOTS);
	$ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($ri as $file) {
		if ($file->isDir()) {
			@rmdir($file->getPathname());
		} else {
			@unlink($file->getPathname());
		}
	}
	@rmdir($workDir);
};

// Helper to flush output chunks for progress tracking
function flushChunk(): void
{
	if (ob_get_level() > 0) {
		@ob_flush();
	}
	@flush();
}

// Build scaled cover (640x640 JPEG) for embedding
$scaledCoverPath = $workDir . DIRECTORY_SEPARATOR . 'cover_640.jpg';
try {
	$imagick = new Imagick($originalCoverPath);
	$imagick->resizeImage(640, 640, Imagick::FILTER_LANCZOS, 1, true);
	$imagick->setImageFormat('jpeg');
	$imagick->setImageCompressionQuality(90);
	$imagick->writeImage($scaledCoverPath);
	$imagick->clear();
} catch (Exception $e) {
	$cleanup();
	http_response_code(500);
	echo $e->getMessage();
	exit('Cover scaling failed');
}

// Convert original cover to PNG as "00 - cover.png"
$pngCoverName = "00 - cover.png";
$pngCoverPath = $workDir . DIRECTORY_SEPARATOR . $pngCoverName;
copy($originalCoverPath, $pngCoverPath);

$songList = SongController::getAlbumSongs($album->getAlbumID());
// Prepare album metadata
$albumTitle = $album->getName();
$albumArtistsStr = implode(', ', $album->getArtists());
$albumDate = $album->getReleaseDate()->format('Y');

// Decide padding width (at least 2, grows if album has >= 100 tracks)
$digits = max(2, strlen((string)count($songIDs)));
$totalTracks = str_pad((string)count($songIDs), $digits, '0', STR_PAD_LEFT);

// Process each song: embed cover + album metadata, export to tracksDir
$processedFiles = [];
for ($i = 0; $i < count($songIDs); $i++) {
	$trackNo = $i + 1;
	$songID = (int)$songIDs[$i];

	$song = $songList[$i];

	$flacPath = $GLOBALS['PROJECT_ROOT_DIR'] . "/audio/flac/" . $song->getFlacFileName();
	if (!file_exists($flacPath)) {
		$cleanup();
		http_response_code(404);
		exit("Audio file missing for song: $songID");
	}

	// Prepare temp copies and output path
	$tmpIn = $workDir . DIRECTORY_SEPARATOR . uniqid('in_', true) . ".flac";
	$tmpOut = $workDir . DIRECTORY_SEPARATOR . uniqid('out_', true) . ".flac";
	if (!copy($flacPath, $tmpIn)) {
		$cleanup();
		http_response_code(500);
		exit("Failed to stage audio for song: $songID");
	}

	// Build metadata
	$title = $song->getTitle();
	$trackArtists = implode(', ', $song->getArtists());
	$trackNumber = str_pad((string)$trackNo, $digits, '0', STR_PAD_LEFT);
	$genre = $song->getGenre();

	// ffmpeg command to attach cover and write metadata while copying streams
	$cmd = sprintf(
		'ffmpeg -y -i %s -i %s -map 0 -map 1 -c copy ' .
		'-metadata TITLE=%s ' .
		'-metadata ARTIST=%s ' .
		'-metadata ALBUM=%s ' .
		'-metadata ALBUMARTIST=%s ' .
		'-metadata GENRE=%s ' .
		'-metadata TRACK=%s ' .
		'-metadata TRACKTOTAL=%s ' .
		'-metadata DATE=%s ' .
		'-metadata:s:v title="Cover" -metadata:s:v comment="Cover (front)" ' .
		'-disposition:v:0 attached_pic %s 2>&1',
		escapeshellarg($tmpIn),
		escapeshellarg($scaledCoverPath),
		escapeshellarg($title),
		escapeshellarg($trackArtists),
		escapeshellarg($albumTitle),
		escapeshellarg($albumArtistsStr),
		escapeshellarg($genre),
		escapeshellarg($trackNumber),
		escapeshellarg($totalTracks),
		escapeshellarg($albumDate),
		escapeshellarg($tmpOut)
	);
	exec($cmd, $outLines, $rc);
	@unlink($tmpIn);
	if ($rc !== 0 || !file_exists($tmpOut)) {
		@unlink($tmpOut);
		$cleanup();
		http_response_code(500);
		exit('Failed to embed metadata for one or more tracks');
	}

	// Final name: "%TRACK% - %TITLE%.flac" (track padded to 2 digits)
	$forbidden = '/[\/:*?"<>|]/';
	$safeTitle = preg_replace($forbidden, '', $title);
	$finalName = sprintf('%02d - %s.flac', $trackNo, $safeTitle);
	$finalPath = $tracksDir . DIRECTORY_SEPARATOR . $finalName;

	if (!rename($tmpOut, $finalPath)) {
		@unlink($tmpOut);
		$cleanup();
		http_response_code(500);
		exit('Failed to finalize track file');
	}

	$processedFiles[] = $finalPath;
}

// Build zip
$zipName = preg_replace('/[\/:*?"<>|]/', '', $albumArtistsStr . ' - ' . $albumTitle) . '.zip';
$zipPath = $workDir . DIRECTORY_SEPARATOR . $zipName;

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
	$cleanup();
	http_response_code(500);
	exit('Failed to create zip');
}

// Add cover as "00 - cover.png" at root (STORE, no recompression)
$zip->addFile($pngCoverPath, $pngCoverName);
$zip->setCompressionName($pngCoverName, ZipArchive::CM_STORE);

// Add tracks (STORE as you add)
foreach ($processedFiles as $filePath) {
	$name = basename($filePath);
	$zip->addFile($filePath, $name);
	$zip->setCompressionName($name, ZipArchive::CM_STORE);
}

$zip->close();

// Prepare clean, uncompressed binary response
if (function_exists('ini_set')) {
	@ini_set('zlib.output_compression', '0');
	@ini_set('output_buffering', '0');
	@ini_set('implicit_flush', '1');
}
while (ob_get_level() > 0) {
	@ob_end_clean();
}
@header_remove('Content-Encoding');

// Send zip with chunked output for progress
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
$size = filesize($zipPath);
if ($size !== false) {
	header('Content-Length: ' . $size);
}

readfile($zipPath);

// Cleanup
@unlink($zipPath);
$cleanup();
exit;