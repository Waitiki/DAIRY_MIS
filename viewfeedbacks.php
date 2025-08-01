<?php
include 'db_connection.php';
include 'functions.php';

function markFeedbackAsRead($conn, $feedbackId) {
    $stmt = $conn->prepare("UPDATE feedback_table SET is_read = TRUE WHERE feedback_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $feedbackId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    return false;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $feedbackId = $_POST['feedback_id'];
    if (markFeedbackAsRead($conn, $feedbackId)) {
        $success_message = "Feedback marked as read successfully.";
    } else {
        $error_message = "Error marking feedback as read.";
    }
    // Redirect to avoid form resubmission on page refresh
    header("Location: viewfeedbacks.php" . (isset($_GET['show_all']) ? '?show_all=1' : ''));
    exit();
}

$showAll = isset($_GET['show_all']);
$readStatus = $showAll ? '' : ' AND is_read = FALSE';
$feedbackQuery = "SELECT * FROM feedback_table WHERE 1$readStatus ORDER BY timestamp DESC";
$feedbackResult = $conn->query($feedbackQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Feedbacks</title>
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
            padding: 20px;
            margin-bottom: 30px;
        }

        .feedback-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .feedback-item {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .feedback-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--medium-gray);
        }

        .feedback-meta {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .feedback-name {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .feedback-id {
            font-size: 0.9rem;
            color: var(--dark-gray);
        }

        .feedback-timestamp {
            font-size: 0.85rem;
            color: #666;
        }

        .feedback-message {
            margin: 15px 0;
            line-height: 1.6;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }

        .btn i {
            margin-right: 5px;
        }

        .filter-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-tab {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 600;
        }

        .filter-tab.active {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .filter-tab:not(.active) {
            background-color: var(--medium-gray);
            color: var(--dark-gray);
        }

        .filter-tab:not(.active):hover {
            background-color: #d0d0d0;
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

        .error-message {
            color: #ff0000;
            margin-bottom: 10px;
            text-align: center;
        }

        .success-message {
            color: var(--primary-color);
            margin-bottom: 10px;
            text-align: center;
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
            
            .feedback-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .filter-tabs {
                flex-direction: column;
                gap: 10px;
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
            <li><a href="sendNotifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
            <li class="active"><a href="viewfeedbacks.php"><i class="fas fa-comment-alt"></i> Feedback</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
            <h2><i class="fas fa-comment-alt"></i> Farmer Feedbacks</h2>
            <div class="card">
                <?php
                if ($error_message) {
                    echo "<div class='error-message'>" . htmlspecialchars($error_message) . "</div>";
                }
                if ($success_message) {
                    echo "<div class='success-message'>" . htmlspecialchars($success_message) . "</div>";
                }
                ?>
                <div class="filter-tabs">
                    <a href="viewfeedbacks.php" class="filter-tab <?php echo !$showAll ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Unread Feedbacks
                    </a>
                    <a href="viewfeedbacks.php?show_all=1" class="filter-tab <?php echo $showAll ? 'active' : ''; ?>">
                        <i class="fas fa-inbox"></i> All Feedbacks
                    </a>
                </div>
                <div class="feedback-container">
                    <?php
                    if ($feedbackResult && $feedbackResult->num_rows > 0) {
                        while ($row = $feedbackResult->fetch_assoc()) {
                            echo '<div class="feedback-item">';
                            echo '<div class="feedback-header">';
                            echo '<div class="feedback-meta">';
                            echo '<span class="feedback-name">' . htmlspecialchars($row['farmer_name']) . '</span>';
                            echo '<span class="feedback-id">Farmer ID: ' . htmlspecialchars($row['farmer_id']) . '</span>';
                            echo '</div>';
                            echo '<span class="feedback-timestamp"><i class="far fa-clock"></i> ' . htmlspecialchars($row['timestamp']) . '</span>';
                            echo '</div>';
                            echo '<div class="feedback-message">';
                            echo '<p>' . nl2br(htmlspecialchars($row['feedback_message'])) . '</p>';
                            echo '</div>';
                            if (!$row['is_read']) {
                                echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . (isset($_GET['show_all']) ? '?show_all=1' : '') . '">';
                                echo '<input type="hidden" name="feedback_id" value="' . htmlspecialchars($row['feedback_id']) . '">';
                                echo '<button type="submit" name="mark_as_read" class="btn btn-primary">';
                                echo '<i class="fas fa-check"></i> Mark as Read';
                                echo '</button>';
                                echo '</form>';
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="empty-state">';
                        echo '<i class="far fa-comment-dots"></i>';
                        echo '<h3>No feedbacks available</h3>';
                        echo '<p>There are currently no feedbacks to display.</p>';
                        echo '</div>';
                    }

                    $conn->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>