CREATE TABLE Song
(
    songID      INT,
    title       VARCHAR(255) NOT NULL,
    genre       VARCHAR(255),
    releaseDate DATE,
    imagePath   VARCHAR(255),
    rating      FLOAT,
    songLength  TIME,

    filePath    VARCHAR(255),

    PRIMARY KEY (songID)
);

CREATE TABLE Album
(
    albumID   INT,
    title     VARCHAR(255),
    imagePath VARCHAR(255),
    length    INT,
    duration  TIME,

    PRIMARY KEY (albumID)
);

CREATE TABLE User
(
    userID       INT,
    username     VARCHAR(255) NOT NULL,
    email        VARCHAR(255) NOT NULL,
    userPassword VARCHAR(255) NOT NULL,
    salt         VARCHAR(255) NOT NULL,
    imagePath    VARCHAR(255),

    PRIMARY KEY (userID)
);

CREATE TABLE Playlist
(
    playlistID INT,
    imagePath  VARCHAR(255),
    name       VARCHAR(255),
    length     INT,
    duration   TIME,

    creatorID  INT,

    PRIMARY KEY (playlistID),

    FOREIGN KEY (creatorID) REFERENCES User (userID)
);

CREATE TABLE Artist
(
    artistID    INT,
    name        VARCHAR(255),
    imagePath   VARCHAR(255),
    follower    INT,
    activeSince DATE,

    userID      INT,

    FOREIGN KEY (userID) REFERENCES User (userID),

    PRIMARY KEY (artistID)
);

CREATE TABLE Releases_Song
(
    artistID INT,
    songID   INT,

    FOREIGN KEY (artistID) REFERENCES Artist (artistID),
    FOREIGN KEY (songID) REFERENCES Song (songID),

    CONSTRAINT releaseSongKey PRIMARY KEY (artistID, songID)
);

CREATE TABLE Releases_Album
(
    artistID INT,
    albumID  INT,

    FOREIGN KEY (artistID) REFERENCES Artist (artistID),
    FOREIGN KEY (albumID) REFERENCES Album (albumID),

    CONSTRAINT releaseAlbumKey PRIMARY KEY (artistID, albumID)
);

CREATE TABLE In_Album
(
    songID  INT,
    albumId INT,

    FOREIGN KEY (songID) REFERENCES Song (songID),
    FOREIGN KEY (albumID) REFERENCES Album (albumID),

    CONSTRAINT inAlbumKey PRIMARY KEY (songID, albumID)
);

CREATE TABLE In_Playlist
(
    songID     INT,
    playlistID INT,

    FOREIGN KEY (songID) REFERENCES Song (songID),
    FOREIGN KEY (playlistID) REFERENCES Playlist (playlistID),

    CONSTRAINT inPlaylistKey PRIMARY KEY (songID, playlistID)
);