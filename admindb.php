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
            position: fixed;
            width: 250px;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            transition: 0.5s;
        }

        /* Style for the header in the sidebar */
        .sidebar header {
            font-size: 22px;
            text-align: center;
            padding: 20px 0;
            color: #333;
        }

        /* Style for the navigation links in the sidebar */
        .sidebar ul {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .sidebar ul li {
            padding: 15px;
            text-align: center;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
            font-size: 18px;
            display: block;
            transition: 0.3s;
        }

        /* Change link color on hover */
        .sidebar ul li a:hover {
            color: #555;
        }

        /* Add a media query for responsiveness */
        @media screen and (max-width: 768px) {
            .sidebar {
                width: 35%;
            }

            .sidebar header,
            .sidebar ul li {
                text-align: left; /* Adjust text alignment for smaller screens */
            }

            .sidebar ul li a {
                padding: 15px;
                text-align: left;
            }
        }

        /* Style for the toggle button */
        #check {
            display: none;
        }

        /* Style for the hamburger and close icons */
        #btn,
        #cancel {
            font-size: 30px;
            cursor: pointer;
        }

        /* Position the icons inside the label */
        label i {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        /* Display the close icon initially as hidden */
        #cancel {
            display: none;
            color: red;
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

    <div class="welcome-message">
        Welcome, <?php echo $adminName; ?>! <!-- Display the admin's name -->
    </div>

    <!-- Rest of your HTML content -->

</body>

</html>
