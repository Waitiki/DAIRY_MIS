<?php

if (!function_exists('checkAdminAuthentication')) {
    function checkAdminAuthentication() {
        session_start();

        if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin($userID) {
        // Your isAdmin function code...
        $host = "localhost";
        $user = "root";
        $password = "";
        $db = "dairy";

        $conn = new mysqli($host, $user, $password, $db);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if $_SESSION['user_id'] is set
        if (!isset($_SESSION['user_id'])) {
            $conn->close();
            return false;
        }

        $userID = $_SESSION['user_id'];

        $adminCheckQuery = "SELECT * FROM admins WHERE id = $userID";
        $adminCheckResult = $conn->query($adminCheckQuery);

        $conn->close();

        return ($adminCheckResult && $adminCheckResult->num_rows > 0);
    }
}
// functions.php

if (!function_exists('checkFarmerAuthentication')) {
    function checkFarmerAuthentication() {
        session_start();

        if (!isset($_SESSION['user_id']) || !isFarmer($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
    }
}

if (!function_exists('isFarmer')) {
    function isFarmer($userID) {
        // Your isFarmer function code...
        $host = "localhost";
        $user = "root";
        $password = "";
        $db = "dairy";

        $conn = new mysqli($host, $user, $password, $db);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $userID = $_SESSION['user_id'];

        $farmerCheckQuery = "SELECT * FROM farmers WHERE id = $userID";
        $farmerCheckResult = $conn->query($farmerCheckQuery);

        $conn->close();

        return ($farmerCheckResult && $farmerCheckResult->num_rows > 0);
    }
}

if (!function_exists('getFarmerNotifications')) {
    function getFarmerNotifications($conn, $farmerId) {
        $query = "SELECT * FROM notifications WHERE farmer_id = ? AND is_read = 0";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            return null;
        }

        $stmt->bind_param("i", $farmerId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            return $notifications;
        } else {
            echo "Error getting unread farmer notifications for Farmer ID: $farmerId";
            return null;
        }
    }
}




if (!function_exists('markNotificationsAsRead')) {
    function markNotificationsAsRead($conn, $notificationId) {
        $query = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
        $stmt = $conn->prepare($query);
    
        if ($stmt === false) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            return;
        }
    
        $stmt->bind_param("i", $notificationId);
    
        if (!$stmt->execute()) {
            echo "Error marking notification as read: " . $stmt->error;
        }
    
        $stmt->close();
    }
}

if (!function_exists('getFarmerName')) {
    function getFarmerName($conn, $farmerID) {
        $query = "SELECT name FROM farmers WHERE id = ?";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            return null;
        }

        $stmt->bind_param("i", $farmerID);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['name'];
        } else {
            echo "Error getting farmer name for Farmer ID: $farmerID";
            return null;
        }
    }
}

function getFarmerRecords($conn, $farmerId) {
    $records = array();

    // Replace 'records_table' with the actual name of your records table
    $query = "SELECT record_Id, quantity, rate, date_time FROM records WHERE farmer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $farmerId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    $stmt->close();

    return $records;
}


