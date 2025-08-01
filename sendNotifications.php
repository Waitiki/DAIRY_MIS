<?php
include 'db_connection.php';
include 'functions.php';

$successMessage = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'])) {
        // Handle sending notification here

        // For demonstration purposes, let's assume the message and recipient data are provided in the form
        $notificationMessage = $_POST['message'];
        $recipientType = $_POST['recipient_type']; // 'all' for all farmers, 'specific' for specific farmers

        session_start();
        // Get admin ID from the session (modify as per your session handling)
        $adminId = $_SESSION['user_id'];

        // Get admin username from the database based on admin ID
        $adminUsernameQuery = "SELECT username FROM admins WHERE id = '$adminId'";
        $adminUsernameResult = $conn->query($adminUsernameQuery);

        if ($adminUsernameResult->num_rows > 0) {
            $adminUsernameRow = $adminUsernameResult->fetch_assoc();
            $adminUsername = $adminUsernameRow['username'];

             // Get the current date and time
             $currentDateTime = date("Y-m-d H:i:s");
            if ($recipientType === 'all') {
                // Send notification to all farmers
                $insertNotificationQuery = "INSERT INTO notifications (farmer_id, message) SELECT id, CONCAT('$notificationMessage. Sent by: $adminUsername (Admin-ID: $adminId) on $currentDateTime') AS message FROM farmers";
                $conn->query($insertNotificationQuery);
            } elseif ($recipientType === 'specific') {
                // Send notification to specific farmers
                $specificFarmerId = $_POST['farmer_id'];

                // Check if the specific farmer exists
                $specificFarmerQuery = "SELECT * FROM farmers WHERE id = '$specificFarmerId'";
                $specificFarmerResult = $conn->query($specificFarmerQuery);

                if ($specificFarmerResult->num_rows > 0) {
                    // Insert notification into the notifications table
                    $insertNotificationQuery = "INSERT INTO notifications (farmer_id, message) VALUES ('$specificFarmerId', CONCAT('$notificationMessage. Sent by: $adminUsername (Admin-ID: $adminId) on $currentDateTime'))";
                    $conn->query($insertNotificationQuery);
                }
            }

            // Set success message
            $successMessage = "Notification sent successfully";

            //Redirect to avoid form resubmission on page refresh
            header("Location: sendNotifications.php");
            exit();
        }
    }
}

// Fetch the list of farmers for the form
$farmersQuery = "SELECT * FROM farmers";
$farmersResult = $conn->query($farmersQuery);

// Fetch notifications for the Notification Dashboard
$notificationsQuery = "SELECT * FROM notifications";
$notificationsResult = $conn->query($notificationsQuery);
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification & Notification Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        textarea,
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        #specific_farmers {
            display: none;
        }

        .notification-dashboard {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .notification {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .notification p {
            margin: 0;
        }

        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 10px;
        }

        .confirmation-dialog {
            display: none;
            background-color: rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            justify-content: center;
            align-items: center;
        }

        .confirmation-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .confirmation-box p {
            margin: 0;
        }

        .confirmation-box button {
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        .confirmation-box button.cancel {
            background-color: #ccc;
        }
    </style>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="sidebar">
        <header>ADMIN DASHBOARD</header>
        <ul>
            <li><a href="add_admin.php"><i class="fas fa-stream"></i>Admins</a></li>
            <li><a href="add_farmer.php"><i class="fas fa-stream"></i> Farmers</a></li>
            <li><a href="records.php"><i class="fas fa-sliders-h"></i>records</a></li>
            <li><a href="sendNotifications.php"><i class="fas fa-sliders-h"></i>Notifications</a></li>
            <li><a href="viewfeedbacks.php"><i class="fas fa-sliders-h"></i>Feedback</a></li>
            <li><a href="reports.php"><i class="fas fa-question-circle"></i>reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    <h2>Send Notification</h2>

    <form method="post" action="sendNotifications.php" onsubmit="return confirmNotification()">
        <div class="success-message">
            <?php echo $successMessage; ?>
        </div>

        <label for="notification_message">Notification Message:</label>
        <textarea name="message" id="notification_message" required></textarea>

        <label for="recipient_type">Recipient Type:</label>
        <select name="recipient_type" id="recipient_type" required>
            <option value="all">Send to All Farmers</option>
            <option value="specific">Send to Specific Farmer</option>
        </select>

        <div id="specific_farmers">
            <label for="specific_farmer_id">Enter Specific Farmer ID:</label>
            <input type="text" name="farmer_id" id="specific_farmer_id">
        </div>

        <button type="submit" name="send_notification">Send Notification</button>
    </form>

    <script>
        // Show/hide the specific farmers input based on the recipient type
        document.getElementById('recipient_type').addEventListener('change', function () {
            var specificFarmersDiv = document.getElementById('specific_farmers');
            specificFarmersDiv.style.display = this.value === 'specific' ? 'block' : 'none';
        });

        // Confirmation dialog
        function confirmNotification() {
            var recipientType = document.getElementById('recipient_type').value;
            var confirmationMessage = "";

            if (recipientType === 'all') {
                confirmationMessage = "Are you sure you want to send this message to all farmers?";
            } else if (recipientType === 'specific') {
                var specificFarmerId = document.getElementById('specific_farmer_id').value;
                confirmationMessage = "Are you sure you want to send this message to the farmer with ID " + specificFarmerId + "?";
            }

            return confirm(confirmationMessage);
        }
    </script>

    <div class="notification-dashboard">
        <h2>Sent Notifications</h2>
        <?php
        if ($notificationsResult && $notificationsResult->num_rows > 0) {
            while ($notification = $notificationsResult->fetch_assoc()) {
                echo '<div class="notification">';
                echo '<p><strong>Farmer ID:</strong> ' . $notification['farmer_id'] . '</p>';
                echo '<p><strong>Message:</strong> ' . $notification['message'] . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No notifications available.</p>';
        }
        ?>
    </div>

</body>

</html>
