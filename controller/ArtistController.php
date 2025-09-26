<?php

require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/Objects/Artist.php";
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php";

class ArtistController
{

	public static function getArtistList(string $sortBy = "artist.name ASC"): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT * FROM artist ORDER BY " . $sortBy . ";");

		$stmt->execute();
		$result = $stmt->get_result();

		$artistList = array();
		while ($row = $result->fetch_assoc()) {
			$artistList[] = new Artist($row["artistID"], $row["name"], $row["imageName"], $row["thumbnailName"], $row["activeSince"], $row["userID"]);
		}

		$stmt->close();

		return $artistList;
	}

	public static function insertArtist(Artist $artist): void
	{
		// Generate unique artist ID
		do {
			$newArtistID = rand();
		} while (self::IdExists($newArtistID));

		if (!UserController::IdExists($artist->getUserID())) return;

		$stmt = DBConn::getConn()->prepare("INSERT INTO artist (artistID, name, imageName, thumbnailName, activeSince, userID) VALUES (?, ?, ?, ?, ?, ?)");

		// Store values in variables to avoid reference errors
		$artistName = $artist->getName();
		$imageName = $artist->getImageName();
		$thumbnailName = $artist->getThumbnailName();
		$activeSinceStr = $artist->getActiveSince()->format("Y-m-d");
		$userID = $artist->getUserID();

		$stmt->bind_param("issssi", $newArtistID, $artistName, $imageName, $thumbnailName, $activeSinceStr, $userID);
		$stmt->execute();
		$stmt->close();

		$stmt = DBConn::getConn()->prepare("UPDATE user SET isArtist = TRUE WHERE userID = ?");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$stmt->close();
	}

	public static function IdExists(int $artistID): bool
	{
		$stmt = DBConn::getConn()->prepare("SELECT DISTINCT artistID FROM artist WHERE artistID = ? LIMIT 1");
		$stmt->bind_param("i", $artistID);
		$stmt->execute();
		return $stmt->get_result()->num_rows > 0;
	}

	public static function deleteArtist(int $artistID): void
	{
		require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/controller/SongController.php";

		$conn = DBConn::getConn();

		// Get image name for deletion
		$deleteImage = $conn->prepare("SELECT imageName, thumbnailName FROM artist WHERE artistID = ?");
		$deleteImage->bind_param("i", $artistID);
		$deleteImage->execute();
		$result = $deleteImage->get_result();
		if ($row = $result->fetch_assoc()) {
			try {
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/images/artist/large/" . $row['imageName']);
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/images/artist/thumbnail/" . $row['thumbnailName']);
			} catch (Exception) {
			}
		}
		$deleteImage->close();

		// Delete associated songs
		$stmt = $conn->prepare("SELECT songID FROM releases_song WHERE artistID = ?");
		$stmt->bind_param("i", $artistID);
		$stmt->execute();
		$result = $stmt->get_result();
		while ($row = $result->fetch_assoc()) {
			SongController::deleteSong($row['songID']);
		}
		$stmt->close();

		// Delete releases_song, albums, etc.
		$queries = [
			"DELETE FROM in_album WHERE albumID IN (SELECT albumID FROM releases_album WHERE artistID = ?)",
			"DELETE FROM releases_album WHERE artistID = ?",
			"DELETE FROM album WHERE albumID IN (SELECT albumID FROM releases_album WHERE artistID = ?)",
			"UPDATE user SET isArtist = FALSE WHERE userID = (SELECT userID FROM artist WHERE artistID = ?)",
			"DELETE FROM artist WHERE artistID = ?"
		];
		foreach ($queries as $sql) {
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $artistID);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function getArtistByID(int $artistID): ?Artist
	{
		$stmt = DBConn::getConn()->prepare("SELECT * FROM artist WHERE artistID = ? LIMIT 1");
		$stmt->bind_param("i", $artistID);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($row = $result->fetch_assoc()) {
			$artist = new Artist($row["artistID"], $row["name"], $row["imageName"], $row["thumbnailName"], $row["activeSince"], $row["userID"]);
			$stmt->close();
			return $artist;
		} else {
			$stmt->close();
			return null;
		}
	}

	public static function searchArtist(string $query): array
	{
		$stmt = DBConn::getConn()->prepare("
			SELECT * FROM artist
			WHERE name LIKE CONCAT('%', ?, '%')
			ORDER BY name
		");
		$stmt->bind_param("s", $query);
		$stmt->execute();
		$result = $stmt->get_result();

		$artistList = [];
		while ($row = $result->fetch_assoc()) {
			$artistList[] = new Artist($row["artistID"], $row["name"], $row["imageName"], $row["thumbnailName"], $row["activeSince"], $row["userID"]);
		}
		$stmt->close();
		return $artistList;
	}
}