<?php
session_start(); // Start the session to access user data

$host = "localhost";
$user = "root";
$password = "";
$db = "dairy";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to the login page if not logged in
    exit();
}

// Get the logged-in user's ID from the session
$userID = $_SESSION['user_id'];

// Create a database connection
$conn = new mysqli($host, $user, $password, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data for the charts
// Date vs Quantity (Line Graph)
$dateQuantityQuery = "SELECT DATE(date_time) as recordDate, SUM(quantity) as totalQuantity FROM records WHERE farmer_Id = ? GROUP BY recordDate ORDER BY recordDate";
$dateQuantityStmt = $conn->prepare($dateQuantityQuery);
$dateQuantityStmt->bind_param("i", $userID);
$dateQuantityStmt->execute();
$dateQuantityResult = $dateQuantityStmt->get_result();
$dateQuantityData = $dateQuantityResult->fetch_all(MYSQLI_ASSOC);
$dateQuantityStmt->close();

// Date vs Income (Line Graph)
$dateIncomeQuery = "SELECT DATE(date_time) as recordDate, SUM(quantity * 50) as totalIncome FROM records WHERE farmer_Id = ? GROUP BY recordDate ORDER BY recordDate";
$dateIncomeStmt = $conn->prepare($dateIncomeQuery);
$dateIncomeStmt->bind_param("i", $userID);
$dateIncomeStmt->execute();
$dateIncomeResult = $dateIncomeStmt->get_result();
$dateIncomeData = $dateIncomeResult->fetch_all(MYSQLI_ASSOC);
$dateIncomeStmt->close();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <style>
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
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .chart-container {
            width: 45%;
            margin: 10px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
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
        <h1>Reports</h1>
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
        <!-- Chart for Date vs Quantity (Line Graph) -->
        <div class="chart-container">
            <h3>Date vs Quantity</h3>
            <canvas id="dateQuantityChart"></canvas>
            <div class="explanation">
                <p>This line graph illustrates the quantity of milk recorded on each date.</p>
            </div>
        </div>

        <!-- Chart for Date vs Income (Line Graph) -->
        <div class="chart-container">
            <h3>Date vs Income</h3>
            <canvas id="dateIncomeChart"></canvas>
            <div class="explanation">
                <p>This line graph shows the income generated on each date.</p>
            </div>
        </div>
    </section>

    <footer>
        &copy; 2023 Farmer Dashboard
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Chart for Date vs Quantity (Line Graph)
            new Chart(document.getElementById('dateQuantityChart'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($dateQuantityData, 'recordDate')); ?>,
                    datasets: [{
                        label: 'Total Quantity',
                        data: <?php echo json_encode(array_column($dateQuantityData, 'totalQuantity')); ?>,
                        fill: false,
                        borderColor: '#36A2EB'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Chart for Date vs Income (Line Graph)
            new Chart(document.getElementById('dateIncomeChart'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($dateIncomeData, 'recordDate')); ?>,
                    datasets: [{
                        label: 'Total Income',
                        data: <?php echo json_encode(array_column($dateIncomeData, 'totalIncome')); ?>,
                        fill: false,
                        borderColor: '#FFCE56'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
