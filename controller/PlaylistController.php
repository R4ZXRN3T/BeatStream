<?php

require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php";
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/Objects/Playlist.php";

class PlaylistController
{
	public static function insertPlaylist(Playlist $playlist): void
	{
		do {
			$newPlaylistID = rand();
		} while (PlaylistController::IdExists($newPlaylistID));

		$stmt = DBConn::getConn()->prepare("INSERT INTO playlist VALUES (?, ?, ?, ?, ?, ?, ?)");

		$imageName = $playlist->getImageName();
		$thumbnailName = $playlist->getThumbnailName();
		$name = $playlist->getName();
		$length = $playlist->getLength();
		$duration = $playlist->getDuration();
		$creatorID = $playlist->getCreatorID();

		$stmt->bind_param("isssiis", $newPlaylistID, $imageName, $thumbnailName, $name, $length, $duration, $creatorID);
		$stmt->execute();
		$stmt->close();

		for ($i = 0; $i < count($playlist->getSongIDs()); $i++) {
			$stmt = DBConn::getConn()->prepare("INSERT INTO in_playlist (playlistID, songID, songIndex) VALUES (?, ?, ?)");
			$stmt->bind_param("iii", $newPlaylistID, $playlist->getSongIDs()[$i], $i);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function IdExists(int $playlistID): bool
	{
		$stmt = DBConn::getConn()->prepare("SELECT DISTINCT playlistID FROM playlist WHERE playlistID = ? LIMIT 1");
		$stmt->bind_param("i", $playlistID);
		$stmt->execute();
		return $stmt->get_result()->num_rows > 0;
	}

	public static function getPlaylistList(string $sortBy = "playlist.name ASC"): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT playlist.playlistID, playlist.imageName, playlist.thumbnailName, playlist.name, length, duration, creatorID, user.username, song.songID, songIndex
		FROM playlist, in_playlist, song, user
		WHERE song.songID = in_playlist.songID
  		AND playlist.playlistID = in_playlist.playlistID
		AND playlist.creatorID = user.userID
		ORDER BY " . $sortBy . ", in_playlist.songIndex;");

		$stmt->execute();
		$result = $stmt->get_result();

		$playlistList = array();
		while ($row = $result->fetch_assoc()) {
			$alreadyExists = false;
			$newPlaylist = new Playlist($row["playlistID"], $row["name"], array($row["songID"]), $row["duration"], $row["length"], $row['imageName'], $row["thumbnailName"], $row["creatorID"], $row["username"]);

			for ($i = 0; $i < count($playlistList); $i++) {
				if ($playlistList[$i]->getPlaylistID() == $newPlaylist->getPlaylistID()) {
					$alreadyExists = true;
					$playlistList[$i]->setSongIDs(array_merge($playlistList[$i]->getSongIDs(), $newPlaylist->getSongIDs()));
				}
			}
			if (!$alreadyExists) {
				$playlistList[] = $newPlaylist;
			}
		}

		$stmt->close();

		return $playlistList;
	}

	public static function deletePlaylist(int $playlistID): void
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imageName, thumbnailName FROM playlist WHERE playlistID = ?");
		$deleteImage->bind_param("i", $playlistID);
		$deleteImage->execute();
		$result = $deleteImage->get_result()->fetch_assoc();
		if ($result) {
			try {
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/images/playlist/large/" . $result['imageName']);
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/images/playlist/thumbnail/" . $result['thumbnailName']);
			} catch (Exception) {
			}
		}
		$deleteImage->close();

		$queries = [
			"DELETE FROM in_playlist WHERE in_playlist.playlistID=?",
			"DELETE FROM playlist WHERE playlist.playlistID=?"
		];

		foreach ($queries as $sql) {
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $playlistID);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function getPlaylistById(int $playlistID): ?Playlist
	{
		$stmt = DBConn::getConn()->prepare("SELECT playlist.playlistID, playlist.imageName, playlist.thumbnailName, playlist.name, length, duration, creatorID, user.username, song.songID, songIndex
		FROM playlist 
		LEFT JOIN in_playlist ON playlist.playlistID = in_playlist.playlistID
		LEFT JOIN song ON song.songID = in_playlist.songID
		LEFT JOIN user ON playlist.creatorID = user.userID
		WHERE playlist.playlistID = ?
		ORDER BY in_playlist.songIndex;");

		$stmt->bind_param("i", $playlistID);
		$stmt->execute();
		$result = $stmt->get_result();

		$playlist = null;
		while ($row = $result->fetch_assoc()) {
			if ($playlist === null) {
				$playlist = new Playlist($row["playlistID"], $row["name"], $row["songID"] ? array($row["songID"]) : array(), $row["duration"], $row["length"], $row['imageName'], $row["thumbnailName"], $row["creatorID"], $row["username"] ?? "Unknown");
			} else {
				if ($row["songID"]) {
					$playlist->setSongIDs(array_merge($playlist->getSongIDs(), array($row["songID"])));
				}
			}
		}

		$stmt->close();

		return $playlist;
	}

	public static function searchPlaylist(string $query): array
	{
		$stmt = DBConn::getConn()->prepare("
			SELECT playlist.playlistID, playlist.imageName, playlist.thumbnailName, playlist.name, length, duration, creatorID, user.username
			FROM playlist
			JOIN user ON playlist.creatorID = user.userID
			WHERE playlist.name LIKE CONCAT('%', ?, '%') OR user.username LIKE CONCAT('%', ?, '%')
			ORDER BY playlist.name
		");
		$stmt->bind_param("ss", $query, $query);
		$stmt->execute();
		$result = $stmt->get_result();

		$playlistList = [];
		while ($row = $result->fetch_assoc()) {
			$playlistList[] = new Playlist(
				$row["playlistID"], $row["name"], [], $row["duration"], $row["length"],
				$row["imageName"], $row["thumbnailName"], $row["creatorID"], $row["username"]
			);
		}
		$stmt->close();
		return $playlistList;
	}
}