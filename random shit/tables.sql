CREATE TABLE song
(
	songID        INT PRIMARY KEY,
	title         VARCHAR(255) NOT NULL,
	genre         VARCHAR(255),
	releaseDate   DATE,
	imageName     VARCHAR(255),
	thumbnailName VARCHAR(255),
	songLength    INT,

	flacFileName  VARCHAR(255),
	opusFileName  VARCHAR(255)
);

CREATE TABLE album
(
	albumID       INT PRIMARY KEY,
	title         VARCHAR(255),
	imageName     VARCHAR(255),
	thumbnailName VARCHAR(255),
	length        INT,
	duration      INT,
	releaseDate   DATE,
	isSingle      BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE user
(
	userID        INT PRIMARY KEY,
	username      VARCHAR(255) NOT NULL UNIQUE,
	email         VARCHAR(255) NOT NULL UNIQUE,
	userPassword  VARCHAR(255) NOT NULL,
	salt          VARCHAR(255) NOT NULL,
	isAdmin       BOOLEAN      NOT NULL DEFAULT FALSE,
	isArtist      BOOLEAN      NOT NULL DEFAULT FALSE,
	imageName     VARCHAR(255),
	thumbnailName VARCHAR(255)
);

CREATE TABLE playlist
(
	playlistID    INT PRIMARY KEY,
	imageName     VARCHAR(255),
	thumbnailName VARCHAR(255),
	name          VARCHAR(255),
	length        INT,
	duration      INT,

	creatorID     INT,

	FOREIGN KEY (creatorID) REFERENCES user (userID)
);

CREATE TABLE artist
(
	artistID      INT PRIMARY KEY,
	name          VARCHAR(255) NOT NULL,
	imageName     VARCHAR(255),
	thumbnailName VARCHAR(255),
	activeSince   DATE,

	userID        INT,

	FOREIGN KEY (userID) REFERENCES user (userID)
);

CREATE TABLE releases_song
(
	artistID    INT,
	songID      INT,
	artistIndex INT,

	FOREIGN KEY (artistID) REFERENCES artist (artistID),
	FOREIGN KEY (songID) REFERENCES song (songID),

	CONSTRAINT releaseSongKey PRIMARY KEY (artistID, songID)
);

CREATE TABLE releases_album
(
	artistID    INT,
	albumID     INT,
	artistIndex INT,

	FOREIGN KEY (artistID) REFERENCES artist (artistID),
	FOREIGN KEY (albumID) REFERENCES album (albumID),

	CONSTRAINT releaseAlbumKey PRIMARY KEY (artistID, albumID)
);

CREATE TABLE in_album
(
	songID    INT,
	albumId   INT,
	songIndex INT,

	FOREIGN KEY (songID) REFERENCES song (songID),
	FOREIGN KEY (albumID) REFERENCES album (albumID),

	CONSTRAINT inAlbumKey PRIMARY KEY (songID, albumID)
);

CREATE TABLE in_playlist
(
	songID     INT,
	playlistID INT,
	songIndex  INT,

	FOREIGN KEY (songID) REFERENCES song (songID),
	FOREIGN KEY (playlistID) REFERENCES playlist (playlistID),

	CONSTRAINT inPlaylistKey PRIMARY KEY (songID, playlistID)
);