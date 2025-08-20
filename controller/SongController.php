<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/BeatStream/Objects/Song.php";

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
		$songLength = $song->getSongLength()->format("H:i:s");
		$fileName = $song->getFileName();

		$stmt = DBConn::getConn()->prepare("INSERT INTO song VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("issssss", $newSongID, $title, $genre, $releaseDate, $imageName, $songLength, $fileName);
		$stmt->execute();
		$stmt->close();

		$artistsInSong = $song->getArtistIDs();

		for ($i = 0; $i < count($artistsInSong); $i++) {
			$stmt = DBConn::getConn()->prepare("INSERT INTO releases_song VALUES (?, ?, ?)");
			$stmt->bind_param("iii", $newSongID, $artistsInSong[$i], $i);
			$stmt->execute();
			$stmt->close();
		}
	}

	/**
	 * @throws Exception
	 */
	public static function getSongList(string $sortBy = "song.title ASC"): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT song.songID, song.title, artist.name, artist.artistID, song.genre, song.releaseDate, song.imageName, song.songLength, song.fileName
  		FROM song, artist, releases_song
  		WHERE song.songID = releases_song.songID
  		AND artist.artistID = releases_song.artistID
  		ORDER BY " . $sortBy . ", releases_song.artistIndex;");

		$stmt->execute();
		$result = $stmt->get_result();

		$songList = array();
		while ($row = $result->fetch_assoc()) {
			$newSong = new Song($row["songID"], $row["title"], array($row["name"]), array($row["artistID"]), $row["genre"], $row["releaseDate"], $row["songLength"], $row["fileName"], $row["imageName"]);
			$alreadyExists = false;

			for ($i = 0; $i < count($songList); $i++) {
				if ($songList[$i]->getSongID() == $newSong->getSongID()) {
					$alreadyExists = true;
					$songList[$i]->setArtists(array_merge($songList[$i]->getArtists(), $newSong->getArtists()));
					$songList[$i]->setArtistIDs(array_merge($songList[$i]->getArtistIDs(), $newSong->getArtistIDs()));
				}
			}
			if (!$alreadyExists) $songList[] = $newSong;
		}
		$stmt->close();

		return $songList;
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

		// Then get all data for those songs with proper artist ordering
		$placeholders = str_repeat('?,', count($songIDs) - 1) . '?';
		$stmt = DBConn::getConn()->prepare("SELECT song.songID, song.title, artist.name, artist.artistID, song.genre, song.releaseDate, song.imageName, song.songLength, song.fileName
        FROM song, artist, releases_song
        WHERE song.songID = releases_song.songID
        AND artist.artistID = releases_song.artistID
        AND song.songID IN ($placeholders)
        ORDER BY song.songID, releases_song.artistIndex");

		$stmt->bind_param(str_repeat('i', count($songIDs)), ...$songIDs);
		$stmt->execute();
		$result = $stmt->get_result();

		$songList = array();
		while ($row = $result->fetch_assoc()) {
			$newSong = new Song($row["songID"], $row["title"], array($row["name"]), array($row["artistID"]), $row["genre"], $row["releaseDate"], $row["songLength"], $row["fileName"], $row["imageName"]);
			$alreadyExists = false;

			for ($i = 0; $i < count($songList); $i++) {
				if ($songList[$i]->getSongID() == $newSong->getSongID()) {
					$alreadyExists = true;
					$songList[$i]->setArtists(array_merge($songList[$i]->getArtists(), $newSong->getArtists()));
				}
			}
			if (!$alreadyExists) {
				$songList[] = $newSong;
			}
		}
		$stmt->close();

		return $songList;
	}

	public static function getAlbumSongs(int $albumID): array
	{
		$stmt = DBConn::getConn()->prepare("
        SELECT song.songID, song.title, artist.name, artist.artistID, song.genre, 
               song.releaseDate, song.imageName, song.songLength, song.fileName
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

		$songList = array();
		while ($row = $result->fetch_assoc()) {
			$newSong = new Song($row["songID"], $row["title"], array($row["name"]), array($row["artistID"]), $row["genre"], $row["releaseDate"], $row["songLength"], $row["fileName"], $row["imageName"]);

			$alreadyExists = false;
			for ($i = 0; $i < count($songList); $i++) {
				if ($songList[$i]->getSongID() == $newSong->getSongID()) {
					$alreadyExists = true;
					$songList[$i]->setArtists(array_merge($songList[$i]->getArtists(), $newSong->getArtists()));
					$songList[$i]->setArtistIDs(array_merge($songList[$i]->getArtistIDs(), $newSong->getArtistIDs()));
					break;
				}
			}

			if (!$alreadyExists) {
				$songList[] = $newSong;
			}
		}

		$stmt->close();
		return $songList;
	}

	public static function IdExists(int $songID): bool {
		$stmt = DBConn::getConn()->prepare("SELECT DISTINCT songID FROM song WHERE songID = ? LIMIT 1");
		$stmt->bind_param("i", $songID);
		$stmt->execute();
		$result = $stmt->get_result();

		return $result->num_rows > 0;
	}
}