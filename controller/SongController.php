<?php

require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/Objects/Song.php";
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php";

class SongController
{
	public static function insertSong(Song $song): void
	{
		do {
			$newSongID = rand();
		} while (SongController::IdExists($newSongID));

		$title = $song->getTitle();
		$genre = $song->getGenre();
		$releaseDate = $song->getReleaseDate()->format("Y-m-d");
		$imageName = $song->getImageName();
		$thumbnailName = $song->getThumbnailName();
		$songLength = $song->getSongLength();
		$flacFileName = $song->getFlacFileName();
		$opusFileName = $song->getOpusFileName();

		$stmt = DBConn::getConn()->prepare("INSERT INTO song VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("isssssiss", $newSongID, $title, $genre, $releaseDate, $imageName, $thumbnailName, $songLength, $flacFileName, $opusFileName);
		$stmt->execute();
		$stmt->close();

		$artistsInSong = $song->getArtistIDs();

		for ($i = 0; $i < count($artistsInSong); $i++) {
			$stmt = DBConn::getConn()->prepare("INSERT INTO releases_song VALUES (?, ?, ?)");
			$stmt->bind_param("iii", $artistsInSong[$i], $newSongID, $i);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function IdExists(int $songID): bool
	{
		$stmt = DBConn::getConn()->prepare("SELECT DISTINCT songID FROM song WHERE songID = ? LIMIT 1");
		$stmt->bind_param("i", $songID);
		$stmt->execute();
		$result = $stmt->get_result();

		return $result->num_rows > 0;
	}

	public static function getSongList(string $sortBy = "song.title ASC"): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT song.songID, song.title, artist.name, artist.artistID, song.genre, song.releaseDate, song.imageName, song.thumbnailName, song.songLength, song.flacFilename, song.opusFilename
  		FROM song, artist, releases_song
  		WHERE song.songID = releases_song.songID
  		AND artist.artistID = releases_song.artistID
  		ORDER BY " . $sortBy . ", releases_song.artistIndex;");

		$stmt->execute();
		$result = $stmt->get_result();
		$songList = self::mergeSongRowsToList($result);
		$stmt->close();

		return $songList;
	}

	/**
	 * Helper to merge artists for a song list, indexed by songID.
	 */
	private static function mergeSongRowsToList(mysqli_result $result): array
	{
		$songList = [];
		while ($row = $result->fetch_assoc()) {
			$songID = $row["songID"];
			if (!isset($songList[$songID])) {
				$songList[$songID] = new Song(
					$row["songID"], $row["title"], [$row["name"]], [$row["artistID"]],
					$row["genre"], $row["releaseDate"], $row["songLength"],
					$row["flacFilename"], $row["opusFilename"], $row["imageName"], $row["thumbnailName"]
				);
			} else {
				$songList[$songID]->setArtists(array_merge($songList[$songID]->getArtists(), [$row["name"]]));
				$songList[$songID]->setArtistIDs(array_merge($songList[$songID]->getArtistIDs(), [$row["artistID"]]));
			}
		}
		return array_values($songList);
	}

	public static function getRandomSongs(int $limit = 20): array
	{
		// First, get random song IDs
		$stmt = DBConn::getConn()->prepare("SELECT DISTINCT songID FROM song ORDER BY RAND() LIMIT ?");
		$stmt->bind_param("i", $limit);
		$stmt->execute();
		$result = $stmt->get_result();

		$songIDs = [];
		while ($row = $result->fetch_assoc()) {
			$songIDs[] = $row['songID'];
		}
		$stmt->close();

		if (empty($songIDs)) {
			return [];
		}

		// Then get all data for those songs preserving random order
		$placeholders = str_repeat('?,', count($songIDs) - 1) . '?';
		$orderByCase = "CASE song.songID ";
		for ($i = 0; $i < count($songIDs); $i++) {
			$orderByCase .= "WHEN ? THEN $i ";
		}
		$orderByCase .= "END";

		$stmt = DBConn::getConn()->prepare("SELECT song.songID, song.title, artist.name, artist.artistID, song.genre, song.releaseDate, song.imageName, song.thumbnailName, song.songLength, song.flacFilename, song.opusFilename
    	FROM song, artist, releases_song
   		WHERE song.songID = releases_song.songID
    	AND artist.artistID = releases_song.artistID
    	AND song.songID IN ($placeholders)
    	ORDER BY $orderByCase, releases_song.artistIndex");

		// Bind the song IDs twice - once for IN clause, once for CASE statement
		$allParams = array_merge($songIDs, $songIDs);
		$stmt->bind_param(str_repeat('i', count($allParams)), ...$allParams);
		$stmt->execute();
		$result = $stmt->get_result();

		$songList = self::mergeSongRowsToList($result);

		$stmt->close();

		return $songList;
	}

	public static function getAlbumSongs(int $albumID): array
	{
		$stmt = DBConn::getConn()->prepare("
        SELECT song.songID, song.title, artist.name, artist.artistID, song.genre, 
               song.releaseDate, song.imageName, song.thumbnailName, song.songLength, song.flacFilename, song.opusFilename
        FROM song, artist, releases_song, in_album
        WHERE song.songID = releases_song.songID
        AND artist.artistID = releases_song.artistID
        AND song.songID = in_album.songID
        AND in_album.albumID = ?
        ORDER BY in_album.songIndex, releases_song.artistIndex
        ");

		$stmt->bind_param("i", $albumID);
		$stmt->execute();
		$result = $stmt->get_result();

		$songList = self::mergeSongRowsToList($result);

		$stmt->close();
		return $songList;
	}

	public static function getArtistSongs(int $artistID): array
	{
		$stmt = DBConn::getConn()->prepare("
			SELECT song.songID, song.title, artist.name, artist.artistID, song.genre,
				song.releaseDate, song.imageName, song.thumbnailName, song.songLength, song.flacFilename, song.opusFilename
			FROM song, artist, releases_song
			WHERE song.songID = releases_song.songID
			AND artist.artistID = releases_song.artistID
			AND song.songID IN (
				SELECT DISTINCT songID 
				FROM releases_song 
				WHERE artistID = ?
			)
			ORDER BY song.title, releases_song.artistIndex
    	");

		$stmt->bind_param("i", $artistID);
		$stmt->execute();
		$result = $stmt->get_result();

		$songList = self::mergeSongRowsToList($result);

		$stmt->close();
		return $songList;
	}

	public static function getSongByID(int $songID): ?Song
	{
		$stmt = DBConn::getConn()->prepare("
		SELECT song.songID, song.title, artist.name, artist.artistID, song.genre, 
			   song.releaseDate, song.imageName, song.thumbnailName, song.songLength, song.flacFilename, song.opusFilename
		FROM song, artist, releases_song
		WHERE song.songID = releases_song.songID
		AND artist.artistID = releases_song.artistID
		AND song.songID = ?
		ORDER BY releases_song.artistIndex
		");

		$stmt->bind_param("i", $songID);
		$stmt->execute();
		$result = $stmt->get_result();

		$song = null;
		while ($row = $result->fetch_assoc()) {
			if ($song === null) {
				$song = new Song($row["songID"], $row["title"], array($row["name"]), array($row["artistID"]), $row["genre"], $row["releaseDate"], $row["songLength"], $row["flacFilename"], $row["opusFilename"], $row["imageName"], $row["thumbnailName"]);
			} else {
				$song->setArtists(array_merge($song->getArtists(), array($row["name"])));
				$song->setArtistIDs(array_merge($song->getArtistIDs(), array($row["artistID"])));
			}
		}

		$stmt->close();
		return $song;
	}

	public static function getPlaylistSongs(int $playlistID): array
	{
		$stmt = DBConn::getConn()->prepare("
		SELECT song.songID, song.title, artist.name, artist.artistID, song.genre, 
			   song.releaseDate, song.imageName, song.thumbnailName, song.songLength, song.flacFilename, song.opusFilename
		FROM song, artist, releases_song, in_playlist
		WHERE song.songID = releases_song.songID
		AND artist.artistID = releases_song.artistID
		AND song.songID = in_playlist.songID
		AND in_playlist.playlistID = ?
		ORDER BY in_playlist.songIndex, releases_song.artistIndex
		");

		$stmt->bind_param("i", $playlistID);
		$stmt->execute();
		$result = $stmt->get_result();

		$songList = self::mergeSongRowsToList($result);

		$stmt->close();
		return $songList;
	}

	public static function deleteSong(int $songID): void
	{
		$conn = DBConn::getConn();

		// Get image and audio file names for deletion
		$stmt = $conn->prepare("SELECT imageName, song.thumbnailName, flacFilename, opusFilename FROM song WHERE songID = ?");
		$stmt->bind_param("i", $songID);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_assoc();
		if ($result) {
			try {
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/images/song/large/" . $result['imageName']);
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/images/song/thumbnail/" . $result['thumbnailName']);
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/audio/flac/" . $result['flacFilename']);
				unlink($GLOBALS['PROJECT_ROOT_DIR'] . "/audio/opus/" . $result['opusFilename']);
			} catch (Exception) {
			}
		}
		$stmt->close();

		// Delete from related tables
		$queries = [
			"DELETE FROM in_playlist WHERE songID = ?",
			"DELETE FROM in_album WHERE songID = ?",
			"DELETE FROM releases_song WHERE songID = ?",
			"DELETE FROM song WHERE songID = ?"
		];

		foreach ($queries as $sql) {
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $songID);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function searchSong(string $query): array
	{
		$words = preg_split('/\s+/', trim($query));
		if (empty($words)) return [];

		// Build dynamic WHERE clause
		$where = [];
		$params = [];
		$types = '';
		foreach ($words as $word) {
			$where[] = "(song.title LIKE CONCAT('%', ?, '%') OR artist.name LIKE CONCAT('%', ?, '%'))";
			$params[] = $word;
			$params[] = $word;
			$types .= 'ss';
		}
		$whereClause = implode(' AND ', $where);

		$sql = "
			SELECT DISTINCT song.songID
			FROM song
			JOIN releases_song ON song.songID = releases_song.songID
			JOIN artist ON artist.artistID = releases_song.artistID
			WHERE $whereClause
			ORDER BY song.title
		";

		$stmt = DBConn::getConn()->prepare($sql);
		$stmt->bind_param($types, ...$params);
		$stmt->execute();
		$result = $stmt->get_result();

		$songIDs = [];
		while ($row = $result->fetch_assoc()) {
			$songIDs[] = $row["songID"];
		}
		$stmt->close();

		if (empty($songIDs)) return [];

		// Fetch full song data as before
		$placeholders = implode(',', array_fill(0, count($songIDs), '?'));
		$types2 = str_repeat('i', count($songIDs));
		$stmt = DBConn::getConn()->prepare("
			SELECT song.songID, song.title, artist.name, artist.artistID, song.genre,
				song.releaseDate, song.imageName, song.thumbnailName, song.songLength, song.flacFilename, song.opusFilename
			FROM song
			JOIN releases_song ON song.songID = releases_song.songID
			JOIN artist ON artist.artistID = releases_song.artistID
			WHERE song.songID IN ($placeholders)
			ORDER BY song.title, releases_song.artistIndex
		");
		$stmt->bind_param($types2, ...$songIDs);
		$stmt->execute();
		$result = $stmt->get_result();

		$songList = self::mergeSongRowsToList($result);
		$stmt->close();
		return $songList;
	}
}