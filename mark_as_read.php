<?php
include 'db_connection.php';  // Include your database connection code
include 'functions.php'; // Include your functions

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['notification_id'])) {
    markNotificationsAsRead($conn, $_POST['notification_id']);
}

header("Location: farmerdb.php");  // Redirect back to the dashboard
exit();
?>

