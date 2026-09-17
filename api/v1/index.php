<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/api/v1/router.php';
require $GLOBALS['PROJECT_ROOT_DIR'] . '/api/v1/routes.php';

$router->dispatch();
