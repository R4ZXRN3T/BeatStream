<?php

class Converter
{
	private static int $opusBitrate = 160;
	private static int $flacCompressionLevel = 5;

	public static function uploadAudio($file): array
	{
		$flacUploadDir = $GLOBALS['PROJECT_ROOT_DIR'] . "/audio/flac/";
		$opusUploadDir = $GLOBALS['PROJECT_ROOT_DIR'] . "/audio/opus/";

		if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return ['success' => false, 'error' => 'No audio file provided or upload error'];

		$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
		$allowedExtensions = [
			'wav', 'flac', 'ape', 'wv', 'tta', 'aiff', 'aif', 'au', 'snd', 'caf', 'w64', 'rf64', 'bwf', 'tak', 'als',
			'mp3', 'aac', 'm4a', 'ogg', 'oga', 'opus', 'wma', 'ac3', 'eac3', 'dts', 'amr', 'awb', 'gsm', 'qcp', 'evrc',
			'mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm', 'ogv', '3gp', '3g2', 'asf', 'vob', 'ts', 'mts', 'm2ts',
			'ra', 'rm', 'shn', 'mlp', 'truehd', 'atrac', 'vqf', 'spx', 'mka', 'mpc', 'mp2', 'mp1', 'mpga'
		];

		if (!in_array(strtolower($extension), $allowedExtensions)) return ['success' => false, 'error' => 'Invalid audio file format'];

		$losslessFormats = ['wav', 'flac', 'caf', 'aiff', 'aif', 'ape', 'wv', 'tta', 'shn', 'pcm', 'au', 'snd', 'w64', 'rf64', 'bwf', 'tak', 'als'];

		if (!in_array(strtolower($extension), $losslessFormats)) return ['success' => false, 'error' => 'Error: You are uploading a lossy audio format (' . strtoupper($extension) . '). In order to keep up our quality standard, you must upload lossless files such as FLAC or WAV.'];

		if (!is_dir($flacUploadDir)) mkdir($flacUploadDir, 0777, true);
		if (!is_dir($opusUploadDir)) mkdir($opusUploadDir, 0777, true);

		$uniqueId = uniqid();
		$flacFileName = $uniqueId . '.flac';
		$opusFileName = $uniqueId . '.opus';
		$flacPath = $flacUploadDir . $flacFileName;
		$opusPath = $opusUploadDir . $opusFileName;

		try {
			// Get duration using ffprobe
			$durationCmd = sprintf(
				'ffprobe -v quiet -print_format csv=p=0 -show_entries format=duration "%s"',
				escapeshellarg($file['tmp_name'])
			);
			$duration = trim(shell_exec($durationCmd));

			// Convert to FLAC
			$flacCmd = sprintf(
				'ffmpeg -i "%s" -vn -map 0:a -compression_level %d -map_metadata -1 "%s" 2>&1',
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
				'ffmpeg -i "%s" -vn -map 0:a -c:a libopus -b:a %dk -vbr on -map_metadata -1 "%s" 2>&1',
				escapeshellarg($file['tmp_name']),
				self::$opusBitrate,
				escapeshellarg($opusPath)
			);
			exec($opusCmd, $opusOutput, $opusReturnCode);

			if ($opusReturnCode !== 0) {
				throw new Exception('Opus conversion failed: ' . implode("\n", $opusOutput));
			}

			$duration *= 1000;

			return [
				'success' => true,
				'flac_filename' => $flacFileName,
				'opus_filename' => $opusFileName,
				'duration' => (int)$duration // Convert seconds to milliseconds
			];

		} catch (Exception $e) {
			if (file_exists($flacPath)) unlink($flacPath);
			if (file_exists($opusPath)) unlink($opusPath);

			return ['success' => false, 'error' => 'Audio conversion failed: ' . $e->getMessage()];
		}
	}

	public static function uploadImage($image, ImageType $imageType): array
	{
		if (!extension_loaded('imagick')) {
			return ['success' => false, 'error' => 'ImageMagick extension not available'];
		}

		$imageUploadDir = $GLOBALS['PROJECT_ROOT_DIR'] . "/images/" . $imageType->value . "/";
		$originalDir = $imageUploadDir . "original/";
		$largeDir = $imageUploadDir . "large/";
		$thumbnailDir = $imageUploadDir . "thumbnail/";

		if (!isset($image) || $image['error'] !== UPLOAD_ERR_OK) return ['success' => false, 'error' => 'No image file provided or upload error'];


		if (!is_dir($originalDir)) mkdir($originalDir, 0777, true);
		if (!is_dir($largeDir)) mkdir($largeDir, 0777, true);
		if (!is_dir($thumbnailDir)) mkdir($thumbnailDir, 0777, true);

		$uniqueId = uniqid();
		$originalFileName = $uniqueId . '.png';
		$largeFileName = $uniqueId . '.webp';
		$thumbnailFileName = $uniqueId . '.webp';

		try {
			$imagick = new Imagick($image['tmp_name']);
			$imagick->stripImage();

			// Create original PNG version (max 3000x3000)
			$original = clone $imagick;

			// Save original only for SONGs and ALBUMs
			if ($imageType === ImageType::SONG || $imageType === ImageType::ALBUM) {
				$width = $original->getImageWidth();
				$height = $original->getImageHeight();

				if ($width > 3000 || $height > 3000) {
					$original->resizeImage(3000, 3000, Imagick::FILTER_LANCZOS, 1, true);
				}

				$original->setImageFormat('png');
				$original->writeImage($originalDir . $originalFileName);
			}

			// Create 640x640 version
			$large = clone $imagick;
			$large->resizeImage(640, 640, Imagick::FILTER_LANCZOS, 1, true);
			$large->setImageFormat('webp');
			$large->setImageCompressionQuality(80);
			$large->writeImage($largeDir . $largeFileName);

			// Create 64x64 version
			$thumbnail = clone $imagick;
			$thumbnail->resizeImage(80, 80, Imagick::FILTER_LANCZOS, 1, true);
			$thumbnail->setImageFormat('webp');
			$thumbnail->setImageCompressionQuality(80);
			$thumbnail->writeImage($thumbnailDir . $thumbnailFileName);

			$imagick->clear();
			if ($imageType === ImageType::SONG || $imageType === ImageType::ALBUM) $original->clear();
			$large->clear();
			$thumbnail->clear();

			return [
				'success' => true,
				'original_filename' => $originalFileName,
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