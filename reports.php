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
if (!$quantityCowBreedResult) {
    die("Query failed: " . $conn->error);
}
$quantityCowBreedData = $quantityCowBreedResult->fetch_all(MYSQLI_ASSOC);

// Income vs Location (Bar Graph)
$incomeLocationQuery = "SELECT location, SUM(quantity * 50) as totalIncome FROM farmers INNER JOIN records ON farmers.id = records.farmer_Id GROUP BY location";
$incomeLocationResult = $conn->query($incomeLocationQuery);
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
    <title>Dairy Farm Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .chart-container {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .chart-container h3 {
            color: var(--primary-color);
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .chart-explanation {
            margin-top: 15px;
            padding: 10px;
            background-color: rgba(76, 175, 80, 0.05);
            border-left: 3px solid var(--primary-color);
            font-size: 0.9rem;
            color: var(--dark-gray);
        }

        /* Sidebar styles */
        .sidebar {
            background-color: #2c3e50;
            color: white;
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
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 10px;
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
            color: white;
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

            .chart-grid {
                grid-template-columns: 1fr;
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
        <div class="sidebar-header">ADMIN DASHBOARD</div>
        <ul>
            <li><a href="add_admin.php"><i class="fas fa-user-shield"></i> Admins</a></li>
            <li><a href="add_farmer.php"><i class="fas fa-users"></i> Farmers</a></li>
            <li><a href="records.php"><i class="fas fa-clipboard-list"></i> Records</a></li>
            <li><a href="sendNotifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
            <li><a href="viewfeedbacks.php"><i class="fas fa-comment-alt"></i> Feedback</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
            <h2><i class="fas fa-chart-bar"></i> Dairy Farm Analytics</h2>
            
            <div class="card">
                <div class="chart-grid">
                    <!-- Date vs Quantity (Line Graph) -->
                    <div class="chart-container">
                        <h3><i class="fas fa-chart-line"></i> Milk Production Over Time</h3>
                        <canvas id="dateQuantityChart"></canvas>
                        <div class="chart-explanation">
                            <p>This line graph shows the daily milk production quantities, helping identify trends and patterns over time.</p>
                        </div>
                    </div>

                    <!-- Location vs Quantity (Pie Chart) -->
                    <div class="chart-container">
                        <h3><i class="fas fa-chart-pie"></i> Milk Production by Location</h3>
                        <canvas id="locationQuantityChart"></canvas>
                        <div class="chart-explanation">
                            <p>This pie chart shows milk production distribution across different locations, with quantity and farmer count.</p>
                        </div>
                    </div>

                    <!-- Farmers Location (Pie Chart) -->
                    <div class="chart-container">
                        <h3><i class="fas fa-map-marker-alt"></i> Farmer Distribution by Location</h3>
                        <canvas id="farmersLocationChart"></canvas>
                        <div class="chart-explanation">
                            <p>Visual representation of farmer distribution across different geographical locations.</p>
                        </div>
                    </div>

                    <!-- Quantity vs Cow Breed (Bar Graph) -->
                    <div class="chart-container">
                        <h3><i class="fas fa-chart-bar"></i> Milk Production by Cow Breed</h3>
                        <canvas id="quantityCowBreedChart"></canvas>
                        <div class="chart-explanation">
                            <p>Comparison of milk production quantities across different cow breeds.</p>
                        </div>
                    </div>

                    <!-- Income vs Location (Bar Graph) -->
                    <div class="chart-container">
                        <h3><i class="fas fa-money-bill-wave"></i> Income by Location</h3>
                        <canvas id="incomeLocationChart"></canvas>
                        <div class="chart-explanation">
                            <p>Total income generated from milk sales, broken down by location.</p>
                        </div>
                    </div>
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
                        label: 'Milk Quantity (kg)',
                        data: <?php echo json_encode(array_column($dateQuantityData, 'totalQuantity')); ?>,
                        fill: false,
                        borderColor: '#4CAF50',
                        backgroundColor: '#4CAF50',
                        tension: 0.1,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + ' kg';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity (kg)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
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
                        backgroundColor: [
                            '#4CAF50',
                            '#36A2EB',
                            '#FFCE56',
                            '#FF6384',
                            '#9966FF',
                            '#4BC0C0'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} kg (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Chart for Farmers Location (Pie Chart)
            new Chart(document.getElementById('farmersLocationChart'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_column($locationData, 'location')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($locationData, 'numFarmers')); ?>,
                        backgroundColor: [
                            '#36A2EB',
                            '#FFCE56',
                            '#4CAF50',
                            '#FF6384',
                            '#9966FF',
                            '#4BC0C0'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} farmers (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Chart for Quantity vs Cow Breed (Bar Graph)
            new Chart(document.getElementById('quantityCowBreedChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($quantityCowBreedData, 'breedOfCow')); ?>,
                    datasets: [{
                        label: 'Milk Quantity (kg)',
                        data: <?php echo json_encode(array_column($quantityCowBreedData, 'totalQuantity')); ?>,
                        backgroundColor: '#4CAF50',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + ' kg';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity (kg)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Cow Breed'
                            }
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
                        label: 'Total Income (Ksh)',
                        data: <?php echo json_encode(array_column($incomeLocationData, 'totalIncome')); ?>,
                        backgroundColor: '#FFCE56',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Ksh ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Income (Ksh)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'Ksh ' + value.toLocaleString();
                                }
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
        });
    </script>
</body>
</html>