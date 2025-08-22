<?php

class Converter
{
	private static int $opusBitrate = 160;
	private static int $flacCompressionLevel = 8;
	private static string $ffmpegPath = 'C:\\Tools\\ffmpeg\\ffmpeg.exe';
	private static string $ffprobePath = 'C:\\Tools\\ffmpeg\\ffprobe.exe';

	public static function uploadAudio($file): array
	{
		$flacUploadDir = $_SERVER["DOCUMENT_ROOT"] . "/BeatStream/audio/flac/";
		$opusUploadDir = $_SERVER["DOCUMENT_ROOT"] . "/BeatStream/audio/opus/";

		if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
			return ['success' => false, 'error' => 'No audio file provided or upload error'];
		}

		$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
		$allowedExtensions = [
			'wav', 'flac', 'ape', 'wv', 'tta', 'aiff', 'aif', 'au', 'snd', 'caf', 'w64', 'rf64', 'bwf', 'tak', 'als',
			'mp3', 'aac', 'm4a', 'ogg', 'oga', 'opus', 'wma', 'ac3', 'eac3', 'dts', 'amr', 'awb', 'gsm', 'qcp', 'evrc',
			'mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm', 'ogv', '3gp', '3g2', 'asf', 'vob', 'ts', 'mts', 'm2ts',
			'ra', 'rm', 'shn', 'mlp', 'truehd', 'atrac', 'vqf', 'spx', 'mka', 'mpc', 'mp2', 'mp1', 'mpga'
		];

		if (!in_array(strtolower($extension), $allowedExtensions)) {
			return ['success' => false, 'error' => 'Invalid audio file format'];
		}

		$losslessFormats = ['wav', 'flac', 'caf', 'aiff', 'aif', 'ape', 'wv', 'tta', 'shn', 'pcm', 'au', 'snd', 'w64', 'rf64', 'bwf', 'tak', 'als'];
		$isLossy = !in_array(strtolower($extension), $losslessFormats);
		$warning = $isLossy ? 'Warning: You are uploading a lossy audio format (' . strtoupper($extension) . '). For best quality, consider using lossless formats like FLAC or WAV.' : null;

		if (!is_dir($flacUploadDir)) {
			mkdir($flacUploadDir, 0777, true);
		}
		if (!is_dir($opusUploadDir)) {
			mkdir($opusUploadDir, 0777, true);
		}

		$uniqueId = uniqid();
		$flacFileName = $uniqueId . '.flac';
		$opusFileName = $uniqueId . '.opus';
		$flacPath = $flacUploadDir . $flacFileName;
		$opusPath = $opusUploadDir . $opusFileName;

		try {
			// Get duration using ffprobe
			$durationCmd = sprintf(
				'"%s" -v quiet -print_format csv=p=0 -show_entries format=duration "%s"',
				self::$ffprobePath,
				escapeshellarg($file['tmp_name'])
			);
			$duration = trim(shell_exec($durationCmd));

			// Convert to FLAC
			$flacCmd = sprintf(
				'"%s" -i "%s" -vn -map 0:a -compression_level %d -map_metadata -1 -metadata ENCODER= "%s" 2>&1',
				self::$ffmpegPath,
				escapeshellarg($file['tmp_name']),
				self::$flacCompressionLevel,
				escapeshellarg($flacPath)
			);
			exec($flacCmd, $flacOutput, $flacReturnCode);

			if ($flacReturnCode !== 0) {
				throw new Exception('FLAC conversion failed: ' . implode("\n", $flacOutput));
			}

			// Convert to Opus
			$opusCmd = sprintf(
				'"%s" -i "%s" -vn -map 0:a -c:a libopus -b:a %dk -vbr on -map_metadata -1 -metadata ENCODER= "%s" 2>&1',
				self::$ffmpegPath,
				escapeshellarg($file['tmp_name']),
				self::$opusBitrate,
				escapeshellarg($opusPath)
			);
			exec($opusCmd, $opusOutput, $opusReturnCode);

			if ($opusReturnCode !== 0) {
				throw new Exception('Opus conversion failed: ' . implode("\n", $opusOutput));
			}

			$result = [
				'success' => true,
				'flac_filename' => $flacFileName,
				'opus_filename' => $opusFileName,
				'duration' => (float)$duration
			];

			if ($warning) {
				$result['warning'] = $warning;
			}

			return $result;

		} catch (Exception $e) {
			if (file_exists($flacPath)) {
				unlink($flacPath);
			}
			if (file_exists($opusPath)) {
				unlink($opusPath);
			}

			return ['success' => false, 'error' => 'Audio conversion failed: ' . $e->getMessage()];
		}
	}

	public static function uploadImage($image, ImageType $imageType): array
	{
		if (!extension_loaded('imagick')) {
			return ['success' => false, 'error' => 'ImageMagick extension not available'];
		}

		$imageUploadDir = $_SERVER["DOCUMENT_ROOT"] . "/BeatStream/images/" . $imageType->value . "/";
		$largeDir = $imageUploadDir . "large/";
		$thumbnailDir = $imageUploadDir . "thumbnail/";

		if (!isset($image) || $image['error'] !== UPLOAD_ERR_OK) {
			return ['success' => false, 'error' => 'No image file provided or upload error'];
		}

		if (!is_dir($largeDir)) mkdir($largeDir, 0777, true);
		if (!is_dir($thumbnailDir)) mkdir($thumbnailDir, 0777, true);

		$uniqueId = uniqid();
		$largeFileName = $uniqueId . '.webp';
		$thumbnailFileName = $uniqueId . '.webp';

		try {
			$imagick = new Imagick($image['tmp_name']);
			$imagick->stripImage(); // Remove metadata

			// Create 640x640 version
			$large = clone $imagick;
			$large->resizeImage(640, 640, Imagick::FILTER_LANCZOS, 1, true);
			$large->setImageFormat('webp');
			$large->setImageCompressionQuality(60);
			$large->writeImage($largeDir . $largeFileName);

			// Create 64x64 version
			$thumbnail = clone $imagick;
			$thumbnail->resizeImage(64, 64, Imagick::FILTER_LANCZOS, 1, true);
			$thumbnail->setImageFormat('webp');
			$thumbnail->setImageCompressionQuality(60);
			$thumbnail->writeImage($thumbnailDir . $thumbnailFileName);

			$imagick->clear();
			$large->clear();
			$thumbnail->clear();

			return [
				'success' => true,
				'large_filename' => $largeFileName,
				'thumbnail_filename' => $thumbnailFileName
			];

		} catch (Exception $e) {
			return ['success' => false, 'error' => 'Image conversion failed: ' . $e->getMessage()];
		}
	}
}

enum ImageType: string
{
	case SONG = 'song';
	case USER = 'user';
	case PLAYLIST = 'playlist';
	case ARTIST = 'artist';
	case ALBUM = 'album';
}