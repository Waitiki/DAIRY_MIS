<?php
// Include your database connection and functions code here
include 'db_connection.php';
include 'functions.php';

// Check farmer authentication
checkFarmerAuthentication();
// session_start();
// Fetch farmer notifications
$farmerId = $_SESSION['user_id'];
$farmerName = getFarmerName($conn, $farmerId);
$notifications = getFarmerNotifications($conn, $farmerId);


// Check if the farmer has unread notifications
$notifications = getFarmerNotifications($conn, $farmerId);
// Mark notifications as read
markNotificationsAsRead($conn, $farmerId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard</title>
    <style>
        .msg-btn{
            background-color: #3498db;
            color: white;
            padding: 3px;
            font-size: medium;
            font-weight: 300;
            font-family: ui-monospace;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0px 0px 5px white;
            cursor: pointer;
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

        nav a.active {
            background-color: #2c3e50;
            color: #ecf0f1;
            /* border-radius: 4px; */
            border-top: 4px solid #3498db;
        }

        section {
            padding: 20px;
        }

        #filterDate {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
            box-shadow: 0px 0px 5px #3498db;
        }

            /* Add this CSS to style the form */
         #filterForm {
            margin-bottom: 20px;
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .records-table th, .records-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .records-table th {
            background-color: #3498db;
            color: #ecf0f1;
        }

        .records-table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .dashboard-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
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

        .notifications-container {
            margin-top: 20px;
        }

        .notification {
            background-color: #3498db;
            color: #ecf0f1;
            font-size: medium;
            font-family: ui-monospace;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        footer {
            background-color: #2c3e50;
            padding: 10px;
            color: #ecf0f1;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to Your Dashboard, <?php echo getFarmerName($conn, $_SESSION['user_id']); ?>!</h1>
    </header>

    <nav>
        <a href="farmerdb.php">Refresh</a>
        <a class="active" href="farmerdb.php">Dashboard</a>
        <a href="farmerreports.php">Reports</a>
        <a href="profile.php">Profile</a>
        <a href="feedback.php">Feedback</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php">Logout</a>
    </nav>

    <section>
        <div class="dashboard-container">
            <div class="dashboard-item">
                <h2>Your Records</h2>

      <?php
// Fetch and display the farmer's records
$records = getFarmerRecords($conn, $_SESSION['user_id']);
if ($records) {
    // Check if a date filter is applied
    $filterDate = isset($_POST['filterDate']) ? $_POST['filterDate'] : '';

    echo '<form method="post" id="filterForm">';
    echo '<label for="filterDate">Filter by Date:</label>';
    echo '<input type="date" name="filterDate" id="filterDate" value="' . $filterDate . '" onchange="document.getElementById(\'filterForm\').submit();">';
    echo '</form>';

    if (!empty($filterDate)) {
        // If a date is provided, filter the records
        $filteredRecords = array_filter($records, function ($record) use ($filterDate) {
            // Use date_format to format the dates consistently
            return date_format(date_create($record['date_time']), 'Y-m-d') == $filterDate;
        });

        // Calculate total income for filtered records
        $totalIncomeFiltered = array_sum(array_map(function ($record) {
            return $record['quantity'] * $record['rate'];
        }, $filteredRecords));
    } else {
        // If no date filter, consider all records
        $filteredRecords = $records;
        $totalIncomeFiltered = null;
    }

    // Calculate total income for all records
    $totalIncomeOverall = array_sum(array_map(function ($record) {
        return $record['quantity'] * $record['rate'];
    }, $records));

    if (!empty($filteredRecords)) {
        // Display records if available
        echo '<table class="records-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Record ID</th>';
        echo '<th>Quantity</th>';
        echo '<th>Rate</th>';
        echo '<th>Income</th>';
        echo '<th>Date Recorded</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($filteredRecords as $record) {
            echo '<tr>';
            echo '<td>' . $record['record_Id'] . '</td>';
            echo '<td>' . $record['quantity'] . '</td>';
            echo '<td>' . $record['rate'] . '</td>';
            // Calculate and display income for each record
            $income = $record['quantity'] * $record['rate'];
            echo '<td>' . $income . ' KSH</td>';
            echo '<td>' . $record['date_time'] . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';

        // Display the total income in a separate row below the "Income" column
        echo '<tfoot>';
        echo '<tr>';
        echo '<td colspan="3"><strong>Total Income:</strong></td>';
        echo '<td><strong>' . ($totalIncomeFiltered !== null ? $totalIncomeFiltered : $totalIncomeOverall) . ' KSH</strong></td>';
        echo '<td></td>'; // Placeholder for Date Recorded column
        echo '</tr>';
        echo '</tfoot>';

        echo '</table>';
    } else {
        // Display a message if no records are available for the selected date
        echo '<p>No records available for the selected date.</p>';
    }
} else {
    echo '<p>No records available.</p>';
}
?>



            </div>

            <div class="dashboard-item">
                <h2>Notifications</h2>
                <div class="notifications-container">
                    
                <?php
                $notifications = getFarmerNotifications($conn, $_SESSION['user_id']);
                if ($notifications) {
                    foreach ($notifications as $notification) {
                        echo '<div class="notification">';
                        echo '<p>' . $notification['message'] . '</p>';
                        
                        // Add a button to mark the message as read
                        echo '<form method="post" action="mark_as_read.php">';
                        echo '<input type="hidden" name="notification_id" value="' . $notification['notification_id'] . '">';
                        echo '<input class="msg-btn" type="submit" value="Mark as Read"></input>';
                        echo '</form>';
                        
                        echo '</div>';
                    }
                } else {
                    echo '<p>No new notifications.</p>';
                }
                ?>
                    
                </div>
            </div>
        </div>
    </section>

    <footer>
        &copy; 2025 Farmer Dashboard
    </footer>
</body>
</html>
