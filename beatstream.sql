-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 24. Jun 2025 um 15:42
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `beatstream`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `album`
--

CREATE TABLE `album` (
  `albumID` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `imageName` varchar(255) DEFAULT NULL,
  `length` int(11) DEFAULT NULL,
  `duration` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `album`
--

INSERT INTO `album` (`albumID`, `title`, `imageName`, `length`, `duration`) VALUES
(2010862770, 'Remade In Misery', '6859844acaa9d.jpg', 11, '12:38:01');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `artist`
--

CREATE TABLE `artist` (
  `artistID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `imageName` varchar(255) DEFAULT NULL,
  `activeSince` date DEFAULT NULL,
  `userID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `artist`
--

INSERT INTO `artist` (`artistID`, `name`, `imageName`, `activeSince`, `userID`) VALUES
(12345, 'The Midnight Echo', '', '2025-05-10', 1),
(12346, 'Nova Sparks', '', '2025-05-10', 2),
(12347, 'Luna Waves', '', '2025-05-10', 3),
(12348, 'Echo Runners', '', '2025-05-10', 4),
(12349, 'Skyline Dreams', '', '2025-05-10', 5),
(12350, 'Electric Vibe', '', '2025-05-10', 6),
(12351, 'Wanderlust Sounds', '', '2025-05-10', 7),
(12352, 'Silent Mirage', '', '2025-05-10', 8),
(12353, 'Stellar Bloom', '', '2025-05-10', 9),
(12354, 'Violet Horizon', '', '2025-05-10', 10),
(1191795637, 'AJ Channer', '', '2014-01-01', 448765477),
(1703545867, 'Memphis May Fire', '6859811f61944.jpg', '2006-12-01', 773568582);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `in_album`
--

CREATE TABLE `in_album` (
  `songID` int(11) NOT NULL,
  `albumId` int(11) NOT NULL,
  `songIndex` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `in_album`
--

INSERT INTO `in_album` (`songID`, `albumId`, `songIndex`) VALUES
(371924227, 2010862770, 0),
(790884928, 2010862770, 4),
(830664484, 2010862770, 5),
(932039978, 2010862770, 9),
(1109907086, 2010862770, 8),
(1309990108, 2010862770, 10),
(1313173535, 2010862770, 1),
(1559867559, 2010862770, 6),
(1593412358, 2010862770, 2),
(1657256488, 2010862770, 3),
(1992853497, 2010862770, 7);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `in_playlist`
--

CREATE TABLE `in_playlist` (
  `songID` int(11) NOT NULL,
  `playlistID` int(11) NOT NULL,
  `songIndex` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `in_playlist`
--

INSERT INTO `in_playlist` (`songID`, `playlistID`, `songIndex`) VALUES
(371924227, 457726783, 3),
(830664484, 457726783, 0),
(1313173535, 457726783, 1),
(1559867559, 457726783, 2),
(1992853497, 457726783, 4);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `playlist`
--

CREATE TABLE `playlist` (
  `playlistID` int(11) NOT NULL,
  `imageName` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `length` int(11) DEFAULT NULL,
  `duration` time DEFAULT NULL,
  `creatorID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `playlist`
--

INSERT INTO `playlist` (`playlistID`, `imageName`, `name`, `length`, `duration`, `creatorID`) VALUES
(457726783, 'folder_1750703310.png', 'test', 5, '12:02:36', 1711997645);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `releases_album`
--

CREATE TABLE `releases_album` (
  `artistID` int(11) NOT NULL,
  `albumID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `releases_album`
--

INSERT INTO `releases_album` (`artistID`, `albumID`) VALUES
(1191795637, 2010862770),
(1703545867, 2010862770);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `releases_song`
--

CREATE TABLE `releases_song` (
  `artistID` int(11) NOT NULL,
  `songID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `releases_song`
--

INSERT INTO `releases_song` (`artistID`, `songID`) VALUES
(12345, 1),
(12345, 2),
(12346, 3),
(12346, 4),
(12347, 5),
(12347, 6),
(12348, 7),
(12348, 8),
(12349, 9),
(12349, 10),
(12350, 11),
(12350, 12),
(12351, 13),
(12351, 14),
(12352, 15),
(12352, 16),
(12353, 17),
(12353, 18),
(12354, 19),
(12354, 20),
(1191795637, 932039978),
(1703545867, 371924227),
(1703545867, 790884928),
(1703545867, 830664484),
(1703545867, 932039978),
(1703545867, 1109907086),
(1703545867, 1309990108),
(1703545867, 1313173535),
(1703545867, 1559867559),
(1703545867, 1593412358),
(1703545867, 1657256488),
(1703545867, 1992853497);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `song`
--

CREATE TABLE `song` (
  `songID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `genre` varchar(255) DEFAULT NULL,
  `releaseDate` date DEFAULT NULL,
  `imageName` varchar(255) DEFAULT NULL,
  `songLength` time DEFAULT NULL,
  `fileName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `song`
--

INSERT INTO `song` (`songID`, `title`, `genre`, `releaseDate`, `imageName`, `songLength`, `fileName`) VALUES
(1, 'Midnight Dreams', 'Pop', '2025-05-09', '', '03:15:12', 'song.mp3'),
(2, 'Echoes of Silence', 'Rock', '2025-05-09', '', '04:05:45', 'song.mp3'),
(3, 'Chasing Stars', 'EDM', '2025-05-09', '', '02:45:25', 'song.mp3'),
(4, 'Whispers in the Dark', 'R&B', '2025-05-09', '', '03:30:35', 'song.mp3'),
(5, 'Heartbreaker', 'Pop', '2025-05-09', '', '03:20:10', 'song.mp3'),
(6, 'Luminous Sky', 'Indie', '2025-05-09', '', '03:10:50', ''),
(7, 'Violet Horizon', 'Alternative', '2025-05-09', '', '02:55:30', 'song.mp3'),
(8, 'On the Edge', 'Rock', '2025-05-09', '', '03:50:20', 'song.mp3'),
(9, 'Rising Sun', 'Pop', '2025-05-09', '', '03:05:15', 'song.mp3'),
(10, 'In the Silence', 'Classical', '2025-05-09', '', '04:00:10', 'song.mp3'),
(11, 'Fading Light', 'Alternative', '2025-05-09', '', '03:35:40', 'song.mp3'),
(12, 'Lost in Time', 'EDM', '2025-05-09', '', '02:50:55', 'song.mp3'),
(13, 'Serenity', 'Jazz', '2025-05-09', '', '03:40:25', 'song.mp3'),
(14, 'Cosmic Waves', 'Pop', '2025-05-09', '', '03:00:05', 'song.mp3'),
(15, 'Storm Inside', 'Rock', '2025-05-09', '', '04:10:15', 'song.mp3'),
(16, 'Silent Rain', 'Indie', '2025-05-09', '', '02:35:45', 'song.mp3'),
(17, 'Reckless Love', 'R&B', '2025-05-09', '', '03:25:10', 'song.mp3'),
(18, 'Golden Horizon', 'Country', '2025-05-09', '', '03:15:20', 'song.mp3'),
(19, 'Reflections', 'Electronic', '2025-05-09', '', '03:45:05', 'song.mp3'),
(20, 'Into the Wild', 'Rock', '2025-05-09', '', '04:30:25', 'song.mp3'),
(371924227, 'Blood & Water', 'Metalcore', '2022-06-03', 'folder_1750696286.jpg', '00:03:51', '001 - Blood & Water_1750696286.mp3'),
(790884928, 'The American Dream', 'Metalcore', '2022-06-03', 'folder_1750696580.jpg', '00:03:31', '005 - The American Dream_1750696580.mp3'),
(830664484, 'Your Turn', 'Metalcore', '2022-06-03', 'folder_1750696377.jpg', '00:03:21', '006 - Your Turn_1750696377.mp3'),
(932039978, 'Only Human', 'Metalcore', '2022-06-03', 'folder_1750696456.jpg', '00:03:03', '010 - Only Human_1750696456.mp3'),
(1109907086, 'Left For Dead', 'Metalcore', '2022-06-03', 'folder_1750696435.jpg', '00:03:06', '009 - Left For Dead_1750696435.mp3'),
(1309990108, 'The Fight Within', 'Metalcore', '2022-06-03', 'folder_1750696480.jpg', '00:03:54', '011 - The Fight Within_1750696480.mp3'),
(1313173535, 'Bleed Me Dry', 'Metalcore', '2022-06-03', 'folder_1750696306.jpg', '00:03:23', '002 - Bleed Me Dry_1750696306.mp3'),
(1559867559, 'Make Believe', 'Metalcore', '2022-06-03', 'folder_1750696393.jpg', '00:03:49', '007 - Make Believe_1750696393.mp3'),
(1593412358, 'Somebody', 'Metalcore', '2022-06-03', 'folder_1750696325.jpg', '00:03:20', '003 - Somebody_1750696325.mp3'),
(1657256488, 'Death Inside', 'Metalcore', '2022-06-03', 'folder_1750696346.jpg', '00:03:31', '004 - Death Inside_170.mp3'),
(1992853497, 'Misery', 'Metalcore', '2022-06-03', 'folder_1750696413.jpg', '00:03:12', '008 - Misery_1750696413.mp3');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE `user` (
  `userID` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `userPassword` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `isAdmin` tinyint(1) NOT NULL DEFAULT 0,
  `isArtist` tinyint(1) NOT NULL DEFAULT 0,
  `imageName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`userID`, `username`, `email`, `userPassword`, `salt`, `isAdmin`, `isArtist`, `imageName`) VALUES
(1, 'john_doe', 'john.doe@example.com', 'password123', 'salt', 0, 1, ''),
(2, 'sara_smith', 'sara.smith@example.com', 'securePass456', 'salt', 0, 1, ''),
(3, 'alex_lee', 'alex.lee@example.com', 'alexPass789', 'salt', 0, 1, ''),
(4, 'emily_jones', 'emily.jones@example.com', 'emilySecret101', 'salt', 0, 1, ''),
(5, 'michael_brown', 'michael.brown@example.com', 'mikePass202', 'salt', 0, 1, ''),
(6, 'laura_wilson', 'laura.wilson@example.com', 'laura1234', 'salt', 0, 1, ''),
(7, 'daniel_white', 'daniel.white@example.com', 'danielPass567', 'salt', 0, 1, ''),
(8, 'lisa_clark', 'lisa.clark@example.com', 'lisaSecure890', 'salt', 0, 1, ''),
(9, 'james_harris', 'james.harris@example.com', 'james2021', 'salt', 0, 1, ''),
(10, 'olivia_martin', 'olivia.martin@example.com', 'oliviaPass345', 'salt', 0, 1, ''),
(448765477, 'AJ Channer', 'ajchanner@beatstream.com', '4b05d53de2aec945c4875254f0c0af58fab600b16bad353c816070e72045b305', 'dR5xJ/d@U&(jrhFN', 0, 1, ''),
(773568582, 'Memphis May Fire', 'memphismayfire@beatstream.com', 'f07510ba082a4fbd1e4cfa1920a15186a0657fa26d1554c9570de2604134d326', 'b)+61uCwrSG@Ger+', 0, 1, '685980f8d7b55.jpg'),
(1108887861, 'testuser', 'testemail@mail.com', 'f8b93767c005ed040d3a91b1dee3e998b8b642db591669119d7258fc82ff418b', '8Lk$K,<zM6+?v7Jv', 0, 0, 'Unbenannt1750702716.png'),
(1711997645, 'root', 'root@root.root', 'a421fc12fa867397d5ffe9ef03da0356ed5cc850975f0de58ea8b95e82e341f3', 'ddHkq?HD:Sr%J;dg', 1, 0, 'rootuser1750695678.webp');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `album`
--
ALTER TABLE `album`
  ADD PRIMARY KEY (`albumID`);

--
-- Indizes für die Tabelle `artist`
--
ALTER TABLE `artist`
  ADD PRIMARY KEY (`artistID`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `userID` (`userID`);

--
-- Indizes für die Tabelle `in_album`
--
ALTER TABLE `in_album`
  ADD PRIMARY KEY (`songID`,`albumId`),
  ADD KEY `albumId` (`albumId`);

--
-- Indizes für die Tabelle `in_playlist`
--
ALTER TABLE `in_playlist`
  ADD PRIMARY KEY (`songID`,`playlistID`),
  ADD KEY `playlistID` (`playlistID`);

--
-- Indizes für die Tabelle `playlist`
--
ALTER TABLE `playlist`
  ADD PRIMARY KEY (`playlistID`),
  ADD KEY `creatorID` (`creatorID`);

--
-- Indizes für die Tabelle `releases_album`
--
ALTER TABLE `releases_album`
  ADD PRIMARY KEY (`artistID`,`albumID`),
  ADD KEY `albumID` (`albumID`);

--
-- Indizes für die Tabelle `releases_song`
--
ALTER TABLE `releases_song`
  ADD PRIMARY KEY (`artistID`,`songID`),
  ADD KEY `songID` (`songID`);

--
-- Indizes für die Tabelle `song`
--
ALTER TABLE `song`
  ADD PRIMARY KEY (`songID`);

--
-- Indizes für die Tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `artist`
--
ALTER TABLE `artist`
  ADD CONSTRAINT `artist_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints der Tabelle `in_album`
--
ALTER TABLE `in_album`
  ADD CONSTRAINT `in_album_ibfk_1` FOREIGN KEY (`songID`) REFERENCES `song` (`songID`),
  ADD CONSTRAINT `in_album_ibfk_2` FOREIGN KEY (`albumId`) REFERENCES `album` (`albumID`);

--
-- Constraints der Tabelle `in_playlist`
--
ALTER TABLE `in_playlist`
  ADD CONSTRAINT `in_playlist_ibfk_1` FOREIGN KEY (`songID`) REFERENCES `song` (`songID`),
  ADD CONSTRAINT `in_playlist_ibfk_2` FOREIGN KEY (`playlistID`) REFERENCES `playlist` (`playlistID`);

--
-- Constraints der Tabelle `playlist`
--
ALTER TABLE `playlist`
  ADD CONSTRAINT `playlist_ibfk_1` FOREIGN KEY (`creatorID`) REFERENCES `user` (`userID`);

--
-- Constraints der Tabelle `releases_album`
--
ALTER TABLE `releases_album`
  ADD CONSTRAINT `releases_album_ibfk_1` FOREIGN KEY (`artistID`) REFERENCES `artist` (`artistID`),
  ADD CONSTRAINT `releases_album_ibfk_2` FOREIGN KEY (`albumID`) REFERENCES `album` (`albumID`);

--
-- Constraints der Tabelle `releases_song`
--
ALTER TABLE `releases_song`
  ADD CONSTRAINT `releases_song_ibfk_1` FOREIGN KEY (`artistID`) REFERENCES `artist` (`artistID`),
  ADD CONSTRAINT `releases_song_ibfk_2` FOREIGN KEY (`songID`) REFERENCES `song` (`songID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
