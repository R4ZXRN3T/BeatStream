<?php
session_start();
$_SESSION['account_loggedin'] = false;
session_unset();
session_destroy();
header('Location: ../BeatStream/');