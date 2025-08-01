<!-- feedback.php -->
<?php
// Include your database connection and functions code here
include 'db_connection.php';
include 'functions.php';

session_start();
// Notification variables
$notificationMessage = '';
$notificationClass = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['feedback'])) {
    // Process and store the feedback
    $farmerId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $farmerName = ($farmerId !== null) ? getFarmerName($conn, $farmerId) : null;
    $feedbackMessage = $_POST['feedback'];

    // Save feedback to the database
    if (saveFeedback($conn, $farmerId, $farmerName, $feedbackMessage)) {
        // Set notification if feedback is successfully sent
        $notificationMessage = 'Feedback sent successfully!';
        $notificationClass = 'success';
    } else {
        // Set notification if there was an error
        $notificationMessage = 'Error sending feedback. Please try again.';
        $notificationClass = 'error';
    }
}

// Function to save feedback to the database
function saveFeedback($conn, $farmerId, $farmerName, $feedbackMessage) {
    // Adjust the table name and column names based on your database schema
    $tableName = 'feedback_table';
    $columns = ['farmer_id', 'farmer_name', 'feedback_message'];

    // Build the SQL query
    $sql = "INSERT INTO $tableName (" . implode(',', $columns) . ") VALUES (?, ?, ?)";
    
    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('iss', $farmerId, $farmerName, $feedbackMessage);

    if (!$stmt->execute()) {
        return false;
    }

    $stmt->close();

    return true;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <style>


.notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #4CAF50;
            color: white;
        }

        .error {
            background-color: #f44336;
            color: white;
        }



        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background-color: #2c3e50;
            padding: 20px;
            color: #ecf0f1;
            text-align: center;
        }

        nav {
            background-color: #34495e;
            padding: 10px;
        }

        nav a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 10px;
            margin: 0 10px;
        }

        section {
            padding: 20px;
        }

        .dashboard-item {
            background-color: #ecf0f1;
            padding: 20px;
            margin: 10px;
            flex: 1;
            min-width: 300px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        footer {
            background-color: #2c3e50;
            padding: 10px;
            color: #ecf0f1;
            text-align: center;
        }

        /* Add additional styling as needed */
        form {
            margin-top: 20px;
            
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <h1>Feedback</h1>
    </header>

    <nav>
        <a href="farmerdb.php">Dashboard</a>
        <a href="farmerreports.php">Reports</a>
        <a href="profile.php">Profile</a>
        <a href="feedback.php">Feedback</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php">Logout</a>
    </nav>

    <section>
        <div class="dashboard-item">
            <h2>Provide Feedback</h2>

            <!-- Display notification if present -->
            <?php if (!empty($notificationMessage)) : ?>
                <div class="notification <?php echo $notificationClass; ?>">
                    <?php echo $notificationMessage; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <label for="feedback">Your Feedback:</label>
                <textarea name="feedback" id="feedback" rows="4" cols="50" required></textarea>
                <br>
                <input type="submit" value="Submit Feedback">
            </form>
        </div>
    </section>

    <footer>
        &copy; 2023 Farmer Dashboard
    </footer>
</body>
</html>
