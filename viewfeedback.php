<!-- viewfeedbacks.php -->
<?php
session_start();

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "dairy";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function isAdmin($userID)
{
    $userID = $_SESSION['user_id'];

    $adminCheckQuery = "SELECT * FROM admins WHERE id = $userID";
    $adminCheckResult = queryDatabase($adminCheckQuery);

    return ($adminCheckResult && $adminCheckResult->num_rows > 0);
}

function queryDatabase($query)
{
    global $conn;

    $result = $conn->query($query);

    return $result;
}

$feedbackQuery = "SELECT * FROM feedback_table";
$feedbackResult = queryDatabase($feedbackQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedbacks</title>
    <style>
        /* Add table styles as needed */
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: #ecf0f1;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    <?php include 'admin_header.php'; ?>

    <div>
        <h2>Feedbacks</h2>
        <?php
        if ($feedbackResult && $feedbackResult->num_rows > 0) {
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Feedback ID</th>';
            echo '<th>Farmer ID</th>';
            echo '<th>Farmer Name</th>';
            echo '<th>Feedback Message</th>';
            echo '<th>Timestamp</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($row = $feedbackResult->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['feedback_id'] . '</td>';
                echo '<td>' . $row['farmer_id'] . '</td>';
                echo '<td>' . $row['farmer_name'] . '</td>';
                echo '<td>' . $row['feedback_message'] . '</td>';
                echo '<td>' . $row['timestamp'] . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No feedbacks available.</p>';
        }
        ?>
    </div>

</body>

</html>
