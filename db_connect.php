<?php
// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_NAME', 'newforest');

// Connect to the database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if (!$conn) {
    die('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Set the character encoding
mysqli_set_charset($conn, 'utf8');
?>
