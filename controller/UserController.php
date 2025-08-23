<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/Objects/User.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/dbConnection.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/Utils.php");

class UserController
{
	public static function insertUser(User $user): void
	{
		$salt = htmlspecialchars(Utils::generateRandomString(16));
		$password = hash("sha256", $user->getUserPassword() . $salt);

		// Generate unique user ID
		do {
			$newUserID = rand();
		} while (self::IdExists($newUserID));

		$stmt = DBConn::getConn()->prepare("INSERT INTO user VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

		// Store values in variables to avoid reference errors
		$username = $user->getUsername();
		$email = $user->getEmail();
		$imageName = $user->getImageName();
		$thumbnailName = $user->getThumbnailName();
		$isAdmin = $user->isAdmin();
		$isArtist = $user->isArtist();

		$stmt->bind_param("issssiiss", $newUserID, $username, $email, $password, $salt, $isAdmin, $isArtist, $imageName, $thumbnailName);
		$stmt->execute();
		$stmt->close();
	}

	public static function IdExists(int $userID): bool
	{
		$stmt = DBConn::getConn()->prepare("SELECT DISTINCT userID FROM user WHERE userID = ? LIMIT 1");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$result = $stmt->get_result()->num_rows > 0;
		$stmt->close();
		return $result;
	}

	public static function usernameExists(string $username): bool
	{
		$stmt = DBConn::getConn()->prepare("SELECT DISTINCT username FROM user WHERE username = ? LIMIT 1");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result()->num_rows > 0;
		$stmt->close();
		return $result;
	}

	public static function emailExists(string $email): bool
	{
		$stmt = DBConn::getConn()->prepare("SELECT DISTINCT email FROM user WHERE email = ? LIMIT 1");
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$result = $stmt->get_result()->num_rows > 0;
		$stmt->close();
		return $result;
	}

	public static function deleteUser(int $userID): void
	{
		$conn = DBConn::getConn();
		$stmt = $conn->prepare("SELECT imageName FROM user WHERE userID = ?");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		if ($result && $result['imageName']) {
			try {
				unlink($_SERVER["DOCUMENT_ROOT"] . "/BeatStream/images/user/" . $result['imageName']);
			} catch (Exception) {
			}
		}

		// Delete playlists created by the user
		$queries = [
			"DELETE FROM in_playlist WHERE playlistID IN (SELECT playlistID FROM playlist WHERE creatorID = ?);",
			"DELETE FROM playlist WHERE creatorID = ?;",
		];
		foreach ($queries as $sql) {
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $userID);
			$stmt->execute();
			$stmt->close();
		}

		$stmt = $conn->prepare("SELECT artistID FROM artist WHERE userID = ?");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		$artistID = $row ? $row['artistID'] : null;
		$stmt->close();

		// Delete all relations and the songs themselves
		if ($artistID !== null) {
			DataController::deleteArtist($artistID);
		}

		$stmt = $conn->prepare("DELETE FROM user WHERE userID = ?;");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$stmt->close();
	}

	public static function getUserList(string $sortBy = "user.username ASC"): array
	{
		// Whitelist allowed sort options to prevent SQL injection
		$allowedSortOptions = [
			"user.username ASC",
			"user.username DESC",
			"user.email ASC",
			"user.email DESC",
			"user.userID ASC",
			"user.userID DESC"
		];

		if (!in_array($sortBy, $allowedSortOptions)) {
			$sortBy = "user.username ASC"; // Default fallback
		}

		$stmt = DBConn::getConn()->prepare("SELECT * FROM user ORDER BY " . $sortBy);

		$stmt->execute();
		$result = $stmt->get_result();

		$userList = array();
		while ($row = $result->fetch_assoc()) {
			$userList[] = new User($row["userID"], $row["username"], $row["email"], $row["userPassword"], $row["salt"], $row["isAdmin"], $row["isArtist"], $row["imageName"], $row["thumbnailName"]);
		}

		$stmt->close();

		return $userList;
	}
}
