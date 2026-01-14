<?php
/**
 * BeatStream bulk importer (CLI)
 *
 * Usage:
 *   php tools/bulk_import.php --root "D:\Music\ToImport" --dry-run
 *   php tools/bulk_import.php --root "D:\Music\ToImport"
 */

declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

if (PHP_SAPI !== 'cli') {
	http_response_code(400);
	echo "This script must be run from the command line.\n";
	exit(1);
}

// Bootstrap
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..');
require_once $_SERVER['DOCUMENT_ROOT'] . '/globals.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/vendor/autoload.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/dbConnection.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/converter.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/Objects/Song.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/Objects/Album.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/Objects/Artist.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/SongController.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/AlbumController.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/ArtistController.php';

// -------- tiny helpers --------

#[NoReturn]
function usageAndExit(int $code = 0): void
{
	echo "BeatStream bulk importer\n\nOptions:\n";
	echo "  --root <path>         Root folder containing album folders (required)\n";
	echo "  --dry-run             Only print what would be imported\n";
	echo "  --accept-lossy        Allow lossy formats (mp3/aac/...) (default: NO)\n";
	echo "  --skip-existing        Skip if an album with same title+artist+date exists (default: YES)\n";
	echo "  --no-skip-existing     Import even if duplicates are found (default: NO)\n";
	echo "  --limit <n>            Max number of album folders to process\n\n";
	exit($code);
}

function argValue(array $argv, string $name): ?string
{
	$i = array_search($name, $argv, true);
	return $i === false ? null : ($argv[$i + 1] ?? null);
}

function hasFlag(array $argv, string $name): bool
{
	return in_array($name, $argv, true);
}

function normArtist(string $s): string
{
	return preg_replace('/\s+/', ' ', trim($s));
}

function parseYearToDateString(?string $in): string
{
	if (!$in) return (new DateTime())->format('Y-m-d');
	$in = str_replace(['.', '/'], '-', trim($in));
	if (preg_match('/^\d{4}$/', $in)) return $in . '-01-01';
	if (preg_match('/^\d{4}-\d{2}$/', $in)) return $in . '-01';
	return preg_match('/^\d{4}-\d{2}-\d{2}$/', $in) ? $in : (new DateTime())->format('Y-m-d');
}

function normalizeArgvForWindowsRoot(array $argv): array
{
	$i = array_search('--root', $argv, true);
	if ($i === false || !isset($argv[$i + 1])) return $argv;
	$parts = [];
	for ($j = $i + 1; $j < count($argv); $j++) {
		if (str_starts_with((string)$argv[$j], '--')) break;
		$parts[] = (string)$argv[$j];
	}
	if (count($parts) <= 1) return $argv;
	$argv[$i + 1] = implode(' ', $parts);
	array_splice($argv, $i + 2, count($parts) - 1);
	return $argv;
}

function createSafeTempCopy(string $src, ?string $forcedExt = null): string
{
	$ext = $forcedExt ?? strtolower((string)pathinfo($src, PATHINFO_EXTENSION));
	$ext = $ext !== '' ? ('.' . $ext) : '';
	$tmp = tempnam(sys_get_temp_dir(), 'bsimp_');
	if ($tmp === false) throw new RuntimeException('Failed to create temp file in: ' . sys_get_temp_dir());
	$dst = $tmp . $ext;
	@unlink($tmp);
	if (!copy($src, $dst)) throw new RuntimeException('Failed to copy to temp file: ' . $src);
	return $dst;
}

function uploadArrayFromPath(string $path, bool $safe = true): array
{
	$tmp = $safe ? createSafeTempCopy($path) : $path;
	$mime = (new finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'application/octet-stream';
	return ['name' => basename($path), 'type' => $mime, 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => filesize($path)];
}

function cleanupTempUpload(array $upload, string $originalPath): void
{
	$tmp = $upload['tmp_name'] ?? null;
	if (is_string($tmp) && $tmp !== $originalPath && file_exists($tmp)) @unlink($tmp);
}

function withUpload(string $path, callable $fn)
{
	$u = uploadArrayFromPath($path);
	try {
		return $fn($u);
	} finally {
		cleanupTempUpload($u, $path);
	}
}

function ensureCliPrereqs(): void
{
	$ffmpeg = trim((string)shell_exec('ffmpeg -version 2>&1'));
	$ffprobe = trim((string)shell_exec('ffprobe -version 2>&1'));
	if ($ffmpeg === '' || stripos($ffmpeg, 'ffmpeg version') === false) throw new RuntimeException('ffmpeg not found in PATH. Install ffmpeg and ensure it is available in PATH.');
	if ($ffprobe === '' || stripos($ffprobe, 'ffprobe version') === false) throw new RuntimeException('ffprobe not found in PATH. Install ffmpeg (includes ffprobe) and ensure it is available in PATH.');
	if (!extension_loaded('imagick')) throw new RuntimeException('PHP imagick extension is not enabled; album cover import requires it.');
}

/** @return array{comments: array<string, list<string>>} */
function analyzeAudioTags(getID3 $id3, string $path): array
{
	$info = $id3->analyze($path);
	getid3_lib::CopyTagsToComments($info);
	return ['comments' => $info['comments'] ?? []];
}

function pickTag(array $tags, array $keys): ?string
{
	foreach ($keys as $k) {
		if (!empty($tags[$k][0])) return (string)$tags[$k][0];
	}
	return null;
}

function extractTrackNumber(array $tags): int
{
	foreach (['track_number', 'tracknumber', 'track'] as $k) {
		if (!empty($tags[$k][0]) && preg_match('/^(\d+)/', (string)$tags[$k][0], $m)) return (int)$m[1];
	}
	return 0;
}

function listAlbumFolders(string $root): array
{
	$e = scandir($root);
	if ($e === false) return [];
	$out = [];
	foreach ($e as $x) {
		if ($x !== '.' && $x !== '..' && is_dir($root . DIRECTORY_SEPARATOR . $x)) $out[] = $root . DIRECTORY_SEPARATOR . $x;
	}
	sort($out, SORT_NATURAL | SORT_FLAG_CASE);
	return $out;
}

function findAudioFiles(string $dir, bool $acceptLossy): array
{
	$allowed = [
		'wav', 'flac', 'ape', 'wv', 'tta', 'aiff', 'aif', 'au', 'snd', 'caf', 'w64', 'rf64', 'bwf', 'tak', 'als',
		'mp3', 'aac', 'm4a', 'ogg', 'oga', 'opus', 'wma', 'ac3', 'eac3', 'dts', 'amr', 'awb', 'gsm', 'qcp', 'evrc',
		'mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm', 'ogv', '3gp', '3g2', 'asf', 'vob', 'ts', 'mts', 'm2ts',
		'ra', 'rm', 'shn', 'mlp', 'truehd', 'atrac', 'vqf', 'spx', 'mka', 'mpc', 'mp2', 'mp1', 'mpga'
	];
	$lossless = ['wav', 'flac', 'caf', 'aiff', 'aif', 'ape', 'wv', 'tta', 'shn', 'pcm', 'au', 'snd', 'w64', 'rf64', 'bwf', 'tak', 'als'];

	$all = scandir($dir);
	if ($all === false) return [];
	$files = [];
	foreach ($all as $f) {
		if ($f === '.' || $f === '..') continue;
		if (strtolower($f) === '00 - cover.png') continue;
		$p = $dir . DIRECTORY_SEPARATOR . $f;
		if (!is_file($p)) continue;
		$ext = strtolower((string)pathinfo($f, PATHINFO_EXTENSION));
		if ($ext === '' || !in_array($ext, $allowed, true)) continue;
		if (!$acceptLossy && !in_array($ext, $lossless, true)) continue;
		$files[] = $p;
	}
	return $files;
}

function getExistingArtistsByName(array $names): array
{
	$names = array_values(array_unique(array_filter(array_map('normArtist', $names), static fn($x) => $x !== '')));
	if (!$names) return [];
	$ph = implode(',', array_fill(0, count($names), '?'));
	$types = str_repeat('s', count($names));
	$stmt = DBConn::getConn()->prepare("SELECT artistID, name FROM artist WHERE name IN ($ph)");
	$stmt->bind_param($types, ...$names);
	$stmt->execute();
	$res = $stmt->get_result();
	$out = [];
	while ($r = $res->fetch_assoc()) $out[normArtist($r['name'])] = (int)$r['artistID'];
	$stmt->close();
	return $out;
}

function getAlbumIdByIdentity(string $title, int $artistId, string $releaseDate): ?int
{
	$stmt = DBConn::getConn()->prepare('
  SELECT a.albumID
  FROM album a
  JOIN releases_album ra ON ra.albumID = a.albumID
  WHERE a.title = ? AND a.releaseDate = ? AND ra.artistID = ? AND ra.artistIndex = 0
  LIMIT 1
 ');
	$stmt->bind_param('ssi', $title, $releaseDate, $artistId);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	return $row ? (int)$row['albumID'] : null;
}

function getExistingSongIdByTitleAndArtist(string $title, int $artistId): ?int
{
	$title = trim($title);
	$stmt = DBConn::getConn()->prepare('
		SELECT s.songID
		FROM song s
		JOIN releases_song rs ON rs.songID = s.songID
		WHERE s.title = ? AND rs.artistID = ? AND rs.artistIndex = 0
		LIMIT 1
	');
	$stmt->bind_param('si', $title, $artistId);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	return $row ? (int)$row['songID'] : null;
}

function getSongLengthById(int $songId): ?int
{
	$stmt = DBConn::getConn()->prepare('SELECT songLength FROM song WHERE songID = ? LIMIT 1');
	$stmt->bind_param('i', $songId);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	return $row ? (int)$row['songLength'] : null;
}

// Move these helper functions above the "// -------- main --------" section.

function getCommaSeparatedArtistsFromTags(array $tags, array $keys, string $fallbackSingle): array
{
	$rawValues = [];
	foreach ($keys as $k) {
		if (!empty($tags[$k]) && is_array($tags[$k])) {
			$rawValues = array_merge($rawValues, $tags[$k]);
		}
	}
	if (empty($rawValues)) {
		$fb = normArtist($fallbackSingle);
		return ($fb !== '' && strcasecmp($fb, 'Unknown Artist') !== 0) ? [$fb] : [];
	}
	// Split only on commas as requested.
	$out = [];
	foreach ($rawValues as $v) {
		if (!is_string($v)) continue;
		foreach (explode(',', $v) as $part) {
			$name = normArtist($part);
			if ($name !== '' && strcasecmp($name, 'Unknown Artist') !== 0) $out[] = $name;
		}
	}
	// De-dupe preserve order
	$seen = [];
	$uniq = [];
	foreach ($out as $n) {
		$k = strtolower($n);
		if (isset($seen[$k])) continue;
		$seen[$k] = true;
		$uniq[] = $n;
	}
	return $uniq;
}

// -------- main --------

$argv = normalizeArgvForWindowsRoot($_SERVER['argv'] ?? []);
$root = trim((string)argValue($argv, '--root'));
if ($root === '') usageAndExit(1);
$root = trim($root, "\"'");
$rp = realpath($root);
$root = $rp !== false ? $rp : $root;

if (!is_dir($root)) {
	echo "Root folder does not exist (as seen by PHP): \n";
	echo "  raw:      " . (argValue($_SERVER['argv'] ?? [], '--root') ?? '') . "\n";
	echo "  parsed:   $root\n";
	echo "  cwd:      " . getcwd() . "\n";
	exit(1);
}

$dryRun = hasFlag($argv, '--dry-run');
$acceptLossy = hasFlag($argv, '--accept-lossy');
$skipExisting = !hasFlag($argv, '--no-skip-existing');
$limitN = ($v = argValue($argv, '--limit')) ? max(1, (int)$v) : null;

try {
	ensureCliPrereqs();
} catch (Throwable $e) {
	echo "Prereq check failed: " . $e->getMessage() . "\n";
	exit(1);
}

$id3 = new getID3();
$albumDirs = listAlbumFolders($root);
if ($limitN !== null) $albumDirs = array_slice($albumDirs, 0, $limitN);
echo "Found " . count($albumDirs) . " album folders under: $root\n";

// Pass 1: artists
$need = [];
foreach ($albumDirs as $dir) {
	$cover = $dir . DIRECTORY_SEPARATOR . '00 - cover.png';
	if (!file_exists($cover)) continue;
	$audio = findAudioFiles($dir, $acceptLossy);
	if (!$audio) continue;

	foreach ($audio as $p) {
		$tags = analyzeAudioTags($id3, $p)['comments'];
		$artists = getCommaSeparatedArtistsFromTags($tags, ['artist'], 'Unknown Artist');
		$albumArtists = getCommaSeparatedArtistsFromTags($tags, ['albumartist'], $artists[0] ?? 'Unknown Artist');
		$need = array_merge($need, $artists, $albumArtists);
	}
}

$need = array_values(array_unique(array_filter($need, static fn($a) => $a !== '' && strcasecmp($a, 'Unknown Artist') !== 0)));
$artistMap = getExistingArtistsByName($need);

$missing = [];
foreach ($need as $n) if (!isset($artistMap[$n])) $missing[] = $n;

if ($missing) {
	sort($missing, SORT_NATURAL | SORT_FLAG_CASE);
	echo "\nMissing artists in DB (create these first, then re-run importer):\n";
	foreach ($missing as $n) echo "  - $n\n";
	echo "\nAborting: importer does not auto-create artists.\n";
	exit(2);
}

echo "\nAll required artists exist (" . count($artistMap) . "). Proceeding...\n";

$processed = $createdAlbums = $createdSongs = $skipped = $errors = 0;

foreach ($albumDirs as $dir) {
	$processed++;
	$folder = basename($dir);
	$cover = $dir . DIRECTORY_SEPARATOR . '00 - cover.png';
	echo "\n[$processed/" . count($albumDirs) . "] $folder\n";

	if (!file_exists($cover)) {
		echo "  SKIP: Missing cover: $cover\n";
		$skipped++;
		continue;
	}
	$audio = findAudioFiles($dir, $acceptLossy);
	if (!$audio) {
		echo "  SKIP: No audio files found\n";
		$skipped++;
		continue;
	}

	$tracks = [];
	foreach ($audio as $p) {
		$tags = analyzeAudioTags($id3, $p)['comments'];
		$title = pickTag($tags, ['title']) ?? pathinfo($p, PATHINFO_FILENAME);

		$artists = getCommaSeparatedArtistsFromTags($tags, ['artist'], 'Unknown Artist');
		$albumArtists = getCommaSeparatedArtistsFromTags($tags, ['albumartist'], $artists[0] ?? 'Unknown Artist');
		$albumTitle = pickTag($tags, ['album']) ?? $folder;
		$genre = pickTag($tags, ['genre']) ?? 'Unknown';
		$date = pickTag($tags, ['date', 'year']);

		$tracks[] = [
			'path' => $p,
			'title' => trim((string)$title),
			'artists' => $artists,
			'album' => trim($albumTitle),
			'album_artists' => $albumArtists,
			'genre' => trim($genre),
			'release_date' => parseYearToDateString($date),
			'track_no' => extractTrackNumber($tags),
		];
	}

	usort($tracks, static fn($a, $b) => (($a['track_no'] ?: 9999) <=> ($b['track_no'] ?: 9999)) ?: strcmp($a['path'], $b['path']));

	$albumTitle = $tracks[0]['album'] ?? $folder;

	// Aggregate album artists across all tracks (union, keep order)
	$albumArtistNames = [];
	$seenAlbum = [];
	foreach ($tracks as $t) {
		foreach (($t['album_artists'] ?? []) as $an) {
			$an = normArtist((string)$an);
			if ($an === '' || strcasecmp($an, 'Unknown Artist') === 0) continue;
			$key = strtolower($an);
			if (isset($seenAlbum[$key])) continue;
			$seenAlbum[$key] = true;
			$albumArtistNames[] = $an;
		}
	}
	if (empty($albumArtistNames) && !empty($tracks[0]['artists'][0])) {
		$albumArtistNames = [$tracks[0]['artists'][0]];
	}

	$albumArtistName = $albumArtistNames[0] ?? 'Unknown Artist';
	$albumDate = $tracks[0]['release_date'] ?? (new DateTime())->format('Y-m-d');

	$albumArtistIds = [];
	foreach ($albumArtistNames as $an) {
		$id = $artistMap[normArtist($an)] ?? null;
		if ($id === null) {
			echo "  ERROR: missing album artist (unexpected): $an\n";
			$errors++;
			continue 2;
		}
		$albumArtistIds[] = $id;
	}

	$albumArtistId = $albumArtistIds[0] ?? null;

	if ($albumArtistId === null) {
		echo "  ERROR: missing album artist (unexpected): $albumArtistName\n";
		$errors++;
		continue;
	}

	if ($skipExisting && !$dryRun) {
		if (($id = getAlbumIdByIdentity($albumTitle, $albumArtistId, $albumDate)) !== null) {
			echo "  SKIP: Album already exists (#$id)\n";
			$skipped++;
			continue;
		}
	}

	if ($dryRun) {
		echo "  DRY-RUN: Would import album '$albumTitle' by '$albumArtistName' ($albumDate) with " . count($tracks) . " tracks\n";
		foreach ($tracks as $t) echo "    - [" . ($t['track_no'] ?: '-') . "] " . $t['title'] . ' — ' . implode(', ', $t['artists']) . "\n";
		continue;
	}

	$conn = DBConn::getConn();
	$conn->begin_transaction();
	$createdFiles = ['images' => [], 'audio' => []];

	try {
		$albumImg = withUpload($cover, static fn($u) => Converter::uploadImage($u, ImageType::ALBUM));
		if (empty($albumImg['success'])) throw new RuntimeException('Cover conversion failed: ' . ($albumImg['error'] ?? 'unknown'));

		$createdFiles['images'] = array_merge($createdFiles['images'], [
			$GLOBALS['PROJECT_ROOT_DIR'] . '/images/album/large/' . $albumImg['large_filename'],
			$GLOBALS['PROJECT_ROOT_DIR'] . '/images/album/thumbnail/' . $albumImg['thumbnail_filename'],
			$GLOBALS['PROJECT_ROOT_DIR'] . '/images/album/original/' . $albumImg['original_filename'],
		]);

		$songIds = [];
		$totalDuration = 0;

		foreach ($tracks as $t) {
			if (empty($t['artists'])) throw new RuntimeException('Missing song artist tags for: ' . $t['title']);

			$primaryArtistName = $t['artists'][0];
			$primaryArtistId = $artistMap[normArtist($primaryArtistName)] ?? null;
			if ($primaryArtistId === null) throw new RuntimeException('Missing song artist (unexpected): ' . $primaryArtistName);

			$artistIds = [];
			foreach ($t['artists'] as $an) {
				$id = $artistMap[normArtist($an)] ?? null;
				if ($id === null) throw new RuntimeException('Missing song artist (unexpected): ' . $an);
				$artistIds[] = $id;
			}

			// De-dup: reuse existing song by title + primary artist
			$existingSongId = getExistingSongIdByTitleAndArtist($t['title'], $primaryArtistId);
			if ($existingSongId !== null) {
				$songIds[] = $existingSongId;
				$len = getSongLengthById($existingSongId);
				if ($len !== null) $totalDuration += $len;
				continue;
			}

			// Create new song (cover + audio)
			$songImg = withUpload($cover, static fn($u) => Converter::uploadImage($u, ImageType::SONG));
			if (empty($songImg['success'])) throw new RuntimeException('Song cover conversion failed: ' . ($songImg['error'] ?? 'unknown'));

			$createdFiles['images'] = array_merge($createdFiles['images'], [
				$GLOBALS['PROJECT_ROOT_DIR'] . '/images/song/large/' . $songImg['large_filename'],
				$GLOBALS['PROJECT_ROOT_DIR'] . '/images/song/thumbnail/' . $songImg['thumbnail_filename'],
				$GLOBALS['PROJECT_ROOT_DIR'] . '/images/song/original/' . $songImg['original_filename'],
			]);

			$aud = withUpload($t['path'], static fn($u) => Converter::uploadAudio($u));
			if (empty($aud['success'])) throw new RuntimeException('Audio conversion failed for ' . basename($t['path']) . ': ' . ($aud['error'] ?? 'unknown'));

			$createdFiles['audio'] = array_merge($createdFiles['audio'], [
				$GLOBALS['PROJECT_ROOT_DIR'] . '/audio/flac/' . $aud['flac_filename'],
				$GLOBALS['PROJECT_ROOT_DIR'] . '/audio/opus/' . $aud['opus_filename'],
			]);

			$totalDuration += (int)$aud['duration'];

			SongController::insertSong(new Song(
				0,
				$t['title'],
				$t['artists'],
				$artistIds,
				$t['genre'],
				$t['release_date'],
				(int)$aud['duration'],
				$aud['flac_filename'],
				$aud['opus_filename'],
				$songImg['large_filename'],
				$songImg['thumbnail_filename'],
				$songImg['original_filename']
			));

			$stmt = $conn->prepare('SELECT songID FROM song WHERE flacFilename = ? LIMIT 1');
			$stmt->bind_param('s', $aud['flac_filename']);
			$stmt->execute();
			$row = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			if (!$row) throw new RuntimeException('Failed to find newly inserted song in DB for FLAC ' . $aud['flac_filename']);

			$songIds[] = (int)$row['songID'];
			$createdSongs++;
		}

		AlbumController::insertAlbum(new Album(
			0,
			$albumTitle,
			$songIds,
			$albumArtistNames,
			$albumArtistIds,
			$albumImg['large_filename'],
			$albumImg['thumbnail_filename'],
			count($songIds),
			$totalDuration,
			$albumDate,
			count($songIds) === 1,
			$albumImg['original_filename']
		));
		$createdAlbums++;

		$conn->commit();
		echo "  OK: Imported album '$albumTitle' (tracks: " . count($songIds) . ")\n";
	} catch (Throwable $e) {
		$conn->rollback();
		$errors++;
		foreach (array_merge($createdFiles['images'], $createdFiles['audio']) as $p) if (is_string($p) && file_exists($p)) @unlink($p);
		echo "  ERROR: " . $e->getMessage() . "\n";
	}
}

echo "\nDone. Albums created: $createdAlbums, songs created: $createdSongs, skipped: $skipped, errors: $errors\n";