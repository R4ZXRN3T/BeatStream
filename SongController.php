<?php

use Random\RandomException;

include_once("dbConnection.php");
include("Objects/Song.php");
include("Objects/Artist.php");
include("Objects/User.php");

class SongController
{
	/**
	 * @throws Exception
	 */
	public static function getSongList(): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT song.songID, song.title, artist.name, song.genre, song.releaseDate, song.imagePath, song.rating, song.songLength, song.filePath
		FROM song, artist, releases_song
		WHERE song.songID = releases_song.songID
		AND artist.artistID = releases_song.artistID;");

		$stmt->execute();
		$result = $stmt->get_result();

		$songListe = array();
		while ($row = $result->fetch_assoc()) {
			$newSong = new Song($row["songID"], $row["title"], $row["name"], $row["genre"], $row["releaseDate"], $row["rating"], $row["songLength"], $row["filePath"], $row["imagePath"]);
			$alreadyExists = false;

			for ($i = 0; $i < count($songListe); $i++) {
				if ($songListe[$i]->getSongID() == $newSong->getSongID()) {
					$alreadyExists = true;
					$songListe[$i]->setArtists($songListe[$i]->getArtists() . ", " . $newSong->getArtists());
				}
			}
			if (!$alreadyExists) $songListe[] = $newSong;
		}
		$stmt->close();

		return $songListe;
	}

	public static function getArtistList(): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT * FROM artist");

		$stmt->execute();
		$result = $stmt->get_result();

		$artistList = array();
		while ($row = $result->fetch_assoc()) {
			$artistList[] = new Artist($row["artistID"], $row["name"], $row["imagePath"], $row["follower"], $row["activeSince"], $row["userID"]);
		}

		$stmt->close();

		return $artistList;
	}

	public static function getUserList(): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT * FROM user;");

		$stmt->execute();
		$result = $stmt->get_result();

		$userList = array();
		while ($row = $result->fetch_assoc()) {
			$userList[] = new User($row["userID"], $row["username"], $row["email"], $row["userPassword"], $row["salt"], $row["imagePath"]);
		}

		$stmt->close();

		return $userList;
	}

	public static function getPlaylistList(): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT * FROM playlist;");

		$stmt->execute();
		$result = $stmt->get_result();

		$playlistList = array();
		while ($row = $result->fetch_assoc()) {
			$playlistList[] = new Playlist($row["playlistID"], $row["imagePath"], $row["name"], $row["duration"], $row["length"], $row["creatorID"]);
		}

		$stmt->close();

		return $playlistList;
	}

	public static function getAlbumList(): array
	{
		$stmt = DBConn::getConn()->prepare("SELECT album.albumID, title, name, album.imagePath, length, duration
		FROM album, artist, releases_album
		WHERE releases_album.artistID = artist.artistID
		AND album.albumID = releases_album.albumID;");

		$stmt->execute();
		$result = $stmt->get_result();

		$albumList = array();
		while ($row = $result->fetch_assoc()) {
			$newAlbum = new Album($row["albumID"], $row["title"], $row["name"], $row["imagePath"], $row["length"], $row["duration"]);
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

		return $albumList;
	}

	/**
	 * @throws Exception
	 */
	public static function insertSong(Song $song)
	{
		$song->setAll(
			$song->getSongID(),
			str_replace("'", "\'", $song->getTitle()),
			str_replace("'", "\'", $song->getArtists()),
			str_replace("'", "\'", $song->getGenre()),
			$song->getReleaseDate(),
			str_replace("'", "\'", $song->getRating()),
			$song->getSongLength(),
			str_replace("'", "\'", $song->getFilePath()),
			str_replace("'", "\'", $song->getImagePath()),

		);

		$songList = SongController::getSongList();
		$artistList = SongController::getArtistList();

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

		$sqlSong = "INSERT INTO song (songID, title, genre, releaseDate, imagePath, rating, songLength, filePath) 
		VALUES (" . $newSongID . ", '" . $song->getTitle() . "', '" . $song->getGenre() . "', '" . $song->getReleaseDate()->format("Y-m-d") . "', '" . $song->getImagePath() . "', '" . $song->getRating() . "', '" . $song->getSongLength()->format("H:i:s") . "', '" . $song->getFilePath() . "')";

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

	public static function insertArtist(Artist $artist)
	{
		$artistList = SongController::getArtistList();
		$userList = SongController::getUserList();

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

		$sqlArtist = "INSERT INTO artist VALUES (" . $newArtistID . ", '" . $artist->getName() . "', '" . $artist->getImagePath() . "', '" . $artist->getFollower() . "', '" . $artist->getActiveSince()->format("Y-m-d") . "', '" . $artist->getUserID() . "')";
		$stmt = DBConn::getConn()->prepare($sqlArtist);
		$stmt->execute();
		$stmt->close();
	}

	public static function insertUser(User $user)
	{
		$userList = SongController::getUserList();

		$salt = SongController::generateRandomString(16);
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

		$sqlUser = "INSERT INTO user VALUES (" . $newUserID . ", '" . $user->getUsername() . "', '" . $user->getEmail() . "', '" . $password . "', '" . $salt . "', '" . $user->getImagePath() . "')";
		$stmt = DBConn::getConn()->prepare($sqlUser);
		$stmt->execute();
		$stmt->close();
	}

	public static function insertPlaylist(Playlist $playlist)
	{
		$playlistList = SongController::getPlaylistList();

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

		$sqlPlaylist = "INSERT INTO playlist VALUES (" . $newPlaylistID . ", '" . $playlist->getImagePath() . "', '" . $playlist->getName() . "', '" . $playlist->getLength() . "', '" . $playlist->getDuration() . "', '" . $playlist->getCreatorID() . "')";
		$stmt = DBConn::getConn()->prepare($sqlPlaylist);
		$stmt->execute();
		$stmt->close();
	}

	public static function insertAlbum(Album $album)
	{
		$albumList = SongController::getAlbumList();
		$artistList = SongController::getArtistList();

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

		$sqlAlbum = "INSERT INTO album VALUES (" . $newAlbumID . ", '" . $album->getName() . "', '" . $album->getImagePath() . "', '" . $album->getLength() . "', '" . $album->getDuration() . "')";
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
	}

	public static function deleteSong(int $songID)
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imagePath FROM song WHERE songID = $songID");
		$deleteImage->execute();
		try {
			unlink("images/songs/" . $deleteImage->get_result()->fetch_assoc()['imagePath']);
		} catch (Exception $e) {}

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

	public static function deleteAlbum(int $albumID)
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imagePath FROM album WHERE albumID = $albumID");
		$deleteImage->execute();
		try {
			unlink("images/albums/" . $deleteImage->get_result()->fetch_assoc()['imagePath']);
		} catch (Exception $e) {}

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

	public static function deletePlaylist(int $playlistID)
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imagePath FROM playlist WHERE playlistID = $playlistID");
		$deleteImage->execute();
		try {
			unlink("images/playlists/" . $deleteImage->get_result()->fetch_assoc()['imagePath']);
		} catch (Exception $e) {}

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

	public static function deleteArtist(int $artistID)
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imagePath FROM artist WHERE artistID = $artistID");
		$deleteImage->execute();
		try {
			unlink("images/artists/" . $deleteImage->get_result()->fetch_assoc()['imagePath']);
		} catch (Exception $e) {}

		$queries = [
			"DELETE FROM releases_song WHERE artistID = ?;",
			"DELETE FROM in_album WHERE songID IN (
			    SELECT songID FROM releases_song WHERE artistID = ?);",
			"DELETE FROM in_playlist WHERE songID IN (
			    SELECT songID FROM releases_song WHERE artistID = ?);",
			"DELETE FROM song WHERE songID IN (
			    SELECT songID FROM releases_song WHERE artistID = ?);",
			"DELETE FROM releases_album WHERE artistID = ?;",
			"DELETE FROM in_album WHERE albumID IN (
			    SELECT albumID FROM releases_album WHERE artistID = ?);",
			"DELETE FROM album WHERE albumID IN (
			    SELECT albumID FROM releases_album WHERE artistID = ?);",
			"DELETE FROM artist WHERE artist.artistID=?"
		];

		foreach ($queries as $sql) {
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $artistID);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function deleteUser(int $userID)
	{
		$conn = DBConn::getConn();
		$deleteImage = $conn->prepare("SELECT imagePath FROM user WHERE userID = $userID");
		$deleteImage->execute();
		try {
			unlink("images/users/" . $deleteImage->get_result()->fetch_assoc()['imagePath']);
		} catch (Exception $e) {}

		$queries = [
			// 1. Delete all playlists created by the user (and their songs in playlists)
			"DELETE FROM in_playlist WHERE playlistID IN (SELECT playlistID FROM playlist WHERE creatorID = ?);",
			"DELETE FROM playlist WHERE creatorID = ?;",

			// 2. Delete all songs by the artist (and their relations)
			"DELETE FROM releases_song WHERE artistID IN (SELECT artistID FROM artist WHERE userID = ?);",
			"DELETE FROM in_album WHERE songID IN (
			    SELECT songID FROM releases_song WHERE artistID IN (SELECT artistID FROM artist WHERE userID = ?)
			);",
			"DELETE FROM in_playlist WHERE songID IN (
			    SELECT songID FROM releases_song WHERE artistID IN (SELECT artistID FROM artist WHERE userID = ?)
			);",
			"DELETE FROM song WHERE songID IN (
			    SELECT songID FROM releases_song WHERE artistID IN (SELECT artistID FROM artist WHERE userID = ?)
			);",

			// 3. Delete all albums by the artist (and their relations)
			"DELETE FROM releases_album WHERE artistID IN (SELECT artistID FROM artist WHERE userID = ?);",
			"DELETE FROM in_album WHERE albumID IN (
			    SELECT albumID FROM releases_album WHERE artistID IN (SELECT artistID FROM artist WHERE userID = ?)
			);",
			"DELETE FROM album WHERE albumID IN (
			    SELECT albumID FROM releases_album WHERE artistID IN (SELECT artistID FROM artist WHERE userID = ?)
			);",

			// 4. Delete the artist entry
			"DELETE FROM artist WHERE userID = ?;",

			// 5. Finally, delete the user
			"DELETE FROM user WHERE userID = ?;"
		];

		foreach ($queries as $sql) {
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $userID);
			$stmt->execute();
			$stmt->close();
		}
	}

	public static function generateRandomString(int $length = 10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!?,.:;()<>$#&*+-/=@%'): string
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

/*INSERT INTO User VALUES (12345, "user1", "email1", "password1", "imagePath1");
INSERT INTO User VALUES (123456, "user2", "email2", "password2", "imagePath2");

INSERT INTO Artist VALUES (12345, "artist1", "imagePath3", 1, '2025-05-10', 12345);
INSERT INTO Artist VALUES (123456, "artist2", "imagePath4", 1, '2025-05-10', 123456);

INSERT INTO Song VALUES (0000, "song1", "genre1", '2025-05-09', "imagePath5", 4.5, '00:50:50', "filepath");

INSERT INTO ReleasesSong VALUES (12345, 0000);
INSERT INTO ReleasesSong VALUES (123456, 0000);


INSERT INTO song VALUES (0001, "Midnight Dreams", "Pop", '2025-05-09', "imagePath1", 4.2, '03:15:12', "filepath1");
INSERT INTO song VALUES (0002, "Echoes of Silence", "Rock", '2025-05-09', "imagePath2", 4.8, '04:05:45', "filepath2");
INSERT INTO song VALUES (0003, "Chasing Stars", "EDM", '2025-05-09', "imagePath3", 4.0, '02:45:25', "filepath3");
INSERT INTO song VALUES (0004, "Whispers in the Dark", "R&B", '2025-05-09', "imagePath4", 4.6, '03:30:35', "filepath4");
INSERT INTO song VALUES (0005, "Heartbreaker", "Pop", '2025-05-09', "imagePath5", 3.9, '03:20:10', "filepath5");
INSERT INTO song VALUES (0006, "Luminous Sky", "Indie", '2025-05-09', "imagePath6", 4.7, '03:10:50', "filepath6");
INSERT INTO song VALUES (0007, "Violet Horizon", "Alternative", '2025-05-09', "imagePath7", 4.3, '02:55:30', "filepath7");
INSERT INTO song VALUES (0008, "On the Edge", "Rock", '2025-05-09', "imagePath8", 4.1, '03:50:20', "filepath8");
INSERT INTO song VALUES (0009, "Rising Sun", "Pop", '2025-05-09', "imagePath9", 4.4, '03:05:15', "filepath9");
INSERT INTO song VALUES (0010, "In the Silence", "Classical", '2025-05-09', "imagePath10", 5.0, '04:00:10', "filepath10");
INSERT INTO song VALUES (0011, "Fading Light", "Alternative", '2025-05-09', "imagePath11", 3.8, '03:35:40', "filepath11");
INSERT INTO song VALUES (0012, "Lost in Time", "EDM", '2025-05-09', "imagePath12", 4.2, '02:50:55', "filepath12");
INSERT INTO song VALUES (0013, "Serenity", "Jazz", '2025-05-09', "imagePath13", 4.9, '03:40:25', "filepath13");
INSERT INTO song VALUES (0014, "Cosmic Waves", "Pop", '2025-05-09', "imagePath14", 4.6, '03:00:05', "filepath14");
INSERT INTO song VALUES (0015, "Storm Inside", "Rock", '2025-05-09', "imagePath15", 4.3, '04:10:15', "filepath15");
INSERT INTO song VALUES (0016, "Silent Rain", "Indie", '2025-05-09', "imagePath16", 4.1, '02:35:45', "filepath16");
INSERT INTO song VALUES (0017, "Reckless Love", "R&B", '2025-05-09', "imagePath17", 4.7, '03:25:10', "filepath17");
INSERT INTO song VALUES (0018, "Golden Horizon", "Country", '2025-05-09', "imagePath18", 4.4, '03:15:20', "filepath18");
INSERT INTO song VALUES (0019, "Reflections", "Electronic", '2025-05-09', "imagePath19", 4.5, '03:45:05', "filepath19");
INSERT INTO song VALUES (0020, "Into the Wild", "Rock", '2025-05-09', "imagePath20", 4.0, '04:30:25', "filepath20");

INSERT INTO user VALUES (0001, "john_doe", "john.doe@example.com", "password123", "salt", "imagePath1");
INSERT INTO user VALUES (0002, "sara_smith", "sara.smith@example.com", "securePass456", "salt", "imagePath2");
INSERT INTO user VALUES (0003, "alex_lee", "alex.lee@example.com", "alexPass789", "salt", "imagePath3");
INSERT INTO user VALUES (0004, "emily_jones", "emily.jones@example.com", "emilySecret101", "salt", "imagePath4");
INSERT INTO user VALUES (0005, "michael_brown", "michael.brown@example.com", "mikePass202", "salt", "imagePath5");
INSERT INTO user VALUES (0006, "laura_wilson", "laura.wilson@example.com", "laura1234", "salt", "imagePath6");
INSERT INTO user VALUES (0007, "daniel_white", "daniel.white@example.com", "danielPass567", "salt", "imagePath7");
INSERT INTO user VALUES (0008, "lisa_clark", "lisa.clark@example.com", "lisaSecure890", "salt", "imagePath8");
INSERT INTO user VALUES (0009, "james_harris", "james.harris@example.com", "james2021", "salt", "imagePath9");
INSERT INTO user VALUES (0010, "olivia_martin", "olivia.martin@example.com", "oliviaPass345", "salt", "imagePath10");

INSERT INTO artist VALUES (12345, "The Midnight Echo", "imagePath1", 1, '2025-05-10', 0001);
INSERT INTO artist VALUES (12346, "Nova Sparks", "imagePath2", 1, '2025-05-10', 0002);
INSERT INTO artist VALUES (12347, "Luna Waves", "imagePath3", 1, '2025-05-10', 0003);
INSERT INTO artist VALUES (12348, "Echo Runners", "imagePath4", 1, '2025-05-10', 0004);
INSERT INTO artist VALUES (12349, "Skyline Dreams", "imagePath5", 1, '2025-05-10', 0005);
INSERT INTO artist VALUES (12350, "Electric Vibe", "imagePath6", 1, '2025-05-10', 0006);
INSERT INTO artist VALUES (12351, "Wanderlust Sounds", "imagePath7", 1, '2025-05-10', 0007);
INSERT INTO artist VALUES (12352, "Silent Mirage", "imagePath8", 1, '2025-05-10', 0008);
INSERT INTO artist VALUES (12353, "Stellar Bloom", "imagePath9", 1, '2025-05-10', 0009);
INSERT INTO artist VALUES (12354, "Violet Horizon", "imagePath10", 1, '2025-05-10', 0010);

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