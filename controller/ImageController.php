<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/converter.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/dbConnection.php';

class ImageController
{
	/**
	 * @throws Exception
	 */
	public static function insertImage($image, ImageType $imageType): int
	{
		$uploadResult = Converter::uploadImage($image, $imageType);
		if (!$uploadResult["success"]) {
			throw new Exception("Image upload failed: " . $uploadResult["error"]);
		}

		do {
			$newImageID = rand();
		} while (self::IdExists($newImageID));

		$stmt = DBConn::getConn()->prepare("INSERT INTO image (image.imageID, imageType, originalImageName, largeName, thumbnailName) VALUES (?, ?, ?, ?, ?)");
		$stmt->bind_param("issss", $newImageID, $imageType, $uploadResult["original_filename"], $uploadResult["large_filename"], $uploadResult["thumbnail_filename"]);
		$stmt->execute();
		$stmt->close();

		return $newImageID;
	}

	public static function IdExists(int $imageID): bool
	{
		$stmt = DBConn::getConn()->prepare("SELECT DISTINCT imageID FROM image WHERE imageID = ? LIMIT 1");
		$stmt->bind_param("i", $imageID);
		$stmt->execute();
		return $stmt->get_result()->num_rows > 0;
	}

	/**
	 * @param int $imageID
	 * @return array|false
	 */
	public static function getImage(int $imageID): false|array
	{
		$stmt = DBConn::getConn()->prepare("SELECT * FROM image WHERE imageID = ? LIMIT 1");
		$stmt->bind_param("i", $imageID);
		$stmt->execute();
		return $stmt->get_result()->fetch_assoc();
	}

	public static function deleteImage(int $imageID): bool
	{
		$stmt = DBConn::getConn()->prepare("DELETE FROM image WHERE imageID = ?");
		$stmt->bind_param("i", $imageID);
		return $stmt->execute();
	}
}
