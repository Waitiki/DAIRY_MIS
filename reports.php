<?php
include 'functions.php';
checkAdminAuthentication();

$host = "localhost";
$user = "root";
$password = "";
$db = "dairy";

// Create a database connection
$conn = new mysqli($host, $user, $password, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data for the charts
// Date vs Quantity (Line Graph)
$dateQuantityQuery = "SELECT DATE(date_time) as recordDate, SUM(quantity) as totalQuantity FROM records GROUP BY recordDate";
$dateQuantityResult = $conn->query($dateQuantityQuery);
$dateQuantityData = $dateQuantityResult->fetch_all(MYSQLI_ASSOC);


// Location vs Quantity (Pie Chart)
$locationQuantityQuery = "SELECT location, SUM(quantity) as totalQuantity, COUNT(*) as numFarmers FROM farmers INNER JOIN records ON farmers.id = records.farmer_Id GROUP BY location";
$locationQuantityResult = $conn->query($locationQuantityQuery);
$locationQuantityData = $locationQuantityResult->fetch_all(MYSQLI_ASSOC);

// Farmers Location (Pie Chart)
$locationQuery = "SELECT location, COUNT(*) as numFarmers FROM farmers GROUP BY location";
$locationResult = $conn->query($locationQuery);
$locationData = $locationResult->fetch_all(MYSQLI_ASSOC);


// Quantity vs Cow Breed (Bar Graph)
$quantityCowBreedQuery = "SELECT breedOfCow, SUM(quantity) as totalQuantity FROM records GROUP BY breedOfCow";
$quantityCowBreedResult = $conn->query($quantityCowBreedQuery);

// Check for errors
if (!$quantityCowBreedResult) {
    die("Query failed: " . $conn->error);
}

$quantityCowBreedData = $quantityCowBreedResult->fetch_all(MYSQLI_ASSOC);

// Income vs Location (Bar Graph)
$incomeLocationQuery = "SELECT location, SUM(quantity * 50) as totalIncome FROM farmers INNER JOIN records ON farmers.id = records.farmer_Id GROUP BY location";
$incomeLocationResult = $conn->query($incomeLocationQuery);

// Check for errors
if (!$incomeLocationResult) {
    die("Query failed: " . $conn->error);
}

$incomeLocationData = $incomeLocationResult->fetch_all(MYSQLI_ASSOC);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dairy Farm Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

   <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        display: flex;
    }

    #sidebar {
        width: 200px;
        height: 100vh;
        background-color: #4CAF50;
        color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        transition: 0.5s;
        overflow-y: auto;
        position: fixed;
        left: 0;
    }

    #sidebar header {
        font-size: 22px;
        text-align: center;
        padding: 20px 0;
    }

    #sidebar ul {
        padding: 0;
        margin: 0;
        list-style: none;
    }

    #sidebar ul li {
        padding: 15px;
        text-align: center;
        transition: background-color 0.3s;
    }

    #sidebar ul li a {
        text-decoration: none;
        color: #fff;
        font-size: 18px;
        display: block;
    }

    #sidebar ul li:hover {
        background-color: #333;
    }

    .dashboard-container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        margin: 20px;
        flex-grow: 1;
    }

    .chart-container {
        width: 45%;
        margin: 10px;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        border-radius: 5px;
    }

    .explanation {
        margin-top: 20px;
        text-align: center;
    }

    @media screen and (max-width: 768px) {
        #sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }

        .dashboard-container {
            flex-direction: column;
            align-items: center;
        }

        .chart-container {
            width: 100%;
        }
    }

    @media screen and (min-width: 768px) {
        /* Apply styling for screens larger than 768px (tablets and desktops) */
        .chart-container {
            width: 45%;
        }
    }
</style>

    </style>
</head>

<body>
    <div id="sidebar">
        <header>ADMIN DASHBOARD</header>
        <ul>
            <li><a href="add_admin.php"><i class="fas fa-stream"></i>Admins</a></li>
            <li><a href="add_farmer.php"><i class="fas fa-stream"></i>Farmers</a></li>
            <li><a href="records.php"><i class="fas fa-sliders-h"></i>Records</a></li>
            <li><a href="sendNotifications.php"><i class="fas fa-sliders-h"></i>Notifications</a></li>
            <li><a href="viewfeedbacks.php"><i class="fas fa-sliders-h"></i>feedback</a></li>
            <li><a href="reports.php"><i class="fas fa-question-circle"></i>Reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
   

    <div class="dashboard-container">
        <!-- Chart for Date vs Quantity (Line Graph) -->
        <div class="chart-container">
            <h3>Date vs Quantity</h3>
            <canvas id="dateQuantityChart"></canvas>
            <div class="explanation">
                <p>This line graph illustrates the quantity of milk recorded on each date.</p>
            </div>
        </div>


        <!-- Chart for Location vs Quantity (Pie Chart) -->
        <div class="chart-container">
            <h3>Location vs Quantity</h3>
            <canvas id="locationQuantityChart"></canvas>
            <div class="explanation">
                <p>This pie chart visualizes the quantity of milk produced by each location along with the number of farmers in each location.</p>
            </div>
        </div>

         <!-- Chart for Farmers Location (Pie Chart) -->
         <div class="chart-container">
            <h3>Farmers Location</h3>
            <canvas id="farmersLocationChart"></canvas>
            <div class="explanation">
                <p>This pie chart displays the distribution of farmers based on their location.</p>
            </div>
        </div>

         <!-- Chart for Quantity vs Cow Breed (Bar Graph) -->
         <div class="chart-container">
            <h3>Quantity vs breedOfCow</h3>
            <canvas id="quantityCowBreedChart"></canvas>
            <div class="explanation">
                <p>This bar graph shows the quantity of milk produced by each cow breed.</p>
            </div>
        </div>

        
         <!-- Chart for Income vs Location (Bar Graph) -->
         <div class="chart-container">
            <h3>Income vs Location</h3>
            <canvas id="incomeLocationChart"></canvas>
            <div class="explanation">
                <p>This bar graph illustrates the income generated in each location.</p>
            </div>
        </div>



</div>
    </div>

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

        
            // Chart for Location vs Quantity (Pie Chart)
            new Chart(document.getElementById('locationQuantityChart'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_column($locationQuantityData, 'location')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($locationQuantityData, 'totalQuantity')); ?>,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                    }]
                }
            });

            // Chart for Farmers Location (Pie Chart)
            new Chart(document.getElementById('farmersLocationChart'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_column($locationData, 'location')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($locationData, 'numFarmers')); ?>,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                    }]
                }
            });
        });


        // Chart for Quantity vs Cow Breed (Bar Graph)
        new Chart(document.getElementById('quantityCowBreedChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($quantityCowBreedData, 'breedOfCow')); ?>,
                    datasets: [{
                        label: 'Total Quantity',
                        data: <?php echo json_encode(array_column($quantityCowBreedData, 'totalQuantity')); ?>,
                        backgroundColor: '#FF6384'
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

            // Chart for Income vs Location (Bar Graph)
            new Chart(document.getElementById('incomeLocationChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($incomeLocationData, 'location')); ?>,
                    datasets: [{
                        label: 'Total Income',
                        data: <?php echo json_encode(array_column($incomeLocationData, 'totalIncome')); ?>,
                        backgroundColor: '#FFCE56'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Total Income'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Location'
                            }
                        }
                    }
                }
            });
    </script>
</body>

</html>
