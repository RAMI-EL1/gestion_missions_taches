<?php
session_start(); // Start the session

// Unset all of the session variables
$_SESSION = [];

// If it's desired to kill the session, also destroy the session.
session_destroy();

// Redirect to the login page
header("Location: home.php");
exit;
?>
