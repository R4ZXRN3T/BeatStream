<?php
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
		$stmt = DBConn::getConn()->prepare("SELECT Song.songID, Song.title, Artist.name, Song.genre, Song.releaseDate, Song.imagePath, Song.rating, Song.songLength, Song.filePath
		FROM Song, Artist, ReleasesSong
		WHERE Song.songID = ReleasesSong.songID
		AND Artist.artistID = ReleasesSong.artistID;");

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
		$stmt = DBConn::getConn()->prepare("SELECT * FROM Artist");

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
		$stmt = DBConn::getConn()->prepare("SELECT * FROM User;");

		$stmt->execute();
		$result = $stmt->get_result();

		$userList = array();
		while ($row = $result->fetch_assoc()) {
			$userList[] = new User($row["userID"], $row["username"], $row["email"], $row["userPassword"], $row["imagePath"]);
		}

		$stmt->close();

		return $userList;
	}

	public static function getPlaylistList(): array
    {
		$stmt = DBConn::getConn()->prepare("SELECT * FROM Playlist;");

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
		$stmt = DBConn::getConn()->prepare("SELECT Album.albumID, title, name, Album.imagePath, length, duration
		FROM Album, Artist, ReleasesAlbum
		WHERE ReleasesAlbum.artistID = Artist.artistID
		AND Album.albumID = ReleasesAlbum.albumID;");

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

		$sqlSong = "INSERT INTO Song (songID, title, genre, releaseDate, imagePath, rating, songLength, filePath) 
		VALUES ('" . $newSongID . "', '" . $song->getTitle() . "', '" . $song->getGenre() . "', '" . $song->getReleaseDate()->format("Y-m-d") . "', '" . $song->getImagePath() . "', '" . $song->getRating() . "', '" . $song->getSongLength()->format("H:i:s") . "', '" . $song->getFilePath() . "')";

		$stmt = DBConn::getConn()->prepare($sqlSong);
		$stmt->execute();
		$stmt->close();

		$artistsInSong = explode(", ", $song->getArtists());

		for ($j = 0; $j < count($artistsInSong); $j++) {

			for ($i = 0; $i < count($artistList); $i++) {

				if ($artistsInSong[$j] == $artistList[$i]->getName()) {

					$stmt = DBConn::getConn()->prepare("INSERT INTO ReleasesSong VALUES (" . $artistList[$i]->getArtistID() . ", " . $newSongID . ")");
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

		$sqlArtist = "INSERT INTO Artist VALUES ('" . $newArtistID . "', '" . $artist->getName() . "', '" . $artist->getImagePath() . "', '" . $artist->getFollower() . "', '" . $artist->getActiveSince() . "', '" . $artist->getUserID() . "')";
		$stmt = DBConn::getConn()->prepare($sqlArtist);
		$stmt->execute();
		$stmt->close();
	}

	public static function insertUser(User $user)
	{
		$userList = SongController::getUserList();

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

		$sqlUser = "INSERT INTO User VALUES ('" . $newUserID . "', '" . $user->getUsername() . "', '" . $user->getEmail() . "', '" . $user->getUserPassword() . "', '" . $user->getImagePath() . "')";
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

		$sqlPlaylist = "INSERT INTO Playlist VALUES ('" . $newPlaylistID . "', '" . $playlist->getImagePath() . "', '" . $playlist->getName() . "', '" . $playlist->getLength() . "', '" . $playlist->getDuration() . "', '" . $playlist->getCreatorID() . "')";
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

		$sqlAlbum = "INSERT INTO Album VALUES (" . $newAlbumID . ", '" . $album->getName() . "', '" . $album->getImagePath() . "', '" . $album->getLength() . "', '" . $album->getDuration() . "')";
		$stmt = DBConn::getConn()->prepare($sqlAlbum);
		$stmt->execute();
		$stmt->close();

		$artistsInAlbum = explode(", ", $album->getArtists());

		for ($j = 0; $j < count($artistsInAlbum); $j++) {

			for ($i = 0; $i < count($artistList); $i++) {

				if ($artistsInAlbum[$j] == $artistList[$i]->getName()) {

					$stmt = DBConn::getConn()->prepare("INSERT INTO ReleasesAlbum VALUES (" . $artistList[$i]->getArtistID() . ", " . $newAlbumID . ")");
					$stmt->execute();
					$stmt->close();
				}
			}
		}
	}
}

/*INSERT INTO User VALUES (12345, "user1", "email1", "password1", "imagePath1");
INSERT INTO User VALUES (123456, "user2", "email2", "password2", "imagePath2");

INSERT INTO Artist VALUES (12345, "artist1", "imagePath3", 1, '2025-05-10', 12345);
INSERT INTO Artist VALUES (123456, "artist2", "imagePath4", 1, '2025-05-10', 123456);

INSERT INTO Song VALUES (0000, "song1", "genre1", '2025-05-09', "imagePath5", 4.5, '00:50:50', "filepath");

INSERT INTO ReleasesSong VALUES (12345, 0000);
INSERT INTO ReleasesSong VALUES (123456, 0000);


INSERT INTO Song VALUES (0001, "Midnight Dreams", "Pop", '2025-05-09', "imagePath1", 4.2, '03:15:12', "filepath1");
INSERT INTO Song VALUES (0002, "Echoes of Silence", "Rock", '2025-05-09', "imagePath2", 4.8, '04:05:45', "filepath2");
INSERT INTO Song VALUES (0003, "Chasing Stars", "EDM", '2025-05-09', "imagePath3", 4.0, '02:45:25', "filepath3");
INSERT INTO Song VALUES (0004, "Whispers in the Dark", "R&B", '2025-05-09', "imagePath4", 4.6, '03:30:35', "filepath4");
INSERT INTO Song VALUES (0005, "Heartbreaker", "Pop", '2025-05-09', "imagePath5", 3.9, '03:20:10', "filepath5");
INSERT INTO Song VALUES (0006, "Luminous Sky", "Indie", '2025-05-09', "imagePath6", 4.7, '03:10:50', "filepath6");
INSERT INTO Song VALUES (0007, "Violet Horizon", "Alternative", '2025-05-09', "imagePath7", 4.3, '02:55:30', "filepath7");
INSERT INTO Song VALUES (0008, "On the Edge", "Rock", '2025-05-09', "imagePath8", 4.1, '03:50:20', "filepath8");
INSERT INTO Song VALUES (0009, "Rising Sun", "Pop", '2025-05-09', "imagePath9", 4.4, '03:05:15', "filepath9");
INSERT INTO Song VALUES (0010, "In the Silence", "Classical", '2025-05-09', "imagePath10", 5.0, '04:00:10', "filepath10");
INSERT INTO Song VALUES (0011, "Fading Light", "Alternative", '2025-05-09', "imagePath11", 3.8, '03:35:40', "filepath11");
INSERT INTO Song VALUES (0012, "Lost in Time", "EDM", '2025-05-09', "imagePath12", 4.2, '02:50:55', "filepath12");
INSERT INTO Song VALUES (0013, "Serenity", "Jazz", '2025-05-09', "imagePath13", 4.9, '03:40:25', "filepath13");
INSERT INTO Song VALUES (0014, "Cosmic Waves", "Pop", '2025-05-09', "imagePath14", 4.6, '03:00:05', "filepath14");
INSERT INTO Song VALUES (0015, "Storm Inside", "Rock", '2025-05-09', "imagePath15", 4.3, '04:10:15', "filepath15");
INSERT INTO Song VALUES (0016, "Silent Rain", "Indie", '2025-05-09', "imagePath16", 4.1, '02:35:45', "filepath16");
INSERT INTO Song VALUES (0017, "Reckless Love", "R&B", '2025-05-09', "imagePath17", 4.7, '03:25:10', "filepath17");
INSERT INTO Song VALUES (0018, "Golden Horizon", "Country", '2025-05-09', "imagePath18", 4.4, '03:15:20', "filepath18");
INSERT INTO Song VALUES (0019, "Reflections", "Electronic", '2025-05-09', "imagePath19", 4.5, '03:45:05', "filepath19");
INSERT INTO Song VALUES (0020, "Into the Wild", "Rock", '2025-05-09', "imagePath20", 4.0, '04:30:25', "filepath20");

INSERT INTO User VALUES (0001, "john_doe", "john.doe@example.com", "password123", "imagePath1");
INSERT INTO User VALUES (0002, "sara_smith", "sara.smith@example.com", "securePass456", "imagePath2");
INSERT INTO User VALUES (0003, "alex_lee", "alex.lee@example.com", "alexPass789", "imagePath3");
INSERT INTO User VALUES (0004, "emily_jones", "emily.jones@example.com", "emilySecret101", "imagePath4");
INSERT INTO User VALUES (0005, "michael_brown", "michael.brown@example.com", "mikePass202", "imagePath5");
INSERT INTO User VALUES (0006, "laura_wilson", "laura.wilson@example.com", "laura1234", "imagePath6");
INSERT INTO User VALUES (0007, "daniel_white", "daniel.white@example.com", "danielPass567", "imagePath7");
INSERT INTO User VALUES (0008, "lisa_clark", "lisa.clark@example.com", "lisaSecure890", "imagePath8");
INSERT INTO User VALUES (0009, "james_harris", "james.harris@example.com", "james2021", "imagePath9");
INSERT INTO User VALUES (0010, "olivia_martin", "olivia.martin@example.com", "oliviaPass345", "imagePath10");

INSERT INTO Artist VALUES (12345, "The Midnight Echo", "imagePath1", 1, '2025-05-10', 0001);
INSERT INTO Artist VALUES (12346, "Nova Sparks", "imagePath2", 1, '2025-05-10', 0002);
INSERT INTO Artist VALUES (12347, "Luna Waves", "imagePath3", 1, '2025-05-10', 0003);
INSERT INTO Artist VALUES (12348, "Echo Runners", "imagePath4", 1, '2025-05-10', 0004);
INSERT INTO Artist VALUES (12349, "Skyline Dreams", "imagePath5", 1, '2025-05-10', 0005);
INSERT INTO Artist VALUES (12350, "Electric Vibe", "imagePath6", 1, '2025-05-10', 0006);
INSERT INTO Artist VALUES (12351, "Wanderlust Sounds", "imagePath7", 1, '2025-05-10', 0007);
INSERT INTO Artist VALUES (12352, "Silent Mirage", "imagePath8", 1, '2025-05-10', 0008);
INSERT INTO Artist VALUES (12353, "Stellar Bloom", "imagePath9", 1, '2025-05-10', 0009);
INSERT INTO Artist VALUES (12354, "Violet Horizon", "imagePath10", 1, '2025-05-10', 0010);

INSERT INTO ReleasesSong VALUES (12345, 0001);
INSERT INTO ReleasesSong VALUES (12345, 0002);
INSERT INTO ReleasesSong VALUES (12346, 0003);
INSERT INTO ReleasesSong VALUES (12346, 0004);
INSERT INTO ReleasesSong VALUES (12347, 0005);
INSERT INTO ReleasesSong VALUES (12347, 0006);
INSERT INTO ReleasesSong VALUES (12348, 0007);
INSERT INTO ReleasesSong VALUES (12348, 0008);
INSERT INTO ReleasesSong VALUES (12349, 0009);
INSERT INTO ReleasesSong VALUES (12349, 0010);
INSERT INTO ReleasesSong VALUES (12350, 0011);
INSERT INTO ReleasesSong VALUES (12350, 0012);
INSERT INTO ReleasesSong VALUES (12351, 0013);
INSERT INTO ReleasesSong VALUES (12351, 0014);
INSERT INTO ReleasesSong VALUES (12352, 0015);
INSERT INTO ReleasesSong VALUES (12352, 0016);
INSERT INTO ReleasesSong VALUES (12353, 0017);
INSERT INTO ReleasesSong VALUES (12353, 0018);
INSERT INTO ReleasesSong VALUES (12354, 0019);
INSERT INTO ReleasesSong VALUES (12354, 0020);
*/