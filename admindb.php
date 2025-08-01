<?php
session_start();

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function isAdmin($userID)
{
    $host = "localhost";
    $user = "root";
    $password = "";
    $db = "dairy";

    $conn = new mysqli($host, $user, $password, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $userID = $_SESSION['user_id'];

    $adminCheckQuery = "SELECT * FROM admins WHERE id = $userID";
    $adminCheckResult = $conn->query($adminCheckQuery);

    $conn->close();

    return ($adminCheckResult && $adminCheckResult->num_rows > 0);
}

$adminID = $_SESSION['user_id'];

$host = "localhost";
$user = "root";
$password = "";
$db = "dairy";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$adminQuery = "SELECT * FROM admins WHERE id = $adminID";
$adminResult = $conn->query($adminQuery);

if ($adminResult && $adminResult->num_rows > 0) {
    $adminData = $adminResult->fetch_assoc();
    // Check if the "username" key exists in $adminData
    $adminName = isset($adminData['username']) ? $adminData['username'] : "Admin";
} else {
    $adminName = "Admin";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN DASHBOARD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css"
        integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>

    <style>
        /* Reset some default styles for the body and margin of the page */
        body,
        html {
            margin: 0;
            padding: 0;
        }

        /* Apply a background image for the glass effect */
        body {
            background-image: url('your-background-image.jpg');
            background-size: cover;
            background-blur: 5px;
            font-family: 'Arial', sans-serif; /* Added a default font-family */
        }

        /* Style for the sidebar */
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
        }

        /* Additional styles for welcome message */
        .welcome-message {
            text-align: center;
            margin-top: 20px;
            font-size: 24px;
            color: #333;
			margin-left:90px;
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
        <div class="welcome-message">
            Welcome, <?php echo $adminName; ?>! <!-- Display the admin's name -->
        </div>

    </div>

    <!-- Rest of your HTML content -->
</body>

</html>
