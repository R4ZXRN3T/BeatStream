<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/dbConnection.php';
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/tools/cleanup_media.php';

$stmt = DBConn::getConn()->prepare("DELETE FROM song WHERE song.songID IN (SELECT song.songID FROM song WHERE NOT song.songID IN (SELECT DISTINCT releases_song.songID FROM releases_song))");
$stmt->execute();
$stmt->close();

$stmt = DBConn::getConn()->prepare("DELETE FROM album WHERE album.albumID IN (SELECT album.albumID FROM album WHERE NOT album.albumID IN (SELECT DISTINCT releases_album.albumID FROM releases_album))");
$stmt->execute();
$stmt->close();

$stmt = DBConn::getConn()->prepare("DELETE FROM api_key WHERE canExpire = TRUE AND expirationDate < NOW() - INTERVAL 30 DAY");
$stmt->execute();
$stmt->close();
