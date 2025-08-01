<?php
include 'db_connection.php';
include 'functions.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message']) && isset($_POST['recipient_type'])) {
        $notificationMessage = $_POST['message'];
        $recipientType = $_POST['recipient_type'];
        session_start();
        $adminId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        if (!$adminId) {
            $errorMessage = "Error: Admin not authenticated.";
        } else {
            $stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
            $stmt->bind_param("i", $adminId);
            $stmt->execute();
            $adminUsernameResult = $stmt->get_result();

            if ($adminUsernameResult->num_rows > 0) {
                $adminUsernameRow = $adminUsernameResult->fetch_assoc();
                $adminUsername = $adminUsernameRow['username'];
                $currentDateTime = date("Y-m-d H:i:s");
                $messageWithMeta = mysqli_real_escape_string($conn, "$notificationMessage. Sent by: $adminUsername (Admin-ID: $adminId) on $currentDateTime");

                if ($recipientType === 'all') {
                    $stmt = $conn->prepare("INSERT INTO notifications (farmer_id, message) SELECT id, ? FROM farmers");
                    $stmt->bind_param("s", $messageWithMeta);
                    if ($stmt->execute()) {
                        $successMessage = "Notification sent successfully to all farmers.";
                    } else {
                        $errorMessage = "Error sending notification to all farmers: " . $stmt->error;
                    }
                    $stmt->close();
                } elseif ($recipientType === 'specific') {
                    $specificFarmerId = $_POST['farmer_id'];
                    if (empty($specificFarmerId)) {
                        $errorMessage = "Error: Farmer ID is required for specific notifications.";
                    } else {
                        $stmt = $conn->prepare("SELECT id FROM farmers WHERE id = ?");
                        $stmt->bind_param("s", $specificFarmerId);
                        $stmt->execute();
                        $specificFarmerResult = $stmt->get_result();

                        if ($specificFarmerResult->num_rows > 0) {
                            $stmt = $conn->prepare("INSERT INTO notifications (farmer_id, message) VALUES (?, ?)");
                            $stmt->bind_param("ss", $specificFarmerId, $messageWithMeta);
                            if ($stmt->execute()) {
                                $successMessage = "Notification sent successfully to Farmer ID $specificFarmerId.";
                            } else {
                                $errorMessage = "Error sending notification: " . $stmt->error;
                            }
                            $stmt->close();
                        } else {
                            $errorMessage = "Error: Farmer ID $specificFarmerId does not exist.";
                        }
                    }
                }
            } else {
                $errorMessage = "Error: Admin username not found.";
            }
            $stmt->close();
        }
        header("Location: sendNotifications.php");
        exit();
    }
}

$farmersQuery = "SELECT id, name FROM farmers";
$farmersResult = $conn->query($farmersQuery);

$notificationsQuery = "SELECT * FROM notifications ORDER BY timestamp DESC";
$notificationsResult = $conn->query($notificationsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification & Notification History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #45a049;
            --light-gray: #f5f5f5;
            --medium-gray: #e0e0e0;
            --dark-gray: #333;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
            color: var(--dark-gray);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary-color);
        }

        .card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-gray);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path fill="%234CAF50" d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center;
            background-size: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn i {
            margin-right: 8px;
        }

        .success-message {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--primary-dark);
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
            display: flex;
            align-items: center;
        }

        .error-message {
            background-color: rgba(255, 0, 0, 0.1);
            color: #721c24;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            display: flex;
            align-items: center;
        }

        .success-message i, .error-message i {
            margin-right: 10px;
        }

        .notification-item {
            background-color: var(--white);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--medium-gray);
        }

        .notification-farmer {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .notification-time {
            font-size: 0.85rem;
            color: #666;
        }

        .notification-message {
            margin: 10px 0;
            line-height: 1.6;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--medium-gray);
        }

        /* Sidebar styles */
        .sidebar {
            background-color: #2c3e50;
            color: var(--white);
            width: 250px;
            position: fixed;
            height: 100%;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            background-color: #1a252f;
            text-align: center;
            font-weight: 600;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .sidebar ul li:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar ul li a {
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .sidebar ul li.active {
            background-color: var(--primary-color);
            border-left: 4px solid var(--white);
        }

        .sidebar ul li.active a {
            color: var(--white);
            font-weight: 600;
        }

        .sidebar ul li.active:hover {
            background-color: var(--primary-dark);
        }

        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
        }

        #check {
            display: none;
        }

        #btn, #cancel {
            position: fixed;
            cursor: pointer;
            background: #2c3e50;
            border-radius: 3px;
            z-index: 1001;
            color: var(--white);
            padding: 6px 10px;
            font-size: 18px;
            transition: all 0.3s;
            display: none;
        }

        #btn {
            top: 20px;
            left: 20px;
        }

        #cancel {
            top: 20px;
            left: 200px;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            
            #check:checked ~ .sidebar {
                left: 0;
            }
            
            #check:checked ~ .main-content {
                margin-left: 250px;
            }
            
            #btn, #cancel {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .notification-header {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <input type="checkbox" id="check">
    <label for="check">
        <i class="fas fa-bars" id="btn"></i>
        <i class="fas fa-times" id="cancel"></i>
    </label>
    
    <div class="sidebar">
        <div class="sidebar-header">ADMIN PANEL</div>
        <ul>
            <li><a href="admindb.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="add_admin.php"><i class="fas fa-user-shield"></i> Admins</a></li>
            <li><a href="add_farmer.php"><i class="fas fa-users"></i> Farmers</a></li>
            <li><a href="records.php"><i class="fas fa-clipboard-list"></i> Records</a></li>
            <li class="active"><a href="sendNotifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
            <li><a href="viewfeedbacks.php"><i class="fas fa-comment-alt"></i> Feedback</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
            <h2><i class="fas fa-bell"></i> Send Notification</h2>
            
            <?php if (!empty($successMessage)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <form method="post" action="sendNotifications.php" onsubmit="return confirmNotification()">
                    <div class="form-group">
                        <label for="notification_message" class="form-label">Notification Message</label>
                        <textarea name="message" id="notification_message" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="recipient_type" class="form-label">Recipient Type</label>
                        <select name="recipient_type" id="recipient_type" class="form-control" required>
                            <option value="all">Send to All Farmers</option>
                            <option value="specific">Send to Specific Farmer</option>
                        </select>
                    </div>
                    <div class="form-group" id="specific_farmers" style="display: none;">
                        <label for="specific_farmer_id" class="form-label">Select Farmer</label>
                        <select name="farmer_id" id="specific_farmer_id" class="form-control">
                            <option value="">Select a Farmer</option>
                            <?php
                            if ($farmersResult && $farmersResult->num_rows > 0) {
                                while ($farmer = $farmersResult->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($farmer['id']) . '">' . htmlspecialchars($farmer['name']) . ' (ID: ' . htmlspecialchars($farmer['id']) . ')</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="send_notification" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Notification
                    </button>
                </form>
            </div>
            
            <h2><i class="fas fa-history"></i> Notification History</h2>
            
            <div class="card">
                <?php if ($notificationsResult && $notificationsResult->num_rows > 0): ?>
                    <div class="notification-list">
                        <?php while ($notification = $notificationsResult->fetch_assoc()): ?>
                            <div class="notification-item">
                                <div class="notification-header">
                                    <span class="notification-farmer">
                                        <i class="fas fa-user"></i> Farmer ID: <?php echo htmlspecialchars($notification['farmer_id']); ?>
                                    </span>
                                    <span class="notification-time">
                                        <i class="far fa-clock"></i> <?php echo htmlspecialchars($notification['timestamp']); ?>
                                    </span>
                                </div>
                                <div class="notification-message">
                                    <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="far fa-bell-slash"></i>
                        <h3>No notifications sent yet</h3>
                        <p>Notifications you send will appear here</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('recipient_type').addEventListener('change', function() {
            var specificFarmersDiv = document.getElementById('specific_farmers');
            specificFarmersDiv.style.display = this.value === 'specific' ? 'block' : 'none';
            document.getElementById('specific_farmer_id').required = this.value === 'specific';
        });

        function confirmNotification() {
            var recipientType = document.getElementById('recipient_type').value;
            var confirmationMessage = "";

            if (recipientType === 'all') {
                confirmationMessage = "Are you sure you want to send this notification to all farmers?";
            } else if (recipientType === 'specific') {
                var specificFarmerId = document.getElementById('specific_farmer_id').value;
                if (!specificFarmerId) {
                    alert("Please select a farmer.");
                    return false;
                }
                var selectedOption = document.getElementById('specific_farmer_id').options[document.getElementById('specific_farmer_id').selectedIndex].text;
                confirmationMessage = "Are you sure you want to send this notification to " + selectedOption + "?";
            }

            return confirm(confirmationMessage);
        }
    </script>
</body>
</html>