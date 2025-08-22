<?php

use Random\RandomException;

include_once("dbConnection.php");
include("Objects/Song.php");
include("Objects/Artist.php");
include("Objects/User.php");
include("Objects/Playlist.php");
include("Objects/Album.php");

class DataController
{
	/**
	 * @throws Exception
	 */
	public static function insertSong(Song $song): void
	{
		$songList = DataController::getSongList();

		$changeMade = false;
		$newSongID = rand();
		do {
			for ($i = 0; $i < count($songList); $i++) {
				if ($newSongID == $songList[$i]->getSongID()) {
					$newSongID = rand();
					$changeMade = true;
				}
			}
		} while ($changeMade == true);

		$sqlSong = "INSERT INTO song
		VALUES (" . $newSongID . ", '" . $song->getTitle() . "', '" . $song->getGenre() . "', '" . $song->getReleaseDate()->format("Y-m-d") . "', '" . $song->getImageName() . "', '" . $song->getSongLength()->format("H:i:s") . "', '" . $song->getFlacFileName() . "')";

		$stmt = DBConn::getConn()->prepare($sqlSong);
		$stmt->execute();
		$stmt->close();

		$artistsInSong = $song->getArtistIDs();

		for ($i = 0; $i < count($artistsInSong); $i++) {
			$stmt = DBConn::getConn()->prepare("INSERT INTO releases_song VALUES (" . $song->getArtistIDs()[$i] . ", " . $newSongID . ", " . $i . ")");
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

	public static function getArtistList(string $sortBy = "artist.name ASC"): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT * FROM artist ORDER BY " . $sortBy . ";");

		$stmt->execute();
		$result = $stmt->get_result();

		$artistList = array();
		while ($row = $result->fetch_assoc()) {
			$artistList[] = new Artist($row["artistID"], $row["name"], $row["imageName"], $row["activeSince"], $row["userID"]);
		}

		$stmt->close();

		return $artistList;
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

	public static function insertArtist(Artist $artist): void
	{
		$artistList = DataController::getArtistList();
		$userList = DataController::getUserList();

		$changeMade = false;
		$newArtistID = rand();
		do {
			for ($i = 0; $i < count($artistList); $i++) {
				if ($newArtistID == $artistList[$i]->getArtistID()) {
					$newArtistID = rand();
					$changeMade = true;
				}
			}
		} while ($changeMade == true);

		$userExists = false;

		for ($i = 0; $i < count($userList); $i++) {
			if ($artist->getUserID() == $userList[$i]->getUserID()) $userExists = true;
		}

		if (!$userExists) return;

		$sqlArtist = "INSERT INTO artist VALUES (" . $newArtistID . ", '" . $artist->getName() . "', '" . $artist->getimageName() . "', '" . $artist->getActiveSince()->format("Y-m-d") . "', '" . $artist->getUserID() . "')";
		$stmt = DBConn::getConn()->prepare($sqlArtist);
		$stmt->execute();
		$stmt = DBConn::getConn()->prepare("UPDATE user SET isArtist = TRUE WHERE userID = " . $artist->getUserID());
		$stmt->execute();
		$stmt->close();
	}

	public static function getUserList(string $sortBy = "user.username ASC"): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT * FROM user ORDER BY " . $sortBy . ";");

		$stmt->execute();
		$result = $stmt->get_result();

		$userList = array();
		while ($row = $result->fetch_assoc()) {
			$userList[] = new User($row["userID"], $row["username"], $row["email"], $row["userPassword"], $row["salt"], $row["isAdmin"], $row["isArtist"], $row["imageName"]);
		}

		$stmt->close();

		return $userList;
	}

	public static function insertUser(User $user): void
	{
		$userList = DataController::getUserList();

		$salt = htmlspecialchars(DataController::generateRandomString(16));
		$password = hash("sha256", $user->getUserPassword() . $salt);

		$changeMade = false;
		$newUserID = rand();
		do {
			for ($i = 0; $i < count($userList); $i++) {
				if ($newUserID == $userList[$i]->getUserID()) {
					$newUserID = rand();
					$changeMade = true;
				}
			}
		} while ($changeMade == true);

		$sqlUser = "INSERT INTO user VALUES (" . $newUserID . ", '" . $user->getUsername() . "', '" . $user->getEmail() . "', '" . $password . "', '" . $salt . "', " . ($user->isAdmin() ? 'TRUE' : 'FALSE') . ", FALSE, '" . $user->getimageName() . "')";
		$stmt = DBConn::getConn()->prepare($sqlUser);
		$stmt->execute();
		$stmt->close();
	}

	public
	static function generateRandomString(int $length = 10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!?,.:;()<>$#&*+-/=@%'): string
	{
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
			try {
				$randomString .= $characters[random_int(0, $charactersLength - 1)];
			} catch (RandomException) {
				return '';
			}
		}
		return $randomString;
	}

	public static function insertPlaylist(Playlist $playlist): void
	{
		$playlistList = DataController::getPlaylistList();

		$changeMade = false;
		$newPlaylistID = rand();
		do {
			for ($i = 0; $i < count($playlistList); $i++) {
				if ($newPlaylistID == $playlistList[$i]->getPlaylistID()) {
					$newPlaylistID = rand();
					$changeMade = true;
				}
			}
		} while ($changeMade == true);

		$sqlPlaylist = "INSERT INTO playlist VALUES (" . $newPlaylistID . ", '" . $playlist->getimageName() . "', '" . $playlist->getName() . "', '" . $playlist->getLength() . "', '" . $playlist->getDuration()->format("H:i:s") . "', '" . $playlist->getCreatorID() . "')";
		$stmt = DBConn::getConn()->prepare($sqlPlaylist);
		$stmt->execute();
		for ($i = 0; $i < count($playlist->getSongIDs()); $i++) {
			$sqlInPlaylist = "INSERT INTO in_playlist (playlistID, songID, songIndex) VALUES (" . $newPlaylistID . ", " . $playlist->getSongIDs()[$i] . ", " . $i . ")";
			$stmt = DBConn::getConn()->prepare($sqlInPlaylist);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function getPlaylistList(string $sortBy = "playlist.name ASC"): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT playlist.playlistID, playlist.imageName, name, length, duration, creatorID, song.songID, songIndex
		FROM playlist, in_playlist, song
		WHERE song.songID = in_playlist.songID
  		AND playlist.playlistID = in_playlist.playlistID
		ORDER BY " . $sortBy . ", in_playlist.songIndex;");

		$stmt->execute();
		$result = $stmt->get_result();

		$playlistList = array();
		while ($row = $result->fetch_assoc()) {
			$alreadyExists = false;
			$newPlaylist = new Playlist($row["playlistID"], $row["name"], array($row["songID"]), $row["duration"], $row["length"], $row['imageName'], $row["creatorID"]);

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

	public static function insertAlbum(Album $album): void
	{
		$albumList = DataController::getAlbumList();
		$artistList = DataController::getArtistList();

		$changeMade = false;
		$newAlbumID = rand();
		do {
			for ($i = 0; $i < count($albumList); $i++) {
				if ($newAlbumID == $albumList[$i]->getAlbumID()) {
					$newAlbumID = rand();
					$changeMade = true;
				}
			}
		} while ($changeMade == true);

		$sqlAlbum = "INSERT INTO album VALUES (" . $newAlbumID . ", '" . $album->getName() . "', '" . $album->getImageName() . "', '" . $album->getLength() . "', '" . $album->getDuration()->format("H:i:s") . "')";
		$stmt = DBConn::getConn()->prepare($sqlAlbum);
		$stmt->execute();
		$stmt->close();

		$artistsInAlbum = $album->getArtists();

		for ($j = 0; $j < count($artistsInAlbum); $j++) {
			for ($i = 0; $i < count($artistList); $i++) {
				if ($artistsInAlbum[$j] == $artistList[$i]->getName()) {
					$stmt = DBConn::getConn()->prepare("INSERT INTO releases_album VALUES (" . $artistList[$i]->getArtistID() . ", " . $newAlbumID . ")");
					$stmt->execute();
					$stmt->close();
				}
			}
		}
		for ($i = 0; $i < count($album->getSongIDs()); $i++) {
			$stmt = DBConn::getConn()->prepare("INSERT INTO in_album (songID, albumID, songIndex) VALUES (?, ?, ?)");
			$stmt->bind_param("iii", $album->getSongIDs()[$i], $newAlbumID, $i);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function getAlbumList(string $sortBy = "album.title ASC"): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT album.albumID, title, name, album.imageName, length, duration
		FROM album, artist, releases_album
		WHERE releases_album.artistID = artist.artistID
		AND album.albumID = releases_album.albumID
		ORDER BY " . $sortBy . ";");

		$stmt->execute();
		$result = $stmt->get_result();

		$albumList = array();
		while ($row = $result->fetch_assoc()) {
			$newAlbum = new Album($row["albumID"], $row["title"], array(), array($row["name"]), $row["imageName"], $row["length"], $row["duration"]);
			$alreadyExists = false;

			for ($i = 0; $i < count($albumList); $i++) {
				if ($albumList[$i]->getAlbumID() == $newAlbum->getAlbumID()) {
					$alreadyExists = true;
					$albumList[$i]->setArtists(array_merge($albumList[$i]->getArtists(), $newAlbum->getArtists()));
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

	public static function getAlbumByID(int $albumID): ?Album
	{
		// Get album basic info and artists
		$stmt = DBConn::getConn()->prepare("
        SELECT album.albumID, title, name, album.imageName, length, duration
        FROM album, artist, releases_album
        WHERE releases_album.artistID = artist.artistID
        AND album.albumID = releases_album.albumID
        AND album.albumID = ?
    ");

		$stmt->bind_param("i", $albumID);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			$stmt->close();
			return null;
		}

		$artists = array();
		$albumData = null;

		while ($row = $result->fetch_assoc()) {
			if (!$albumData) {
				$albumData = $row;
			}
			$artists[] = $row["name"];
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

		return new Album($albumData["albumID"], $albumData["title"], $songIDs, $artists, $albumData["imageName"], $albumData["length"], $albumData["duration"]);
	}

	public static function getRandomAlbums(int $limit = 3): array
	{
		// First get random album IDs
		$stmt = DBConn::getConn()->prepare("
        SELECT DISTINCT album.albumID 
        FROM album 
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
        SELECT album.albumID, title, name, album.imageName, length, duration
        FROM album, artist, releases_album
        WHERE releases_album.artistID = artist.artistID
        AND album.albumID = releases_album.albumID
        AND album.albumID IN ($placeholders)
        ORDER BY album.albumID
    ");
		$stmt->bind_param(str_repeat('i', count($albumIDs)), ...$albumIDs);
		$stmt->execute();
		$result = $stmt->get_result();

		$albumList = array();
		while ($row = $result->fetch_assoc()) {
			$newAlbum = new Album($row["albumID"], $row["title"], array(), array($row["name"]), $row["imageName"], $row["length"], $row["duration"]);
			$alreadyExists = false;

			for ($i = 0; $i < count($albumList); $i++) {
				if ($albumList[$i]->getAlbumID() == $newAlbum->getAlbumID()) {
					$alreadyExists = true;
					$albumList[$i]->setArtists(array_merge($albumList[$i]->getArtists(), $newAlbum->getArtists()));
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
		$deleteImage = $conn->prepare("SELECT imageName FROM album WHERE albumID = $albumID");
		$deleteImage->execute();

		$result = $deleteImage->get_result()->fetch_assoc();
		if ($result) {

			try {
				unlink($_SERVER["DOCUMENT_ROOT"] . "/BeatStream/images/album/" . $deleteImage->get_result()->fetch_assoc()['imageName']);
			} catch (Exception) {
			}
		}

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

	public static function deletePlaylist(int $playlistID): void
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imageName FROM playlist WHERE playlistID = $playlistID");
		$deleteImage->execute();
		try {
			unlink($_SERVER["DOCUMENT_ROOT"] . "/BeatStream/images/playlist/" . $deleteImage->get_result()->fetch_assoc()['imageName']);
		} catch (Exception) {
		}

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

	public static function deleteUser(int $userID): void
	{
		$conn = DBConn::getConn();
		$stmt = $conn->prepare("SELECT imageName FROM user WHERE userID = ?");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_assoc();

		if ($result['imageName']) {
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

		// Delete all relations and the songs themselves
		if ($artistID !== null) {
			DataController::deleteArtist($artistID);
		}

		$stmt = $conn->prepare("DELETE FROM user WHERE userID = ?;");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$stmt->close();
	}

	public static function deleteArtist(int $artistID): void
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imageName FROM artist WHERE artistID = $artistID");
		$deleteImage->execute();
		try {
			unlink($_SERVER["DOCUMENT_ROOT"] . "/BeatStream/images/artist/" . $deleteImage->get_result()->fetch_assoc()['imageName']);
		} catch (Exception) {
		}

		$stmt = $conn->prepare("SELECT songID FROM releases_song WHERE artistID = ?");
		$stmt->bind_param("i", $artistID);
		$stmt->execute();
		$result = $stmt->get_result();
		while ($row = $result->fetch_assoc()) {
			DataController::deleteSong($row['songID']);
		}
		$stmt->close();

		// Delete releases_song, albums, etc.
		$queries = [
			"DELETE FROM in_album WHERE albumID IN (SELECT albumID FROM releases_album WHERE artistID = ?);",
			"DELETE FROM releases_album WHERE artistID = ?;",
			"DELETE FROM album WHERE albumID IN (SELECT albumID FROM releases_album WHERE artistID = ?);",
			"UPDATE user SET isArtist = FALSE WHERE userID = (SELECT userID FROM artist WHERE artistID = ?);",
			"DELETE FROM artist WHERE artistID = ?;"
		];
		foreach ($queries as $sql) {
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $artistID);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function deleteSong(int $songID): void
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imageName, fileName FROM song WHERE songID = ?");
		$deleteImage->bind_param("i", $songID);
		$deleteImage->execute();
		$result = $deleteImage->get_result()->fetch_assoc();
		try {
			unlink($_SERVER["DOCUMENT_ROOT"] . "/BeatStream/images/song/" . $result['imageName']);
			unlink($_SERVER["DOCUMENT_ROOT"] . "/BeatStream/audio/" . $result['fileName']);
		} catch (Exception) {
		}

		$queries = [
			"DELETE FROM releases_song WHERE releases_song.songID=?",
			"DELETE FROM in_album WHERE in_album.songID=?",
			"DELETE FROM in_playlist WHERE in_playlist.songID=?",
			"DELETE FROM song WHERE song.songID=?"
		];

		foreach ($queries as $sql) {
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $songID);
			$stmt->execute();
			$stmt->close();
		}
	}
}