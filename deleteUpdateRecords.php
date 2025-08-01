<?php
include 'functions.php';
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'update') {
    handleUpdateAction($conn);
}

function handleUpdateAction($conn) {
    if (isset($_POST['updateRecordId']) && isset($_POST['updateQuantity']) && isset($_POST['updateRate']) && isset($_POST['updateDateTime'])) {
        $recordId = $_POST['updateRecordId'];
        $quantity = $_POST['updateQuantity'];
        $rate = $_POST['updateRate'];
        $dateTime = $_POST['updateDateTime'];

        if (!is_numeric($recordId) || !is_numeric($quantity)) {
            echo "Invalid input data.";
            return;
        }

        // Get the current details of the record before the update
        $currentRecordDetails = getCurrentRecordDetails($conn, $recordId);

        if (!$currentRecordDetails) {
            echo "Failed to get current record details.";
            return;
        }

        // Store the original date, time, quantity, and rate before the update
        $originalDateTime = $currentRecordDetails['date_time'];
        $originalQuantity = $currentRecordDetails['quantity'];
        $originalRate = $currentRecordDetails['rate'];

        session_start();

        if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
            // Get the name & ID of the admin who performed the update
            $adminId = $_SESSION['user_id'];
            $adminName = getAdminName($conn, $_SESSION['user_id']);
        } else {
            echo 'handle session first';
            // Add debugging information
            var_dump($_SESSION);  // Dump the session to see its current state
            die();  // Stop script execution for debugging
        }

        $updateSql = "UPDATE records SET quantity = ?, rate = ?, date_time = ?, original_date_time = ?, update_date_time = NOW(), updated_by_admin = ? WHERE record_Id = ?";
        $updateStmt = $conn->prepare($updateSql);

        if ($updateStmt === false) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            return;
        }

        $updateStmt->bind_param("dssssi", $quantity, $rate, $dateTime, $originalDateTime, $adminName, $recordId);

        if ($updateStmt->execute()) {
            // Fetch the updated date and time after the update
            $updatedDateTime = getCurrentRecordDetails($conn, $recordId)['update_date_time'];

            echo "Update action successful for Record ID: $recordId";

            // Notify the farmer about the record update
            $message = "Your record (Record ID: $recordId)\n";
            $message .= "which was recorded on Date/Time: $originalDateTime\n";
            $message .= "was updated by $adminName (Admin ID: $adminId)\n";
            $message .= "on Date/Time: $updatedDateTime\n";  // Use the updated date/time
            $message .= " .The original Quantity was: $originalQuantity\n";
            $message .= " ,but the Updated Quantity is now: $quantity\n";
            $message .= " .the Original Rate was: $originalRate\n";
            $message .= " ,but the Updated Rate is now: $rate\n";

            sendNotification($conn, $currentRecordDetails['farmer_Id'], $message, $originalQuantity, $quantity, $originalRate, $rate);
        } else {
            echo "Update action failed. Error: " . $updateStmt->error;
        }

        $updateStmt->close();
    } else {
        echo "Update data not provided.";
    }

    $conn->close();
}

function sendNotification($conn, $farmerId, $message, $originalQuantity, $updatedQuantity, $originalRate, $updatedRate) {
    // Insert a notification into the notifications table
    $notificationQuery = "INSERT INTO notifications (farmer_id, message, original_quantity, updated_quantity, original_rate, updated_rate) VALUES (?, ?, ?, ?, ?, ?)";
    $notificationStmt = $conn->prepare($notificationQuery);
    $notificationStmt->bind_param("issddd", $farmerId, $message, $originalQuantity, $updatedQuantity, $originalRate, $updatedRate);

    if ($notificationStmt->execute()) {
        echo "Notification sent to Farmer ID: $farmerId";
    } else {
        echo "Failed to send notification. Error: " . $notificationStmt->error;
    }

    $notificationStmt->close();
}

function getAdminName($conn, $adminId) {
    $query = "SELECT username FROM admins WHERE id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        return null;
    }

    $stmt->bind_param("i", $adminId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['username'];
    } else {
        echo "Error getting admin name for Admin ID: $adminId";
        return null;
    }
}

function getCurrentRecordDetails($conn, $recordId) {
    $query = "SELECT farmer_Id, date_time, quantity, rate, update_date_time FROM records WHERE record_Id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        return null;
    }

    $stmt->bind_param("i", $recordId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row;
    } else {
        echo "Error getting current record details for Record ID: $recordId";
        return null;
    }
}
?>
