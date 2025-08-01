<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function isAdmin($userID, $conn) {
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
    if (!$stmt) {
        error_log("Error preparing isAdmin query: " . $conn->error);
        return false;
    }
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $isAdmin = $result && $result->num_rows > 0;
    $stmt->close();
    return $isAdmin;
}

$host = "localhost";
$user = "root";
$password = "";
$db = "dairy";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Unable to connect to the database. Please try again later.</div>";
    exit();
}

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header("Location: login.php");
    exit();
}

$adminID = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
if (!$stmt) {
    error_log("Error preparing admin query: " . $conn->error);
    echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Error fetching admin data.</div>";
    exit();
}
$stmt->bind_param("i", $adminID);
$stmt->execute();
$adminResult = $stmt->get_result();
$adminName = ($adminResult && $adminResult->num_rows > 0) ? $adminResult->fetch_assoc()['username'] : "Admin";
$stmt->close();

// Initialize variables
$totalFarmers = 0;
$totalRecords = 0;
$totalNotifications = 0;
$totalFeedback = 0;
$totalRevenue = 0;
$totalQuantity = 0;
$averageRate = 0;
$recentRecords = [];
$recentFeedback = [];
$monthlyStats = [];

// Fetch key metrics with error handling
try {
    // Total Farmers
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM farmers");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $totalFarmers = $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
        $stmt->close();
    }

    // Total Records
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM records");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $totalRecords = $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
        $stmt->close();
    }

    // Total Notifications
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $totalNotifications = $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
        $stmt->close();
    }

    // Total Feedback (using correct table name)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM feedback_table");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $totalFeedback = $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
        $stmt->close();
    }

    // Financial Statistics
    $stmt = $conn->prepare("SELECT SUM(quantity * rate) as total_revenue, SUM(quantity) as total_quantity, AVG(rate) as avg_rate FROM records");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $stats = $result->fetch_assoc();
            $totalRevenue = $stats['total_revenue'] ?: 0;
            $totalQuantity = $stats['total_quantity'] ?: 0;
            $averageRate = $stats['avg_rate'] ?: 0;
        }
        $stmt->close();
    }

    // Recent Records (Today only)
    $stmt = $conn->prepare("SELECT record_Id, farmer_Id, farmer_name, quantity, rate, date_time FROM records WHERE DATE(date_time) = CURDATE() ORDER BY date_time DESC LIMIT 5");
    if ($stmt) {
        $stmt->execute();
        $recentRecordsResult = $stmt->get_result();
        $recentRecords = $recentRecordsResult ? $recentRecordsResult->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }

    // Recent Feedback with farmer names (Today only)
    $stmt = $conn->prepare("SELECT f.farmer_id, f.feedback_message as message, f.timestamp, fm.name as farmer_name FROM feedback_table f LEFT JOIN farmers fm ON f.farmer_id = fm.id WHERE DATE(f.timestamp) = CURDATE() ORDER BY f.timestamp DESC LIMIT 5");
    if ($stmt) {
        $stmt->execute();
        $recentFeedbackResult = $stmt->get_result();
        $recentFeedback = $recentFeedbackResult ? $recentFeedbackResult->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }

    // Monthly Statistics for current year
    $currentYear = date('Y');
    $stmt = $conn->prepare("SELECT MONTH(date_time) as month, COUNT(*) as record_count, SUM(quantity) as total_quantity, SUM(quantity * rate) as total_revenue, AVG(rate) as avg_rate FROM records WHERE YEAR(date_time) = ? GROUP BY MONTH(date_time) ORDER BY month");
    if ($stmt) {
        $stmt->bind_param("i", $currentYear);
        $stmt->execute();
        $monthlyResult = $stmt->get_result();
        $monthlyStats = $monthlyResult ? $monthlyResult->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }

} catch (Exception $e) {
    error_log("Error fetching dashboard data: " . $e->getMessage());
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dairy Management System</title>
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
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
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

        .welcome-message {
            text-align: center;
            font-size: 1.5rem;
            color: var(--dark-gray);
            margin-bottom: 30px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 10px 0;
            font-size: 1.2rem;
            color: var(--dark-gray);
        }

        .card .metric {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .card .metric-currency {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--success-color);
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            background-color: var(--white);
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--medium-gray);
        }

        th {
            background-color: var(--primary-color);
            color: var(--white);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: rgba(76, 175, 80, 0.05);
        }

        .notification-item, .feedback-item {
            background-color: var(--white);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
        }

        .notification-item:hover, .feedback-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .notification-header, .feedback-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--medium-gray);
        }

        .notification-farmer, .feedback-farmer {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .notification-time, .feedback-time {
            font-size: 0.85rem;
            color: #666;
        }

        .notification-message, .feedback-message {
            margin: 10px 0;
            line-height: 1.6;
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

        .error-message i {
            margin-right: 10px;
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

        .monthly-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .month-stat {
            background-color: var(--white);
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--info-color);
        }

        .month-name {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 5px;
        }

        .month-value {
            font-size: 1.2rem;
            color: var(--info-color);
            font-weight: 600;
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

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .monthly-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            th, td {
                padding: 8px 10px;
                font-size: 0.85rem;
            }

            .notification-header, .feedback-header {
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
            <div class="welcome-message">
                Welcome, <?php echo htmlspecialchars($adminName); ?>!
            </div>
            
            <h2><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h2>
            
            <div class="dashboard-grid">
                <div class="card">
                    <i class="fas fa-users"></i>
                    <h3>Total Farmers</h3>
                    <div class="metric"><?php echo htmlspecialchars($totalFarmers); ?></div>
                </div>
                <div class="card">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Total Records</h3>
                    <div class="metric"><?php echo htmlspecialchars($totalRecords); ?></div>
                </div>
                <div class="card">
                    <i class="fas fa-bell"></i>
                    <h3>Notifications Sent</h3>
                    <div class="metric"><?php echo htmlspecialchars($totalNotifications); ?></div>
                </div>
                <div class="card">
                    <i class="fas fa-comment-alt"></i>
                    <h3>Feedback Received</h3>
                    <div class="metric"><?php echo htmlspecialchars($totalFeedback); ?></div>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-chart-line"></i> Financial Overview</h3>
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">Ksh <?php echo number_format($totalRevenue, 2); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Total Quantity (kg)</div>
                        <div class="stat-value"><?php echo number_format($totalQuantity, 2); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Average Rate (Ksh/kg)</div>
                        <div class="stat-value"><?php echo number_format($averageRate, 2); ?></div>
                    </div>
                </div>
            </div>

            <?php if (!empty($monthlyStats)): ?>
            <div class="card"></div>
                <h3><i class="fas fa-calendar-alt"></i> Monthly Statistics (<?php echo date('Y'); ?>)</h3>
                <div class="monthly-stats">
                    <?php 
                    $monthNames = [
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                    foreach ($monthlyStats as $stat): 
                    ?>
                    <div class="month-stat">
                        <div class="month-name"><?php echo $monthNames[$stat['month']]; ?></div>
                        <div class="month-value"><?php echo $stat['record_count']; ?> records</div>
                        <div class="month-value"><?php echo number_format($stat['total_quantity'], 2); ?> kg</div>
                        <div class="month-value">Ksh <?php echo number_format($stat['total_revenue'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>                     
            
            <h2><i class="fas fa-comment-alt"></i> Recent Feedback</h2>
            <div class="card">
                <?php if (!empty($recentFeedback)): ?>
                    <div class="feedback-list">
                        <?php foreach ($recentFeedback as $feedback): ?>
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <span class="feedback-farmer">
                                        <i class="fas fa-user"></i> 
                                        <?php echo htmlspecialchars($feedback['farmer_name'] ?: 'Farmer ID: ' . $feedback['farmer_id']); ?>
                                    </span>
                                    <span class="feedback-time">
                                        <i class="far fa-clock"></i> <?php echo htmlspecialchars($feedback['timestamp']); ?>
                                    </span>
                                </div>
                                <div class="feedback-message">
                                    <?php echo nl2br(htmlspecialchars($feedback['message'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="far fa-comment-alt"></i>
                        <h3>No feedback today</h3>
                        <p>Feedback from farmers will appear here</p>
                    </div>
                <?php endif; ?>
            </div>

               <h2><i class="fas fa-clipboard-list"></i> Recent Records</h2>
            <div class="card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Record ID</th>
                                <th>Farmer ID</th>
                                <th>Farmer Name</th>
                                <th>Quantity (kg)</th>
                                <th>Rate (Ksh)</th>
                                <th>Total (Ksh)</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentRecords)): ?>
                                <?php foreach ($recentRecords as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['record_Id']); ?></td>
                                        <td><?php echo htmlspecialchars($record['farmer_Id']); ?></td>
                                        <td><?php echo htmlspecialchars($record['farmer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($record['rate']); ?></td>
                                        <td><?php echo number_format($record['quantity'] * $record['rate'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($record['date_time']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                    <h3>No records today</h3>
                                    <p>Add records to see them here</p>
                                </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>