SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `album`
(
    `albumID`   int(11) NOT NULL,
    `title`     varchar(255) DEFAULT NULL,
    `imageName` varchar(255) DEFAULT NULL,
    `length`    int(11)      DEFAULT NULL,
    `duration`  time         DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `album` (`albumID`, `title`, `imageName`, `length`, `duration`)
VALUES (92835012, 'Loose Dirt', '685d64aedb267.jpg', 8, '00:31:38'),
       (139453109, 'Reincarnation', '685d627de1391.jpg', 8, '00:27:26'),
       (239172513, 'BIG PHARMA', '685c30ed89d78.jpg', 9, '00:18:01'),
       (290628771, 'Darkbloom', '685c2cf8f152d.jpg', 10, '00:36:46'),
       (602358090, 'Fake Everything', '685c2b6d81e74.jpg', 6, '00:19:04'),
       (1266493014, 'Remade In Misery', '685d605923d8a.jpg', 11, '00:37:37'),
       (1560083495, 'Shapeshifter', '685d5e602cb37.jpg', 10, '00:30:11');

CREATE TABLE `artist`
(
    `artistID`    int(11)      NOT NULL,
    `name`        varchar(255) NOT NULL,
    `imageName`   varchar(255) DEFAULT NULL,
    `activeSince` date         DEFAULT NULL,
    `userID`      int(11)      DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `artist` (`artistID`, `name`, `imageName`, `activeSince`, `userID`)
VALUES (22526499, 'Kingpin Skinny Pimp', '685bf1935a212.jfif', '2007-01-05', 1917040665),
       (269327111, 'ENMY', '685bf26c4d9f6.webp', '2019-05-27', 1377223767),
       (296541379, 'Pharmacist', '685bf0acc68f6.jfif', '2021-07-21', 1553281647),
       (354172831, 'Bring Me The Horizon', '685bf14a7b905.jpg', '2004-01-01', 1499462201),
       (370375695, 'Lilbusy', '685bec81d05ea.jfif', '2025-06-25', 588032118),
       (600975371, 'KXLLYXU', '685bf2df37f11.jfif', '2021-08-26', 1994596577),
       (644836764, 'Olya Holiday', '685c298e99445.jpg', '2022-07-15', 1095847301),
       (679487021, 'Klaypex', '685bf31dc170b.webp', '2011-05-06', 452377540),
       (766157380, 'akiaura', '685c280485b20.webp', '2017-02-22', 1826670471),
       (892231412, 'Caleb Shomo', '685bf02ceedec.jpg', '2008-09-11', 164085589),
       (1083973325, 'Blindside', '685bf24c6bb2b.jpg', '1994-01-01', 1354920019),
       (1148326265, 'LONOWN', '685bf0915819a.jfif', '2022-11-05', 1755109461),
       (1163829506, 'Zero 9:36', '685bf067bb733.webp', '2019-09-27', 779155170),
       (1181388884, 'Memphis May Fire', '685bf1fed291f.jpg', '2006-12-01', 2026845397),
       (1195628129, 'Lilnotsobusy', '685befeed8473.jfif', '2025-06-25', 515829910),
       (1298104848, 'Baby Jane', '685c28c21cbeb.jpg', '2019-04-15', 1564642731),
       (1361634559, 'DJ Pointless', '685c2936472a7.jpg', '2024-02-16', 1759447542),
       (1384541864, 'Skibidi Unc', '', '2000-10-13', 907911095),
       (1452958375, 'Apoc Crisis', '', '1970-01-01', 196994981),
       (1545421902, 'Electric Callboy', '685bf20e2c072.jpg', '2010-09-03', 1526269191),
       (1696148028, 'GHOST DATA', '685bf2a9166aa.png', '2012-12-20', 1736983402),
       (1822918017, '$werve', '685bf0e273dfd.jfif', '2021-02-27', 621945341),
       (1868345578, 'AJ Channer', '685bf299db2cc.jpg', '2014-01-01', 1653533046),
       (1873547868, 'Ghostface Playa', '685bf1614feb4.jfif', '2015-04-20', 1103097431),
       (1910588588, 'We Came As Romans', '685beff8efa23.jpg', '2005-08-01', 1143029010),
       (1925741788, 'Towa', '685bf1cc2bfc7.jfif', '2016-11-14', 1271926637);

CREATE TABLE `in_album`
(
    `songID`    int(11) NOT NULL,
    `albumId`   int(11) NOT NULL,
    `songIndex` int(11) DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `in_album` (`songID`, `albumId`, `songIndex`)
VALUES (143861200, 239172513, 3),
       (170590722, 139453109, 6),
       (186210868, 92835012, 0),
       (277064629, 290628771, 5),
       (313528847, 92835012, 7),
       (333597308, 139453109, 2),
       (343435405, 1560083495, 0),
       (391682578, 1560083495, 6),
       (402564235, 290628771, 1),
       (455092328, 290628771, 3),
       (459669696, 239172513, 7),
       (565624535, 290628771, 9),
       (607214217, 92835012, 5),
       (622871191, 1266493014, 10),
       (693947335, 290628771, 7),
       (737948159, 1560083495, 3),
       (769597287, 1266493014, 7),
       (800419191, 92835012, 6),
       (854393293, 139453109, 0),
       (880536876, 1266493014, 5),
       (887258894, 290628771, 8),
       (891084058, 1266493014, 8),
       (902727990, 239172513, 0),
       (907487652, 239172513, 2),
       (933991387, 290628771, 6),
       (939491373, 290628771, 0),
       (984839407, 139453109, 5),
       (1021316266, 92835012, 4),
       (1092895489, 139453109, 1),
       (1101500437, 1266493014, 9),
       (1126600836, 92835012, 3),
       (1149322401, 239172513, 6),
       (1172692568, 1560083495, 8),
       (1176092758, 1266493014, 1),
       (1192162192, 1266493014, 4),
       (1194449208, 139453109, 3),
       (1206865637, 1266493014, 0),
       (1209640170, 602358090, 1),
       (1237020352, 602358090, 3),
       (1311226165, 1266493014, 3),
       (1346040524, 1560083495, 9),
       (1425146279, 92835012, 1),
       (1427933446, 139453109, 7),
       (1469907326, 239172513, 4),
       (1489648141, 602358090, 2),
       (1513957663, 239172513, 8),
       (1530652750, 1560083495, 5),
       (1550737992, 602358090, 5),
       (1566994561, 92835012, 2),
       (1671382205, 290628771, 4),
       (1718631442, 602358090, 4),
       (1760821630, 1266493014, 6),
       (1766624591, 1266493014, 2),
       (1803927411, 239172513, 5),
       (1834728284, 290628771, 2),
       (1915135781, 1560083495, 2),
       (2000247808, 602358090, 0),
       (2012269410, 1560083495, 4),
       (2071909473, 1560083495, 7),
       (2096802662, 139453109, 4),
       (2099263854, 239172513, 1),
       (2101949830, 1560083495, 1);

CREATE TABLE `in_playlist`
(
    `songID`     int(11) NOT NULL,
    `playlistID` int(11) NOT NULL,
    `songIndex`  int(11) DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

CREATE TABLE `playlist`
(
    `playlistID` int(11) NOT NULL,
    `imageName`  varchar(255) DEFAULT NULL,
    `name`       varchar(255) DEFAULT NULL,
    `length`     int(11)      DEFAULT NULL,
    `duration`   time         DEFAULT NULL,
    `creatorID`  int(11)      DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `playlist` (`playlistID`, `imageName`, `name`, `length`, `duration`, `creatorID`)
VALUES (457726783, 'folder_1750703310.png', 'test', 5, '12:02:36', 1711997645);

CREATE TABLE `releases_album`
(
    `artistID` int(11) NOT NULL,
    `albumID`  int(11) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `releases_album` (`artistID`, `albumID`)
VALUES (296541379, 239172513),
       (600975371, 139453109),
       (679487021, 92835012),
       (766157380, 602358090),
       (1148326265, 602358090),
       (1181388884, 1266493014),
       (1181388884, 1560083495),
       (1910588588, 290628771);

CREATE TABLE `releases_song`
(
    `artistID` int(11) NOT NULL,
    `songID`   int(11) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `releases_song` (`artistID`, `songID`)
VALUES (22526499, 143861200),
       (296541379, 143861200),
       (296541379, 459669696),
       (296541379, 902727990),
       (296541379, 907487652),
       (296541379, 1149322401),
       (296541379, 1469907326),
       (296541379, 1513957663),
       (296541379, 1803927411),
       (296541379, 2099263854),
       (354172831, 1876663028),
       (370375695, 714873308),
       (600975371, 170590722),
       (600975371, 333597308),
       (600975371, 854393293),
       (600975371, 984839407),
       (600975371, 1092895489),
       (600975371, 1194449208),
       (600975371, 1427933446),
       (600975371, 2096802662),
       (644836764, 1550737992),
       (644836764, 1718631442),
       (679487021, 186210868),
       (679487021, 313528847),
       (679487021, 607214217),
       (679487021, 800419191),
       (679487021, 1021316266),
       (679487021, 1126600836),
       (679487021, 1425146279),
       (679487021, 1566994561),
       (766157380, 1209640170),
       (766157380, 1237020352),
       (766157380, 1489648141),
       (766157380, 1550737992),
       (766157380, 1718631442),
       (766157380, 2000247808),
       (892231412, 1834728284),
       (1083973325, 1915135781),
       (1148326265, 1209640170),
       (1148326265, 1237020352),
       (1148326265, 1489648141),
       (1148326265, 1550737992),
       (1148326265, 1718631442),
       (1148326265, 2000247808),
       (1163829506, 455092328),
       (1181388884, 343435405),
       (1181388884, 391682578),
       (1181388884, 622871191),
       (1181388884, 737948159),
       (1181388884, 769597287),
       (1181388884, 880536876),
       (1181388884, 891084058),
       (1181388884, 1101500437),
       (1181388884, 1172692568),
       (1181388884, 1176092758),
       (1181388884, 1192162192),
       (1181388884, 1206865637),
       (1181388884, 1311226165),
       (1181388884, 1346040524),
       (1181388884, 1530652750),
       (1181388884, 1760821630),
       (1181388884, 1766624591),
       (1181388884, 1915135781),
       (1181388884, 2012269410),
       (1181388884, 2071909473),
       (1181388884, 2101949830),
       (1195628129, 708001093),
       (1195628129, 714873308),
       (1298104848, 2000247808),
       (1361634559, 1237020352),
       (1452958375, 1149322401),
       (1452958375, 1803927411),
       (1822918017, 1149322401),
       (1868345578, 1101500437),
       (1873547868, 143861200),
       (1910588588, 277064629),
       (1910588588, 402564235),
       (1910588588, 455092328),
       (1910588588, 565624535),
       (1910588588, 693947335),
       (1910588588, 887258894),
       (1910588588, 933991387),
       (1910588588, 939491373),
       (1910588588, 1671382205),
       (1910588588, 1834728284),
       (1925741788, 1469907326);

CREATE TABLE `song`
(
    `songID`      int(11)      NOT NULL,
    `title`       varchar(255) NOT NULL,
    `genre`       varchar(255) DEFAULT NULL,
    `releaseDate` date         DEFAULT NULL,
    `imageName`   varchar(255) DEFAULT NULL,
    `songLength`  time         DEFAULT NULL,
    `fileName`    varchar(255) DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `song` (`songID`, `title`, `genre`, `releaseDate`, `imageName`, `songLength`, `fileName`)
VALUES (143861200, 'BULLETS', 'Memphis Rap', '2025-04-25', 'folder_1750872202.jpg', '00:01:38',
        'Pharmacist, Ghostface Playa, Kingpin Skinny Pimp - BULLETS_1750872202.mp3'),
       (170590722, 'Butchering', 'Memphis Rap', '2021-03-01', '685d61772bbd1.jpg', '00:02:51', '685d61772beba.mp3'),
       (186210868, 'Lights', 'Dubstep', '2011-08-08', '685d6438b5a70.jpg', '00:04:08', '685d6438b5c44.mp3'),
       (277064629, 'One More Day', 'Metalcore', '2022-10-14', 'folder_1750856805.jpg', '00:04:28',
        'We Came As Romans - One More Day_1750856805.mp3'),
       (313528847, 'DUBSTEP GUNS (Bonus Track)', 'Dubstep', '2011-08-08', '685d63b825d54.jpg', '00:02:32',
        '685d63b825f3a.mp3'),
       (333597308, 'Night Growling', 'Memphis Rap', '2021-03-01', '685d61db121be.jpg', '00:03:21', '685d61db124bc.mp3'),
       (343435405, 'Chaotic', 'Metalcore', '2025-03-28', '685d5d18a3d91.jpg', '00:02:52',
        'Memphis May Fire - Chaotic_1750949144.mp3'),
       (391682578, 'The Other Side', 'Metalcore', '2025-03-28', '685d5dca306f2.jpg', '00:03:46',
        'Memphis May Fire - The Other Side_1750949322.mp3'),
       (402564235, 'Plagued', 'Metalcore', '2022-10-14', 'folder_1750856828.jpg', '00:03:30',
        'We Came As Romans - Plagued_1750856828.mp3'),
       (455092328, 'Daggers', 'Metalcore', '2022-10-14', 'folder_1750871115.jpg', '00:03:14',
        'We Came As Romans, Zero 936 - Daggers_1750871115.mp3'),
       (459669696, 'CRIME RATE', 'Memphis Rap', '2025-04-25', 'folder_1750871941.jpg', '00:01:47',
        'Pharmacist - CRIME RATE_1750871941.mp3'),
       (565624535, 'Promise You', 'Metalcore', '2022-10-14', 'folder_1750856859.jpg', '00:03:48',
        'We Came As Romans - Promise You_1750856859.mp3'),
       (607214217, 'Chinter\'s Will (feat. Sara Kay)', 'Dubstep', '2011-08-08', '685d63966eeaa.jpg', '00:04:12',
        '685d63966f122.mp3'),
       (622871191, 'The Fight Within', 'Metalcore', '2022-06-03', '685d600078337.jpg', '00:03:54',
        '011 - The Fight Within_1750949888.mp3'),
       (693947335, 'The Anchor', 'Metalcore', '2022-10-14', 'folder_1750871033.jpg', '00:03:35',
        'We Came As Romans - The Anchor_1750871033.mp3'),
       (708001093, 'Backfisch', 'Hardstyle', '2025-06-25', 'Download.jfii_1750856011.jfif', '00:00:10',
        'backfisch-zarbex_1750856011.mp3'),
       (714873308, 'Habicht', 'Hardstyle', '2025-06-25', 'Download_1750855692.webp', '00:00:04',
        'habicht-habicht-hat-zwei-h_1750855692.mp3'),
       (737948159, 'Paralyzed', 'Metalcore', '2025-03-28', '685d5d9de08c0.jpg', '00:03:16',
        'Memphis May Fire - Paralyzed_1750949277.mp3'),
       (769597287, 'Misery', 'Metalcore', '2022-06-03', '685d5fb49b39e.jpg', '00:03:12', '008 - Misery_1750949812.mp3'),
       (800419191, 'Hit Me', 'Dubstep', '2011-08-08', '685d640a2b6cd.jpg', '00:04:08', '685d640a2b8bf.mp3'),
       (854393293, 'Glock Hoe', 'Memphis Rap', '2021-03-01', '685d61a8beed0.jpg', '00:03:36', '685d61a8bf11d.mp3'),
       (880536876, 'Your Turn', 'Metalcore', '2022-06-03', '685d5f89b36c4.jpg', '00:03:21',
        '006 - Your Turn_1750949769.mp3'),
       (887258894, 'Holding The Embers', 'Metalcore', '2022-10-14', 'folder_1750856765.jpg', '00:04:14',
        'We Came As Romans - Holding The Embers_1750856765.mp3'),
       (891084058, 'Left For Dead', 'Metalcore', '2022-06-03', '685d5fc5e7368.jpg', '00:03:06',
        '009 - Left For Dead_1750949829.mp3'),
       (902727990, 'LOVE2HATE', 'Memphis Rap', '2025-04-25', 'folder_1750871996.jpg', '00:02:02',
        'Pharmacist - LOVE2HATE_1750871996.mp3'),
       (907487652, 'WAKA FLOCKA', 'Memphis Rap', '2025-04-25', 'folder_1750872068.jpg', '00:02:07',
        'Pharmacist - WAKA FLOCKA_1750872068.mp3'),
       (933991387, 'Doublespeak', 'Metalcore', '2022-10-14', 'folder_1750856695.jpg', '00:03:31',
        'We Came As Romans - Doublespeak_1750856695.mp3'),
       (939491373, 'Darkbloom', 'Metalcore', '2022-10-14', 'folder_1750856500.jpg', '00:03:48',
        'We Came As Romans - Darkbloom_1750856500.mp3'),
       (984839407, 'Takin Care Of', 'Memphis Rap', '2021-03-01', '685d6227e490b.jpg', '00:04:00', '685d6227e4ba0.mp3'),
       (1021316266, 'Hey Hey', 'Dubstep', '2011-08-08', '685d63f0accb7.jpg', '00:03:19', '685d63f0ace94.mp3'),
       (1092895489, 'Kill Yourself', 'Memphis Rap', '2021-03-01', '685d61c133227.jpg', '00:03:25', '685d61c133413.mp3'),
       (1101500437, 'Only Human', 'Metalcore', '2022-06-03', '685d5fe985396.jpg', '00:03:03',
        '010 - Only Human_1750949865.mp3'),
       (1126600836, 'Let It Go (feat. KAFTYR)', 'Dubstep', '2011-08-08', '685d64284eb9f.jpg', '00:05:10',
        '685d64284f011.mp3'),
       (1149322401, 'EVIL MEN', 'Memphis Rap', '2025-04-25', 'folder_1750872111.jpg', '00:02:13',
        'Pharmacist, $werve, Apoc Krysis - EVIL MEN_1750872111.mp3'),
       (1172692568, 'Versus', 'Metalcore', '2025-03-28', '685d5de0e84ac.jpg', '00:01:40',
        'Memphis May Fire - Versus_1750949344.mp3'),
       (1176092758, 'Bleed Me Dry', 'Metalcore', '2022-06-03', '685d5f37054e7.jpg', '00:03:23',
        '002 - Bleed Me Dry_1750949687.mp3'),
       (1192162192, 'The American Dream', 'Metalcore', '2022-06-03', '685d5f765442a.jpg', '00:03:31',
        '005 - The American Dream_1750949750.mp3'),
       (1194449208, 'Paranoid Playas', 'Memphis Rap', '2021-03-01', '685d61f493758.jpg', '00:02:50',
        '685d61f493b03.mp3'),
       (1206865637, 'Blood & Water', 'Metalcore', '2022-06-03', '685d5f215bc30.jpg', '00:03:51',
        '001 - Blood & Water_1750949665.mp3'),
       (1209640170, 'Black n White', 'Electroclash', '1970-01-01', 'folder_1750870472.jpg', '00:02:58',
        'akiaura, LONOWN - Black n White_1750870472.mp3'),
       (1237020352, 'Firstclass Misery', 'Electroclash', '1970-01-01', 'folder_1750870574.jpg', '00:02:30',
        'akiaura, LONOWN, DJ Pointless - Firstclass Misery_1750870574.mp3'),
       (1311226165, 'Death Inside', 'Metalcore', '2022-06-03', '685d5f63380dd.jpg', '00:03:07',
        '004 - Death Inside_1750949731.mp3'),
       (1346040524, 'Love Is War', 'Metalcore', '2025-03-28', '685d5d700d2fe.jpg', '00:03:41',
        'Memphis May Fire - Love Is War_1750949232.mp3'),
       (1425146279, 'Rain (feat. Sara Kay)', 'Dubstep', '2011-08-08', '685d645868be3.jpg', '00:04:27',
        '685d645868dca.mp3'),
       (1427933446, 'Fuck This Police', 'Memphis Rap', '2021-03-01', '685d6192d1a95.jpg', '00:03:15',
        '685d6192d1ce5.mp3'),
       (1469907326, 'SPORT', 'Memphis Rap', '2025-04-25', 'folder_1750872223.jpg', '00:01:59',
        'Pharmacist, Towa - SPORT_1750872223.mp3'),
       (1489648141, 'Recrush', 'Electroclash', '1970-01-01', 'folder_1750870498.jpg', '00:02:48',
        'akiaura, LONOWN - Recrush_1750870498.mp3'),
       (1513957663, 'NICE GUYS WHO DO GOOD THINGS - instrumental', 'Memphis Rap', '2025-04-25', 'folder_1750872047.jpg',
        '00:02:15', 'Pharmacist - NICE GUYS WHO DO GOOD THINGS - Instrumental_1750872047.mp3'),
       (1530652750, 'Necessary Evil', 'Metalcore', '2025-03-28', '685d5d8552917.jpg', '00:02:56',
        'Memphis May Fire - Necessary Evil_1750949253.mp3'),
       (1550737992, 'Stay Strange', 'Electroclash', '1970-01-01', 'folder_1750870644.jpg', '00:02:26',
        'akiaura, LONOWN, Olya Holiday - Stay Strange_1750870644.mp3'),
       (1566994561, 'Gamefire (feat. Mike Diva)', 'Dubstep', '2011-08-08', '685d63d47ed8b.jpg', '00:03:42',
        '685d63d47efe2.mp3'),
       (1671382205, 'Golden', 'Metalcore', '2022-10-14', 'folder_1750856727.jpg', '00:03:39',
        'We Came As Romans - Golden_1750856727.mp3'),
       (1718631442, 'Autobahn Nights', 'Electroclash', '1970-01-01', 'folder_1750870611.jpg', '00:03:34',
        'akiaura, LONOWN, Olya Holiday - Autobahn Nights_1750870611.mp3'),
       (1760821630, 'Make Believe', 'Metalcore', '2022-06-03', '685d5fa180c53.jpg', '00:03:49',
        '007 - Make Believe_1750949793.mp3'),
       (1766624591, 'Somebody', 'Metalcore', '2022-06-03', '685d5f4d71015.jpg', '00:03:20',
        '003 - Somebody_1750949709.mp3'),
       (1803927411, 'NICE GUYS WHO DO GOOD THINGS', 'Memphis Rap', '2025-04-25', 'folder_1750872143.jpg', '00:02:15',
        'Pharmacist, Apoc Krysis - NICE GUYS WHO DO GOOD THINGS_1750872143.mp3'),
       (1834728284, 'Black Hole', 'Metalcore', '2022-10-14', 'folder_1750871086.jpg', '00:02:59',
        'We Came As Romans, Caleb Shomo - Black Hole_1750871086.mp3'),
       (1876663028, 'Dear Diary', 'Metal', '1980-03-12', 'folder_1750856999.jpg', '00:02:45',
        'Bring Me The Horizon - Dear Diary,_1750856999.mp3'),
       (1915135781, 'Overdose', 'Metalcore', '2025-03-28', '685d5e022a06f.jpg', '00:03:08',
        'Memphis May Fire, Blindside - Overdose - feat. Blindside_1750949378.mp3'),
       (2000247808, 'Deathwish', 'Electroclash', '1970-01-01', 'folder_1750870537.jpg', '00:04:48',
        'akiaura, LONOWN, Baby Jane - Deathwish_1750870537.mp3'),
       (2012269410, 'Hell Is Empty', 'Metalcore', '2025-03-28', '685d5d3ed2d94.jpg', '00:02:58',
        'Memphis May Fire - Hell Is Empty_1750949182.mp3'),
       (2071909473, 'Shapeshifter', 'Metalcore', '2025-03-28', '685d5db37f87d.jpg', '00:03:06',
        'Memphis May Fire - Shapeshifter_1750949299.mp3'),
       (2096802662, 'Push On', 'Memphis Rap', '2021-03-01', '685d6207b396a.jpg', '00:04:08', '685d6207b3c62.mp3'),
       (2099263854, 'FEUD', 'Memphis Rap', '2025-04-25', 'folder_1750871972.jpg', '00:01:45',
        'Pharmacist - FEUD_1750871972.mp3'),
       (2101949830, 'Infection', 'Metalcore', '2025-03-28', '685d5d5854013.jpg', '00:02:48',
        'Memphis May Fire - Infection_1750949208.mp3');

CREATE TABLE `user`
(
    `userID`       int(11)      NOT NULL,
    `username`     varchar(255) NOT NULL,
    `email`        varchar(255) NOT NULL,
    `userPassword` varchar(255) NOT NULL,
    `salt`         varchar(255) NOT NULL,
    `isAdmin`      tinyint(1)   NOT NULL DEFAULT 0,
    `isArtist`     tinyint(1)   NOT NULL DEFAULT 0,
    `imageName`    varchar(255)          DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `user` (`userID`, `username`, `email`, `userPassword`, `salt`, `isAdmin`, `isArtist`, `imageName`)
VALUES (164085589, 'Caleb Shomo', 'calebshomo@beatstream.com',
        '5de86f0a02c660c69becddf1397cd33acc4c07026768f705a035a5342d9493ba', 'P$l#0RwVRGBYPRz2', 0, 1,
        '685bee943d29f.jpg'),
       (196994981, 'Apoc Crisis', 'apoccrisis@beatstream.com',
        'c8a00eccec6dfdec7fa55898e740c2342c3c30f392cb2ac63ae7fc5504cfaf05', 'C@YDLFHVbBXyhi5-', 0, 1, ''),
       (452377540, 'Klaypex', 'klaypex@beatstream.com',
        '7fdbaf0bd44649aa882280968c312068741026eb088bc5853e84e81e4596a132', 'xI81r2/B?ca&EKg=', 0, 1,
        '685befcbbe0a8.webp'),
       (515829910, 'LasseS', 'lasse.schaller@outlook.com',
        '8f46b59e2bc0a4527007e2e834c280da808ca62c6c18cb95a0fb01437dc8e929', 'G<Y7nOf(R0oz?grp', 0, 1,
        'Download1750855614.jfif'),
       (588032118, 'Lasse', 'lasse.schaller@outlook.de',
        '8b2d0584f0aec21532f9cfd0f7bd6c3bbf544009a78769eea0a9fb28e12758cd', '(kYlWD:-QQ28s2b)', 0, 1,
        'Download1750854700.jfif'),
       (621945341, '$werve', '$werve@beatstream.com',
        'fb22bae7327b9c80c402fd1584637e104669afa415f5cbfbd272decdfb550b11', 'P,n7LiEBlP%)Zihe', 0, 1,
        '685bef017fcc7.jfif'),
       (779155170, 'Zero 9:36', 'zero936@beatstream.com',
        'ada5fba1f94729d7739bf8c8b253435c6bc4c1ce265fe08cf5325601b212e6a5', ')$bw8b?)Z5eP6(tA', 0, 1,
        '685beeafc4d44.webp'),
       (907911095, 'ManfredL', 'manfredlaitko@gmail.com',
        '49cac8c6624087b6e5967d4c94b7d32d1accf426a856627963056fa02a07ba51', '=fVPjyIqSGpt%@k2', 0, 1, ''),
       (1095847301, 'Olya Holiday', 'olyaholiday@beatstream.com',
        '926c525dfe9bf444de6f9bd29d4b034650d229a347a31f031fa6c6ddaf859fa9', '$hNcsdOht1$84b!;', 0, 1,
        '685c297543f6d.jpg'),
       (1103097431, 'Ghostface Playa', 'ghostfaceplaya@beatstream.com',
        'ae33700b188ca86f396823bbe26f3a2f8f5dacb3c4d14d1fa0b817cd69852466', 'BvDVigQH9(;Yqww=', 0, 1,
        '685bef302403c.jfif'),
       (1143029010, 'We Came As Romans', 'wecameasromans@beatstream.com',
        '3cd31dac964d9da3e800d52134b966e436814ae75fdcfc33e205ba4be6ba561b', 'wQ<DVx28mGtjHf&r', 0, 1,
        '685bee6b530f3.jpg'),
       (1271926637, 'Towa', 'towa@beatstream.com', '345588628da6a1d57c0037522c50ed850a5e11e6d8599a3544d8b9d29bd23f13',
        '1QxB&C<HE0o?W0Y9', 0, 1, '685bef5287eef.jfif'),
       (1354920019, 'Blindside', 'blindside@beatstream.com',
        '49a42f2d03c6cb2af841322be69f349cf031c813c97c8344d0ec20dbc1dce1f5', 'vsOgwDQxZzA)bo1i', 0, 1,
        '685bef825a113.jpg'),
       (1377223767, 'ENMY', 'enmy@beatstream.com', 'f61c61e14c786855d8ff5f9cdca765b360c0f4bded827f7aab886077f2729d03',
        'w4S)DB:KS&bol*Dp', 0, 1, '685bef7c879da.webp'),
       (1499462201, 'Bring Me The Horizon', 'bringmethehorizon@beatstream.com',
        '9ac6b9ace452d92bb3c0b9d62d0ce9c21985eb2fe054d9d1ebb970f09e25365a', 'WqXPf<DCsSj$?wy9', 0, 1,
        '685bf0f94f9ab.jpg'),
       (1526269191, 'Electric Callboy', 'electriccallboy@beatstream.com',
        '6d11c393eeaa04fd6d420b7284f9ef432efdd1a4ddbacdad2984e85418ce58a6', 'U(<XA/g8K/fbkM<3', 0, 1,
        '685bef5e336cb.jpg'),
       (1553281647, 'Pharmacist', 'pharmacist@beatstream.com',
        '071f1cdebf6e9ed99a2471e283f58a4e036c993a27fdf74aa5f5b476ab3d80a8', 'Q;9zKsFlmx6GJxsc', 0, 1,
        '685beeebf2002.jfif'),
       (1564642731, 'Baby Jane', 'babyjane@beatstream.com',
        '6ecb6b88a9c2c6cf20967c4a3206cf02ac0bfcded2d039f9bc1c4376df160452', ',U)234x$ph:j%wmX', 0, 1,
        '685c288c92877.jpg'),
       (1653533046, 'AJ Channer', 'ajchanner@beatstream.com',
        '451d44a63a5463e0431528ff1f5dd1a9baf24303a26562cbb2410003d62bb6d3', 'G:g7Q,S0#rS)$Ta5', 0, 1,
        '685bef96634c2.jpg'),
       (1711997645, 'root', 'root@root.root', 'a421fc12fa867397d5ffe9ef03da0356ed5cc850975f0de58ea8b95e82e341f3',
        'ddHkq?HD:Sr%J;dg', 1, 0, 'rootuser1750695678.webp'),
       (1736983402, 'GHOST DATA', 'ghostdata@beatstream.com',
        '08558487a31efb860382c4fe11a12bddb56ddb6678277d5739ffda1918c234a3', 'P>G-5jYGWcKr!N/h', 0, 1,
        '685befa037f47.png'),
       (1755109461, 'LONOWN', 'lonown@beatstream.com',
        'e9703f3b29236e6d56883724d3a643e1513e7e9e18268b0c2dbf327dda98bede', 'lkKER2)T2&R>0T:7', 0, 1,
        '685beef2ee242.jfif'),
       (1759447542, 'DJ Pointless', 'djpointless@beatstream.com',
        'a0ca3c289bbbe0c2a27f440a265e52b071a7acb320a2ce11155308759ce6f0b6', 'w8AjZyEfScvwhmGK', 0, 1,
        '685c28fbcda18.jpg'),
       (1826670471, 'akiaura', 'akiaura@beatstream.com',
        '086f9c6d8a0caa0a41b86e60d28b913157af2d9b868629585ddb1447da47d209', 'dReV.&lt;..hHi,R-K)', 0, 1,
        '685c2799ae08a.webp'),
       (1917040665, 'Kingpin skinny pimp', 'kingpinskinnypimp@beatstream.com',
        '2fbd70c4f2d1cc46e9d5019a4041bdb3a91377c3da082f31e818dc31fa6559ac', 'w*xr0.51qG1AVgl6', 0, 1,
        '685bef4590232.jfif'),
       (1994596577, 'KXLLYXU', 'kxllyxu@beatstream.com',
        '1618e7a7eed79d5f93f2c68fc46be45254d04cadb8a260d9fb8102aacd1ef956', 'rKLYLt<JE.i-2HWR', 0, 1,
        '685befb1d6c67.jfif'),
       (2026845397, 'Memphis May Fire', 'memphismayfire@beatstream.com',
        'b1235a0219990d7b8e0721dd75519ef0d4d00d8c87b42f34df81711b560ffd87', 'XlK,gT)hcmykDelA', 0, 1,
        '685bef709faaf.jpg');


ALTER TABLE `album`
    ADD PRIMARY KEY (`albumID`);

ALTER TABLE `artist`
    ADD PRIMARY KEY (`artistID`),
    ADD UNIQUE KEY `name` (`name`),
    ADD KEY `userID` (`userID`);

ALTER TABLE `in_album`
    ADD PRIMARY KEY (`songID`, `albumId`),
    ADD KEY `albumId` (`albumId`);

ALTER TABLE `in_playlist`
    ADD PRIMARY KEY (`songID`, `playlistID`),
    ADD KEY `playlistID` (`playlistID`);

ALTER TABLE `playlist`
    ADD PRIMARY KEY (`playlistID`),
    ADD KEY `creatorID` (`creatorID`);

ALTER TABLE `releases_album`
    ADD PRIMARY KEY (`artistID`, `albumID`),
    ADD KEY `albumID` (`albumID`);

ALTER TABLE `releases_song`
    ADD PRIMARY KEY (`artistID`, `songID`),
    ADD KEY `songID` (`songID`);

ALTER TABLE `song`
    ADD PRIMARY KEY (`songID`);

ALTER TABLE `user`
    ADD PRIMARY KEY (`userID`),
    ADD UNIQUE KEY `username` (`username`),
    ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `artist`
    ADD CONSTRAINT `artist_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

ALTER TABLE `in_album`
    ADD CONSTRAINT `in_album_ibfk_1` FOREIGN KEY (`songID`) REFERENCES `song` (`songID`),
    ADD CONSTRAINT `in_album_ibfk_2` FOREIGN KEY (`albumId`) REFERENCES `album` (`albumID`);

ALTER TABLE `in_playlist`
    ADD CONSTRAINT `in_playlist_ibfk_1` FOREIGN KEY (`songID`) REFERENCES `song` (`songID`),
    ADD CONSTRAINT `in_playlist_ibfk_2` FOREIGN KEY (`playlistID`) REFERENCES `playlist` (`playlistID`);

ALTER TABLE `playlist`
    ADD CONSTRAINT `playlist_ibfk_1` FOREIGN KEY (`creatorID`) REFERENCES `user` (`userID`);

ALTER TABLE `releases_album`
    ADD CONSTRAINT `releases_album_ibfk_1` FOREIGN KEY (`artistID`) REFERENCES `artist` (`artistID`),
    ADD CONSTRAINT `releases_album_ibfk_2` FOREIGN KEY (`albumID`) REFERENCES `album` (`albumID`);

ALTER TABLE `releases_song`
    ADD CONSTRAINT `releases_song_ibfk_1` FOREIGN KEY (`artistID`) REFERENCES `artist` (`artistID`),
    ADD CONSTRAINT `releases_song_ibfk_2` FOREIGN KEY (`songID`) REFERENCES `song` (`songID`);
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
