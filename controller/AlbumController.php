<?php

require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/Objects/Album.php";
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php";

class AlbumController
{
	public static function insertAlbum(Album $album): void
	{
		do {
			$newAlbumID = rand();
		} while (AlbumController::IdExists($newAlbumID));

		$name = $album->getName();
		$imageName = $album->getImageName();
		$thumbnailName = $album->getThumbnailName();
		$originalImageName = $album->getOriginalImageName();
		$length = $album->getLength();
		$duration = $album->getDuration();
		$releaseDate = $album->getReleaseDate()->format("Y-m-d");
		$isSingle = (int)$album->isSingle();

		$sqlAlbum = "INSERT INTO album VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = DBConn::getConn()->prepare($sqlAlbum);

		$stmt->bind_param("isiisisss", $newAlbumID, $name, $length, $duration, $releaseDate, $isSingle, $imageName, $thumbnailName, $originalImageName);
		$stmt->execute();
		$stmt->close();

		$artistsInAlbum = $album->getArtistIDs();

		for ($j = 0; $j < count($artistsInAlbum); $j++) {
			$stmt = DBConn::getConn()->prepare("INSERT INTO releases_album (artistID, albumID, artistIndex) VALUES (?, ?, ?)");
			$stmt->bind_param("iii", $artistsInAlbum[$j], $newAlbumID, $j);
			$stmt->execute();
			$stmt->close();
		}
		for ($i = 0; $i < count($album->getSongIDs()); $i++) {
			$stmt = DBConn::getConn()->prepare("INSERT INTO in_album (songID, albumID, songIndex) VALUES (?, ?, ?)");
			$stmt->bind_param("iii", $album->getSongIDs()[$i], $newAlbumID, $i);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function IdExists(int $albumID): bool
	{
		$stmt = DBConn::getConn()->prepare("SELECT DISTINCT albumID FROM album WHERE albumID = ? LIMIT 1");
		$stmt->bind_param("i", $albumID);
		$stmt->execute();
		return $stmt->get_result()->num_rows > 0;
	}

	public static function getAlbumList(string $sortBy = "album.title ASC"): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT album.albumID, title, name, album.imageName, album.thumbnailName, album.originalImageName, length, duration, album.releaseDate, artist.artistID, album.isSingle
		FROM album, artist, releases_album
		WHERE releases_album.artistID = artist.artistID
		AND album.albumID = releases_album.albumID
		ORDER BY " . $sortBy . ", releases_album.artistIndex;");

		$stmt->execute();
		$result = $stmt->get_result();

		$albumList = array();
		while ($row = $result->fetch_assoc()) {
			$newAlbum = new Album($row["albumID"], $row["title"], array(), array($row["name"]), array($row['artistID']), $row["imageName"], $row["thumbnailName"], $row["length"], $row["duration"], $row["releaseDate"], (bool)$row["isSingle"], $row["originalImageName"] ?? "");
			$alreadyExists = false;

			for ($i = 0; $i < count($albumList); $i++) {
				if ($albumList[$i]->getAlbumID() == $newAlbum->getAlbumID()) {
					$alreadyExists = true;
					$albumList[$i]->setArtists(array_merge($albumList[$i]->getArtists(), $newAlbum->getArtists()));
					$albumList[$i]->setArtistIDs(array_merge($albumList[$i]->getArtistIDs(), $newAlbum->getArtistIDs()));
				}
			}
			if (!$alreadyExists) $albumList[] = $newAlbum;
		}
		$stmt->close();

		$stmt = DBConn::getConn()->prepare("SELECT album.albumID, in_album.songID, in_album.songIndex
                                   FROM in_album, album
                                   WHERE in_album.albumId = album.albumID
                                   ORDER BY album.albumID, in_album.songIndex");
		$stmt->execute();
		$result = $stmt->get_result();

		$albumSongs = [];
		while ($row = $result->fetch_assoc()) {
			$albumID = $row['albumID'];
			if (!isset($albumSongs[$albumID])) {
				$albumSongs[$albumID] = [];
			}
			$albumSongs[$albumID][] = $row['songID'];
		}

		foreach ($albumSongs as $albumID => $songs) {
			for ($i = 0; $i < count($albumList); $i++) {
				if ($albumList[$i]->getAlbumID() == $albumID) {
					$albumList[$i]->setSongIDs($songs);
					break;
				}
			}
		}
		$stmt->close();

		return $albumList;
	}

	public static function getArtistAlbums(int $artistID, string $sortBy = "album.releaseDate DESC"): array
	{
		$stmt = DBConn::getConn()->prepare("
			SELECT album.albumID
			FROM album, releases_album, artist
			WHERE releases_album.artistID = artist.artistID
			AND album.albumID = releases_album.albumID
			AND artist.artistID = ?
			ORDER BY " . $sortBy . ", releases_album.artistIndex;
		");

		$stmt->bind_param("i", $artistID);
		$stmt->execute();
		$result = $stmt->get_result();

		$albumIDs = [];
		while ($row = $result->fetch_assoc()) {
			$albumIDs[] = $row['albumID'];
		}

		$stmt = DBConn::getConn()->prepare("
			SELECT album.albumID, title, name, album.imageName, album.thumbnailName, album.originalImageName, length, duration, album.releaseDate, artist.artistID, album.isSingle
			FROM album, artist, releases_album
			WHERE album.albumID = releases_album.albumID
			AND releases_album.artistID = artist.artistID
			AND album.albumID IN (" . implode(',', $albumIDs) . ")
			ORDER BY " . $sortBy . ", releases_album.artistIndex;
		");

		$stmt->execute();
		$result = $stmt->get_result();

		$albumList = array();
		while ($row = $result->fetch_assoc()) {
			$newAlbum = new Album($row["albumID"], $row["title"], array(), array($row["name"]), array($row['artistID']), $row["imageName"], $row["thumbnailName"], $row["length"], $row["duration"], $row["releaseDate"], (bool)$row["isSingle"], $row["originalImageName"] ?? "");
			$alreadyExists = false;

			for ($i = 0; $i < count($albumList); $i++) {
				if ($albumList[$i]->getAlbumID() == $newAlbum->getAlbumID()) {
					$alreadyExists = true;
					$albumList[$i]->setArtists(array_merge($albumList[$i]->getArtists(), $newAlbum->getArtists()));
					$albumList[$i]->setArtistIDs(array_merge($albumList[$i]->getArtistIDs(), $newAlbum->getArtistIDs()));
				}
			}
			if (!$alreadyExists) $albumList[] = $newAlbum;
		}
		$stmt->close();
		return $albumList;
	}

	public static function getAlbumByID(int $albumID): ?Album
	{
		// Get album basic info and artists
		$stmt = DBConn::getConn()->prepare("
			SELECT album.albumID, title, name, album.imageName, album.thumbnailName, album.originalImageName, length, duration, album.releaseDate, artist.artistID, album.isSingle
			FROM album, artist, releases_album
			WHERE releases_album.artistID = artist.artistID
			AND album.albumID = releases_album.albumID
			AND album.albumID = ?
			ORDER BY releases_album.artistIndex
    	");

		$stmt->bind_param("i", $albumID);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			$stmt->close();
			return null;
		}

		$artists = array();
		$artistIDs = array();
		$albumData = null;

		while ($row = $result->fetch_assoc()) {
			if (!$albumData) {
				$albumData = $row;
			}
			$artists[] = $row["name"];
			$artistIDs[] = $row['artistID'];
		}
		$stmt->close();

		// Get song IDs for this album
		$stmt = DBConn::getConn()->prepare("
			SELECT songID 
			FROM in_album 
			WHERE albumID = ? 
			ORDER BY songIndex
		");

		$stmt->bind_param("i", $albumID);
		$stmt->execute();
		$result = $stmt->get_result();

		$songIDs = array();
		while ($row = $result->fetch_assoc()) {
			$songIDs[] = $row['songID'];
		}
		$stmt->close();

		return new Album($albumData["albumID"], $albumData["title"], $songIDs, $artists, $artistIDs, $albumData["imageName"], $albumData["thumbnailName"], $albumData["length"], $albumData["duration"], $albumData["releaseDate"], (bool)$albumData["isSingle"], $albumData["originalImageName"] ?? "");
	}

	public static function getRandomAlbums(int $limit = 3): array
	{
		// First get random album IDs
		$stmt = DBConn::getConn()->prepare("
			SELECT DISTINCT album.albumID 
			FROM album
			WHERE isSingle = 0
			ORDER BY RAND() 
			LIMIT ?
		");
		$stmt->bind_param("i", $limit);
		$stmt->execute();
		$result = $stmt->get_result();

		$albumIDs = [];
		while ($row = $result->fetch_assoc()) {
			$albumIDs[] = $row['albumID'];
		}
		$stmt->close();

		if (empty($albumIDs)) {
			return [];
		}

		// Then get all data for those albums
		$placeholders = str_repeat('?,', count($albumIDs) - 1) . '?';
		$stmt = DBConn::getConn()->prepare("
			SELECT album.albumID, title, name, album.imageName, album.thumbnailName, album.originalImageName, length, duration, album.releaseDate, artist.artistID, album.isSingle
			FROM album, artist, releases_album
			WHERE releases_album.artistID = artist.artistID
			AND album.albumID = releases_album.albumID
			AND album.albumID IN ($placeholders)
			ORDER BY album.albumID, releases_album.artistIndex;
		");
		$stmt->bind_param(str_repeat('i', count($albumIDs)), ...$albumIDs);
		$stmt->execute();
		$result = $stmt->get_result();

		$albumList = array();
		while ($row = $result->fetch_assoc()) {
			$newAlbum = new Album($row["albumID"], $row["title"], array(), array($row["name"]), array($row['artistID']), $row["imageName"], $row["thumbnailName"], $row["length"], $row["duration"], $row["releaseDate"], (bool)$row["isSingle"], $row["originalImageName"] ?? "");
			$alreadyExists = false;

			for ($i = 0; $i < count($albumList); $i++) {
				if ($albumList[$i]->getAlbumID() == $newAlbum->getAlbumID()) {
					$alreadyExists = true;
					$albumList[$i]->setArtists(array_merge($albumList[$i]->getArtists(), $newAlbum->getArtists()));
					$albumList[$i]->setArtistIDs(array_merge($albumList[$i]->getArtistIDs(), $newAlbum->getArtistIDs()));
				}
			}
			if (!$alreadyExists) $albumList[] = $newAlbum;
		}
		$stmt->close();

		// Get song data for albums (same as original method)
		$stmt = DBConn::getConn()->prepare("SELECT album.albumID, in_album.songID, in_album.songIndex
                                   FROM in_album, album
                                   WHERE in_album.albumId = album.albumID
                                   AND album.albumID IN ($placeholders)
                                   ORDER BY album.albumID, in_album.songIndex");
		$stmt->bind_param(str_repeat('i', count($albumIDs)), ...$albumIDs);
		$stmt->execute();
		$result = $stmt->get_result();

		$albumSongs = [];
		while ($row = $result->fetch_assoc()) {
			$albumID = $row['albumID'];
			if (!isset($albumSongs[$albumID])) {
				$albumSongs[$albumID] = [];
			}
			$albumSongs[$albumID][] = $row['songID'];
		}

		foreach ($albumSongs as $albumID => $songs) {
			for ($i = 0; $i < count($albumList); $i++) {
				if ($albumList[$i]->getAlbumID() == $albumID) {
					$albumList[$i]->setSongIDs($songs);
					break;
				}
			}
		}
		$stmt->close();

		return $albumList;
	}

	public static function deleteAlbum(int $albumID): void
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imageName, thumbnailName FROM album WHERE albumID = ?");
		$deleteImage->bind_param("i", $albumID);
		$deleteImage->execute();

		$result = $deleteImage->get_result()->fetch_assoc();
		if ($result) {
			try {
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/images/album/large/" . $result['imageName']);
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/images/album/thumbnail/" . $result['thumbnailName']);
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/images/album/original/" . $result['originalImageName']);
			} catch (Exception) {
			}
		}
		$deleteImage->close();

		$queries = [
			"DELETE FROM in_album WHERE in_album.albumID=?",
			"DELETE FROM releases_album WHERE releases_album.albumID=?",
			"DELETE FROM album WHERE album.albumID=?"
		];

		foreach ($queries as $sql) {
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $albumID);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function searchAlbum(string $query): array
	{
		$words = preg_split('/\s+/', trim($query));
		if (empty($words)) return [];

		// Build dynamic WHERE clause
		$where = [];
		$params = [];
		$types = '';
		foreach ($words as $word) {
			$where[] = "(album.title LIKE CONCAT('%', ?, '%') OR artist.name LIKE CONCAT('%', ?, '%'))";
			$params[] = $word;
			$params[] = $word;
			$types .= 'ss';
		}
		$whereClause = implode(' AND ', $where);

		$sql = "
			SELECT album.albumID, album.title, artist.name, album.imageName, album.thumbnailName, album.length, album.duration, album.releaseDate, artist.artistID, album.isSingle
			FROM album
			JOIN releases_album ON album.albumID = releases_album.albumID
			JOIN artist ON artist.artistID = releases_album.artistID
			WHERE $whereClause
			ORDER BY album.title, releases_album.artistIndex
		";

		$stmt = DBConn::getConn()->prepare($sql);
		$stmt->bind_param($types, ...$params);
		$stmt->execute();
		$result = $stmt->get_result();

		$albumList = [];
		while ($row = $result->fetch_assoc()) {
			$albumID = $row["albumID"];
			if (!isset($albumList[$albumID])) {
				$albumList[$albumID] = new Album(
					$row["albumID"], $row["title"], [], [$row["name"]], [$row["artistID"]],
					$row["imageName"], $row["thumbnailName"], $row["length"], $row["duration"], $row["releaseDate"], (bool)$row["isSingle"]
				);
			} else {
				$albumList[$albumID]->setArtists(array_merge($albumList[$albumID]->getArtists(), [$row["name"]]));
				$albumList[$albumID]->setArtistIDs(array_merge($albumList[$albumID]->getArtistIDs(), [$row["artistID"]]));
			}
		}
		$stmt->close();
		return array_values($albumList);
	}
}