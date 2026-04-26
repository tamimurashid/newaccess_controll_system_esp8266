<?php
// config.php
// Purely procedural database connection

$db_host = '127.0.0.1:8889';
$db_user = 'root'; // MAMP default
$db_pass = 'root'; // MAMP default
$db_name = 'rfid_access_control';

// Attempt to connect to the database
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
