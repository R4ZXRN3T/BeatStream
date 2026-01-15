<?php
/**
 * BeatStream media cleanup (simple)
 *
 * What it does:
 *  1) Reads filenames referenced in the database.
 *  2) Scans saved files on disk.
 *  3) ALWAYS warns about files that are referenced in the DB but missing on disk.
 *  4) Deletes files that exist on disk but aren't referenced in the DB.
 *     - With --dry-run: only prints what WOULD be deleted.
 *
 * Usage:
 *   php tools\cleanup_media.php --dry-run
 *   php tools\cleanup_media.php
 */

declare(strict_types=1);

$PROJECT_ROOT = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
if ($PROJECT_ROOT === false) {
	fwrite(STDERR, "Unable to resolve project root.\n");
	exit(2);
}

require_once $PROJECT_ROOT . DIRECTORY_SEPARATOR . 'dbConnection.php';

function relToAbs(string $projectRoot, string $relPath): string
{
	$relPath = str_replace('\\', '/', $relPath);
	$relPath = ltrim($relPath, '/');
	return $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
}

function absToRel(string $projectRoot, string $absPath): string
{
	$projectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/') . '/';
	$absPath = str_replace('\\', '/', $absPath);
	if (stripos($absPath, $projectRoot) !== 0) {
		return $absPath;
	}
	return ltrim(substr($absPath, strlen($projectRoot)), '/');
}

function isSafeDbFilename(string $name): bool
{
	// DB should store a bare filename, not a path.
	if ($name === '') {
		return false;
	}
	if (str_contains($name, '/') || str_contains($name, '\\')) {
		return false;
	}
	if (str_contains($name, '..')) {
		return false;
	}
	return true;
}

/** @return array{dryRun: bool} */
function parseArgs(array $argv): array
{
	$dryRun = false;
	foreach ($argv as $i => $arg) {
		if ($i === 0) {
			continue;
		}
		if ($arg === '--dry-run') {
			$dryRun = true;
			continue;
		}
		fwrite(STDERR, "Unknown argument: $arg\n");
		exit(64);
	}
	return ['dryRun' => $dryRun];
}

/**
 * @return array<string, array<int, string>> map relPath => list of sources (table.id.column)
 */
function getDbReferencedPaths(mysqli $conn): array
{
	$refs = [];

	/**
	 * @param string $table
	 * @param string $idCol
	 * @param array<int, array{col: string, dir: string}> $cols
	 */
	$collect = function (string $table, string $idCol, array $cols) use ($conn, &$refs): void {
		$selectCols = array_merge([$idCol], array_map(static fn($c) => $c['col'], $cols));
		$sql = 'SELECT ' . implode(', ', array_map(static fn($c) => "`$c`", $selectCols)) . " FROM `$table`";
		$res = $conn->query($sql);
		if ($res === false) {
			fwrite(STDERR, "DB query failed: $sql :: $conn->error\n");
			return;
		}
		while ($row = $res->fetch_assoc()) {
			$id = (string)($row[$idCol] ?? '');
			foreach ($cols as $def) {
				$col = $def['col'];
				$val = $row[$col] ?? null;
				if ($val === null) {
					continue;
				}
				$val = trim((string)$val);
				if ($val === '') {
					continue;
				}
				if (!isSafeDbFilename($val)) {
					fwrite(STDERR, "Invalid DB filename (skipping): $table.$id.$col=$val\n");
					continue;
				}
				$rel = rtrim($def['dir'], '/') . '/' . $val;
				$rel = ltrim($rel, '/');
				if (!isset($refs[$rel])) {
					$refs[$rel] = [];
				}
				$refs[$rel][] = "$table.$id.$col";
			}
		}
		$res->free();
	};

	// Based on random shit/tables.sql
	$collect('song', 'songID', [
		['col' => 'flacFileName', 'dir' => 'audio/flac'],
		['col' => 'opusFileName', 'dir' => 'audio/opus'],
		['col' => 'imageName', 'dir' => 'images/song/large'],
		['col' => 'thumbnailName', 'dir' => 'images/song/thumbnail'],
		['col' => 'originalImageName', 'dir' => 'images/song/original'],
	]);

	$collect('album', 'albumID', [
		['col' => 'imageName', 'dir' => 'images/album/large'],
		['col' => 'thumbnailName', 'dir' => 'images/album/thumbnail'],
		['col' => 'originalImageName', 'dir' => 'images/album/original'],
	]);

	$collect('artist', 'artistID', [
		['col' => 'imageName', 'dir' => 'images/artist/large'],
		['col' => 'thumbnailName', 'dir' => 'images/artist/thumbnail'],
	]);

	$collect('playlist', 'playlistID', [
		['col' => 'imageName', 'dir' => 'images/playlist/large'],
		['col' => 'thumbnailName', 'dir' => 'images/playlist/thumbnail'],
	]);

	$collect('user', 'userID', [
		['col' => 'imageName', 'dir' => 'images/user/large'],
		['col' => 'thumbnailName', 'dir' => 'images/user/thumbnail'],
	]);

	return $refs;
}

/**
 * @return array<string, bool> set of rel file paths under the configured media roots
 */
function scanMediaFiles(string $projectRoot): array
{
	$roots = [
		'audio/flac',
		'audio/opus',
		'images/song',
		'images/album',
		'images/artist',
		'images/playlist',
		'images/user',
	];

	$ignoreExact = [
		'favicon.ico' => true,
		'images/defaultAlbum.webp' => true,
		'images/defaultArtist.webp' => true,
		'images/defaultPlaylist.webp' => true,
		'images/defaultSong.webp' => true,
		'images/defaultUser.webp' => true,
		'images/flac_logo.webp' => true,
		'images/opus_logo.webp' => true,
		'images/logo_white.webp' => true,
	];

	$found = [];
	foreach ($roots as $rootRel) {
		$rootAbs = relToAbs($projectRoot, $rootRel);
		if (!is_dir($rootAbs)) {
			continue;
		}

		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootAbs, FilesystemIterator::SKIP_DOTS)
		);
		foreach ($it as $fileInfo) {
			/** @var SplFileInfo $fileInfo */
			if (!$fileInfo->isFile()) {
				continue;
			}

			$abs = $fileInfo->getPathname();
			$rel = str_replace('\\', '/', absToRel($projectRoot, $abs));

			if (isset($ignoreExact[$rel])) {
				continue;
			}
			if (basename($rel) === '! placeholder') {
				continue;
			}

			$found[$rel] = true;
		}
	}

	return $found;
}

$args = parseArgs($argv);
$dryRun = $args['dryRun'];

$conn = DBConn::getConn();
$conn->set_charset('utf8mb4');

$dbRefs = getDbReferencedPaths($conn);
$diskFiles = scanMediaFiles($PROJECT_ROOT);

// 1) Always warn about missing referenced files
$missingCount = 0;
foreach ($dbRefs as $rel => $sources) {
	$abs = relToAbs($PROJECT_ROOT, $rel);
	if (!is_file($abs)) {
		$missingCount++;
		fwrite(STDERR, "MISSING: $rel (referenced by " . implode(', ', $sources) . ")\n");
	}
}

// 2) Orphans: exist on disk but not referenced
$orphans = [];
foreach ($diskFiles as $rel => $_) if (!isset($dbRefs[$rel])) $orphans[] = $rel;

sort($orphans);

if ($dryRun) {
	fwrite(STDOUT, "Dry-run: these files would be deleted (" . count($orphans) . "):\n");
	foreach ($orphans as $rel) fwrite(STDOUT, "  $rel\n");
} else {
	$deleted = 0;
	$failed = 0;
	foreach ($orphans as $rel) {
		$abs = relToAbs($PROJECT_ROOT, $rel);
		if (!is_file($abs)) continue;
		if (@unlink($abs)) {
			$deleted++;
			fwrite(STDOUT, "DELETED: $rel\n");
		} else {
			$failed++;
			fwrite(STDERR, "FAILED TO DELETE: $rel\n");
		}
	}
	fwrite(STDOUT, "Deleted $deleted file(s). Failures: $failed.\n");
}

// Exit non-zero if we have missing refs or delete failures
if ($missingCount > 0) {
	exit(2);
}

exit(0);

