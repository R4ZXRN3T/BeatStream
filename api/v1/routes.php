<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/v1/router.php';

$router = new Router();

// Songs
$router->get('/songs', 'songs/list.php');
$router->get('/songs/random', 'songs/random.php');
$router->get('/songs/{id}', 'songs/get.php');
$router->get('/songs/{id}/album', 'songs/album.php');

// Albums
$router->get('/albums', 'albums/list.php');
$router->post('/albums', 'albums/create.php');
$router->get('/albums/random', 'albums/random.php');
$router->get('/albums/{id}', 'albums/get.php');
$router->get('/albums/{id}/songs', 'albums/songs.php');

// Artists
$router->get('/artists', 'artists/list.php');
$router->post('/artists', 'artists/create.php');
$router->get('/artists/{id}', 'artists/get.php');
$router->get('/artists/{id}/albums', 'artists/albums.php');
$router->get('/artists/{id}/songs', 'artists/songs.php');

// Playlists
$router->get('/playlists', 'playlists/list.php');
$router->get('/playlists/{id}', 'playlists/get.php');
$router->get('/playlists/random', 'playlists/random.php');
$router->get('/playlists/{id}/songs', 'playlists/songs.php');
$router->post('/playlists/{id}/songs', 'playlists/add_song.php');

// Users
$router->get('/users', 'users/list.php');
$router->post('/users', 'users/create.php');
$router->get('/users/{id}', 'users/get.php');

// Authentication
$router->post('/auth/login', 'auth/login.php');
$router->post('/auth/logout', 'auth/logout.php');
$router->post('/auth/signup', 'auth/signup.php');
$router->patch('/auth/profile', 'auth/edit_profile.php');

// API keys
$router->post('/api-keys', 'api_keys/create.php');
$router->delete('/api-keys/{id}', 'api_keys/delete.php');

// Audio
$router->get('/audio/{id}', 'audio/get.php');
$router->get('/audio/{id}/download', 'audio/download.php');

// Images
$router->get('/images/{id}', 'images/image.php');
$router->get('/images/{id}/download', 'images/download.php');
