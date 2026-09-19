<?php
$GLOBALS['PROJECT_ROOT'] = '';
$GLOBALS['PROJECT_ROOT_DIR'] = $_SERVER['DOCUMENT_ROOT'] . $GLOBALS['PROJECT_ROOT'];
$GLOBALS['REQUIRES_ACCESS'] = false;
$GLOBALS['ADMIN_REQUIRES_ACCESS'] = true;

function includeComponent($componentPath, $variables = [])
{
	$fullPath = $GLOBALS['PROJECT_ROOT_DIR'] . '/components/' . $componentPath;
	if (file_exists($fullPath)) {
		extract($variables);
		require_once $fullPath;
	} else {
		echo "Component not found: " . htmlspecialchars($componentPath);
	}
}

enum ControllerType: string
{
	case SONG_CONTROLLER = 'SongController.php';
	case ARTIST_CONTROLLER = 'ArtistController.php';
	case USER_CONTROLLER = 'UserController.php';
	case PLAYLIST_CONTROLLER = 'PlaylistController.php';
	case ALBUM_CONTROLLER = 'AlbumController.php';
	case API_CONTROLLER = 'APIController.php';
	case IMAGE_CONTROLLER = 'ImageController.php';
}

function includeController(ControllerType $controller)
{
	$fullPath = $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/' . $controller->value;
	if (file_exists($fullPath)) {
		require_once $fullPath;
	} else {
		echo "Controller not found: " . htmlspecialchars($controller->value);
	}
}

function requireAccess()
{
	require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_access.php';
}

function requireAdminAccess($redirect = null)
{
	$redirect = $redirect ?? null;
	require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/validate_admin_access.php';
}

function includeConverter()
{
	require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/converter.php';
}
