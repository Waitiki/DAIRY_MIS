<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "dairy";

// Create a database connection
$conn = new mysqli($host, $user, $password, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>