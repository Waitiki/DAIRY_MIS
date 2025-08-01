<?php
include 'db_connection.php';
include 'functions.php';

function markFeedbackAsRead($conn, $feedbackId)
{
    $updateQuery = "UPDATE feedback_table SET is_read = TRUE WHERE feedback_id = $feedbackId";
    $result = $conn->query($updateQuery);
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $feedbackId = $_POST['feedback_id'];
    markFeedbackAsRead($conn, $feedbackId);
    // Redirect to avoid form resubmission on page refresh
    header("Location: viewfeedbacks.php");
    exit();
}

// Check if the admin wants to view all feedbacks (read and unread)
$showAll = isset($_GET['show_all']);

// Select feedbacks based on the read status
$readStatus = $showAll ? '' : ' AND is_read = FALSE';
$feedbackQuery = "SELECT * FROM feedback_table WHERE 1$readStatus";
$feedbackResult = $conn->query($feedbackQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedbacks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('your-background-image.jpg');
            background-size: cover;
            background-blur: 5px;
            margin: 0;
            padding: 0;
        }

        .container-wrap {
            margin: 20px;
            margin-left: 200px;
        }

        .feedback {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            padding: 20px;
            margin-bottom: 20px;
            width: 80%;
            display: flex;
            flex-direction: column;
        }

        .feedback-header {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .feedback-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .feedback-message {
            margin-bottom: 10px;
        }

        .timestamp {
            color: #888;
        }

        .mark-as-read {
            background-color: #4CAF50;
            color: #fff;
            padding: 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
    <link rel="stylesheet" href="style.css">
</head>


<body>

<div class="all-wrap">
    <div class="sidebar">
        <header>ADMIN DASHBOARD</header>
        <ul>
            <li><a href="add_admin.php"><i class="fas fa-stream"></i>Admins</a></li>
            <li><a href="add_farmer.php"><i class="fas fa-stream"></i> Farmers</a></li>
            <li><a href="records.php"><i class="fas fa-sliders-h"></i>records</a></li>
            <li><a href="sendNotifications.php"><i class="fas fa-sliders-h"></i>Notifications</a></li>
            <li><a href="viewfeedbacks.php"><i class="fas fa-sliders-h"></i>feedback</a></li>
            <li><a href="reports.php"><i class="fas fa-question-circle"></i>reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container-wrap">
        <div class="feedback-header">
            <h2>Feedbacks</h2>
            <p>
                <a href="viewfeedbacks.php">Unread Feedbacks</a> |
                <a href="viewfeedbacks.php?show_all=1">All Feedbacks</a>
            </p>
        </div>

        <?php
        if ($feedbackResult && $feedbackResult->num_rows > 0) {
            while ($row = $feedbackResult->fetch_assoc()) {
                echo '<div class="feedback">';
                echo '<div class="feedback-details">';
                echo '<div><strong>Farmer ID:</strong> ' . $row['farmer_id'] . '</div>';
                echo '<div class="timestamp"><strong>Timestamp:</strong> ' . $row['timestamp'] . '</div>';
                echo '</div>';
                echo '<div class="feedback-details">';
                echo '<div><strong>Farmer Name:</strong> ' . $row['farmer_name'] . '</div>';
                echo '</div>';
                echo '<div class="feedback-message">';
                echo '<strong>Feedback Message:</strong> ' . $row['feedback_message'];
                echo '</div>';
                echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
                echo '<input type="hidden" name="feedback_id" value="' . $row['feedback_id'] . '">';
                echo '<button type="submit" name="mark_as_read" class="mark-as-read">Mark as Read</button>';
                echo '</form>';
                echo '</div>';
            }
        } else {
            echo '<p>No feedbacks available.</p>';
        }

        // Close the database connection
        $conn->close();
        ?>

    </div>

</div>


</body>
</html>
