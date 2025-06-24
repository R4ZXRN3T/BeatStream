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
	public static function getSongList(string $sortBy = "song.title ASC"): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT song.songID, song.title, artist.name, song.genre, song.releaseDate, song.imageName, song.songLength, song.fileName
		FROM song, artist, releases_song
		WHERE song.songID = releases_song.songID
		AND artist.artistID = releases_song.artistID
		ORDER BY " . $sortBy . ";");

		$stmt->execute();
		$result = $stmt->get_result();

		$songList = array();
		while ($row = $result->fetch_assoc()) {
			$newSong = new Song($row["songID"], $row["title"], $row["name"], $row["genre"], $row["releaseDate"], $row["songLength"], $row["fileName"], $row["imageName"]);
			$alreadyExists = false;

			for ($i = 0; $i < count($songList); $i++) {
				if ($songList[$i]->getSongID() == $newSong->getSongID()) {
					$alreadyExists = true;
					$songList[$i]->setArtists($songList[$i]->getArtists() . ", " . $newSong->getArtists());
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
			$newAlbum = new Album($row["albumID"], $row["title"], array(), $row["name"], $row["imageName"], $row["length"], $row["duration"]);
			$alreadyExists = false;

			for ($i = 0; $i < count($albumList); $i++) {
				if ($albumList[$i]->getAlbumID() == $newAlbum->getAlbumID()) {
					$alreadyExists = true;
					$albumList[$i]->setArtists($albumList[$i]->getArtists() . ", " . $newAlbum->getArtists());
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
					// Replace the existing songIDs with the properly ordered ones
					$albumList[$i]->setSongIDs($songs);
					break;
				}
			}
		}
		$stmt->close();

		return $albumList;
	}

	/**
	 * @throws Exception
	 */
	public static function insertSong(Song $song): void
	{
		$song->setAll(
			$song->getSongID(),
			str_replace("'", "\'", $song->getTitle()),
			str_replace("'", "\'", $song->getArtists()),
			str_replace("'", "\'", $song->getGenre()),
			$song->getReleaseDate(),
			$song->getSongLength(),
			str_replace("'", "\'", $song->getfileName()),
			str_replace("'", "\'", $song->getimageName()),
		);

		$songList = DataController::getSongList();
		$artistList = DataController::getArtistList();

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
		VALUES (" . $newSongID . ", '" . $song->getTitle() . "', '" . $song->getGenre() . "', '" . $song->getReleaseDate()->format("Y-m-d") . "', '" . $song->getimageName() . "', '" . $song->getSongLength()->format("H:i:s") . "', '" . $song->getfileName() . "')";

		$stmt = DBConn::getConn()->prepare($sqlSong);
		$stmt->execute();
		$stmt->close();

		$artistsInSong = explode(", ", $song->getArtists());

		for ($j = 0; $j < count($artistsInSong); $j++) {

			for ($i = 0; $i < count($artistList); $i++) {

				if ($artistsInSong[$j] == $artistList[$i]->getName()) {

					$stmt = DBConn::getConn()->prepare("INSERT INTO releases_song VALUES (" . $artistList[$i]->getArtistID() . ", " . $newSongID . ")");
					$stmt->execute();
					$stmt->close();
				}
			}
		}
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

	public static function insertUser(User $user): void
	{
		$userList = DataController::getUserList();

		$salt = DataController::generateRandomString(16);
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

		// Change this line in DataController.php insertUser method
		$sqlUser = "INSERT INTO user VALUES (" . $newUserID . ", '" . $user->getUsername() . "', '" . $user->getEmail() . "', '" . $password . "', '" . $salt . "', " . ($user->isAdmin() ? 'TRUE' : 'FALSE') . ", FALSE, '" . $user->getimageName() . "')";
		$stmt = DBConn::getConn()->prepare($sqlUser);
		$stmt->execute();
		$stmt->close();
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

		$sqlAlbum = "INSERT INTO album VALUES (" . $newAlbumID . ", '" . $album->getName() . "', '" . $album->getimageName() . "', '" . $album->getLength() . "', '" . $album->getDuration()->format("H:i:s") . "')";
		$stmt = DBConn::getConn()->prepare($sqlAlbum);
		$stmt->execute();
		$stmt->close();

		$artistsInAlbum = explode(", ", $album->getArtists());

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

		} catch (Exception $e) {
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

	public static function deleteAlbum(int $albumID): void
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imageName FROM album WHERE albumID = $albumID");
		$deleteImage->execute();
		try {
			unlink($_SERVER["DOCUMENT_ROOT"] . "/BeatStream/images/album/" . $deleteImage->get_result()->fetch_assoc()['imageName']);
		} catch (Exception $e) {
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
		} catch (Exception $e) {
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

	public static function deleteArtist(int $artistID): void
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imageName FROM artist WHERE artistID = $artistID");
		$deleteImage->execute();
		try {
			unlink($_SERVER["DOCUMENT_ROOT"] . "/BeatStream/images/artist/" . $deleteImage->get_result()->fetch_assoc()['imageName']);
		} catch (Exception $e) {
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
			"DELETE FROM album WHERE albumID IN (SELECT albumID FROM releases_album WHERE artistID = ?);",
			"DELETE FROM in_album WHERE albumID IN (SELECT albumID FROM releases_album WHERE artistID = ?);",
			"DELETE FROM releases_album WHERE artistID = ?;",
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

	public static function deleteUser(int $userID): void
	{
		$conn = DBConn::getConn();
		$stmt = $conn->prepare("SELECT imageName FROM user WHERE userID = ?");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_assoc();

		try {
			unlink($_SERVER["DOCUMENT_ROOT"] . "/BeatStream/images/user/" . $result['imageName']);
		} catch (Exception $e) {
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

	public
	static function generateRandomString(int $length = 10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!?,.:;()<>$#&*+-/=@%'): string
	{
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
			try {
				$randomString .= $characters[random_int(0, $charactersLength - 1)];
			} catch (RandomException $e) {
				return '';
			}
		}
		return $randomString;
	}
}

/*INSERT INTO User VALUES (12345, "user1", "email1", "password1", "imageName1");
INSERT INTO User VALUES (123456, "user2", "email2", "password2", "imageName2");

INSERT INTO Artist VALUES (12345, "artist1", "imageName3", 1, '2025-05-10', 12345);
INSERT INTO Artist VALUES (123456, "artist2", "imageName4", 1, '2025-05-10', 123456);

INSERT INTO Song VALUES (0000, "song1", "genre1", '2025-05-09', "imageName5", 4.5, '00:50:50', "fileName");

INSERT INTO ReleasesSong VALUES (12345, 0000);
INSERT INTO ReleasesSong VALUES (123456, 0000);


INSERT INTO song VALUES (0001, "Midnight Dreams", "Pop", '2025-05-09', "", '03:15:12', "song.mp3");
INSERT INTO song VALUES (0002, "Echoes of Silence", "Rock", '2025-05-09', "", '04:05:45', "song.mp3");
INSERT INTO song VALUES (0003, "Chasing Stars", "EDM", '2025-05-09', "", '02:45:25', "song.mp3");
INSERT INTO song VALUES (0004, "Whispers in the Dark", "R&B", '2025-05-09', "", '03:30:35', "song.mp3");
INSERT INTO song VALUES (0005, "Heartbreaker", "Pop", '2025-05-09', "", '03:20:10', "song.mp3");
INSERT INTO song VALUES (0006, "Luminous Sky", "Indie", '2025-05-09', "", '03:10:50', "");
INSERT INTO song VALUES (0007, "Violet Horizon", "Alternative", '2025-05-09', "", '02:55:30', "song.mp3");
INSERT INTO song VALUES (0008, "On the Edge", "Rock", '2025-05-09', "", '03:50:20', "song.mp3");
INSERT INTO song VALUES (0009, "Rising Sun", "Pop", '2025-05-09', "", '03:05:15', "song.mp3");
INSERT INTO song VALUES (0010, "In the Silence", "Classical", '2025-05-09', "", '04:00:10', "song.mp3");
INSERT INTO song VALUES (0011, "Fading Light", "Alternative", '2025-05-09', "", '03:35:40', "song.mp3");
INSERT INTO song VALUES (0012, "Lost in Time", "EDM", '2025-05-09', "", '02:50:55', "song.mp3");
INSERT INTO song VALUES (0013, "Serenity", "Jazz", '2025-05-09', "", '03:40:25', "song.mp3");
INSERT INTO song VALUES (0014, "Cosmic Waves", "Pop", '2025-05-09', "", '03:00:05', "song.mp3");
INSERT INTO song VALUES (0015, "Storm Inside", "Rock", '2025-05-09', "", '04:10:15', "song.mp3");
INSERT INTO song VALUES (0016, "Silent Rain", "Indie", '2025-05-09', "", '02:35:45', "song.mp3");
INSERT INTO song VALUES (0017, "Reckless Love", "R&B", '2025-05-09', "", '03:25:10', "song.mp3");
INSERT INTO song VALUES (0018, "Golden Horizon", "Country", '2025-05-09', "", '03:15:20', "song.mp3");
INSERT INTO song VALUES (0019, "Reflections", "Electronic", '2025-05-09', "", '03:45:05', "song.mp3");
INSERT INTO song VALUES (0020, "Into the Wild", "Rock", '2025-05-09', "", '04:30:25', "song.mp3");

INSERT INTO user VALUES (0001, "john_doe", "john.doe@example.com", "password123", "salt", FALSE, TRUE, "");
INSERT INTO user VALUES (0002, "sara_smith", "sara.smith@example.com", "securePass456", "salt", FALSE, TRUE, "");
INSERT INTO user VALUES (0003, "alex_lee", "alex.lee@example.com", "alexPass789", "salt", FALSE, TRUE, "");
INSERT INTO user VALUES (0004, "emily_jones", "emily.jones@example.com", "emilySecret101", "salt", FALSE, TRUE, "");
INSERT INTO user VALUES (0005, "michael_brown", "michael.brown@example.com", "mikePass202", "salt", FALSE, TRUE, "");
INSERT INTO user VALUES (0006, "laura_wilson", "laura.wilson@example.com", "laura1234", "salt", FALSE, TRUE, "");
INSERT INTO user VALUES (0007, "daniel_white", "daniel.white@example.com", "danielPass567", "salt", FALSE, TRUE, "");
INSERT INTO user VALUES (0008, "lisa_clark", "lisa.clark@example.com", "lisaSecure890", "salt", FALSE, TRUE, "");
INSERT INTO user VALUES (0009, "james_harris", "james.harris@example.com", "james2021", "salt", FALSE, TRUE, "");
INSERT INTO user VALUES (0010, "olivia_martin", "olivia.martin@example.com", "oliviaPass345", "salt", FALSE, TRUE, "");

INSERT INTO artist VALUES (12345, "The Midnight Echo", "", '2025-05-10', 0001);
INSERT INTO artist VALUES (12346, "Nova Sparks", "", '2025-05-10', 0002);
INSERT INTO artist VALUES (12347, "Luna Waves", "", '2025-05-10', 0003);
INSERT INTO artist VALUES (12348, "Echo Runners", "", '2025-05-10', 0004);
INSERT INTO artist VALUES (12349, "Skyline Dreams", "", '2025-05-10', 0005);
INSERT INTO artist VALUES (12350, "Electric Vibe", "", '2025-05-10', 0006);
INSERT INTO artist VALUES (12351, "Wanderlust Sounds", "", '2025-05-10', 0007);
INSERT INTO artist VALUES (12352, "Silent Mirage", "", '2025-05-10', 0008);
INSERT INTO artist VALUES (12353, "Stellar Bloom", "", '2025-05-10', 0009);
INSERT INTO artist VALUES (12354, "Violet Horizon", "", '2025-05-10', 0010);

INSERT INTO releases_song VALUES (12345, 0001);
INSERT INTO releases_song VALUES (12345, 0002);
INSERT INTO releases_song VALUES (12346, 0003);
INSERT INTO releases_song VALUES (12346, 0004);
INSERT INTO releases_song VALUES (12347, 0005);
INSERT INTO releases_song VALUES (12347, 0006);
INSERT INTO releases_song VALUES (12348, 0007);
INSERT INTO releases_song VALUES (12348, 0008);
INSERT INTO releases_song VALUES (12349, 0009);
INSERT INTO releases_song VALUES (12349, 0010);
INSERT INTO releases_song VALUES (12350, 0011);
INSERT INTO releases_song VALUES (12350, 0012);
INSERT INTO releases_song VALUES (12351, 0013);
INSERT INTO releases_song VALUES (12351, 0014);
INSERT INTO releases_song VALUES (12352, 0015);
INSERT INTO releases_song VALUES (12352, 0016);
INSERT INTO releases_song VALUES (12353, 0017);
INSERT INTO releases_song VALUES (12353, 0018);
INSERT INTO releases_song VALUES (12354, 0019);
INSERT INTO releases_song VALUES (12354, 0020);
*/