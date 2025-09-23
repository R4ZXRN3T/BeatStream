<?php
session_start();
$_SESSION = null; // Unset all session variables
session_unset();
session_destroy();
header("Location: {$GLOBALS['PROJECT_ROOT']}/");