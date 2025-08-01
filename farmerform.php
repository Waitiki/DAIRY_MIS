<?php
include 'functions.php';
checkAdminAuthentication();

$host = "localhost";
$user = "root";
$dbPassword = "";
$dbName = "dairy";

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $location = $_POST['location'];
    $breedOfCow = $_POST['breedOfCow'];
    $userPassword = $_POST['password'];

    if (empty($userPassword)) {
        die("Error: Password cannot be empty.");
    }

    $conn = new mysqli($host, $user, $dbPassword, $dbName);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "INSERT INTO farmers (name, email, telephone, location, breedOfCow, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error: " . $conn->error);
    }

    $stmt->bind_param("ssssss", $name, $email, $telephone, $location, $breedOfCow, $userPassword);

    if ($stmt->execute()) {
        echo "<div style='text-align: center; color: #4CAF50; margin: 20px;'>Farmer added successfully.</div>";
    } else {
        echo "<div style='text-align: center; color: #FF0000; margin: 20px;'>Error: " . htmlspecialchars($stmt->error) . "</div>";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Farmer</title>
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
            max-width: 600px;
            margin: 0 auto;
        }

        .form-inputs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        label {
            margin-top: 10px;
            font-weight: 600;
            color: var(--dark-gray);
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid var(--medium-gray);
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        select {
            appearance: none;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path fill="%234CAF50" d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center;
            background-size: 12px;
        }

        input[type="submit"] {
            background: var(--primary-color);
            color: var(--white);
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background: var(--primary-dark);
        }

        /* Sidebar styles */
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

            .form-inputs {
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
            <h2><i class="fas fa-user-plus"></i> Add a Farmer</h2>
            <div class="card">
                <form method="post" action="farmerform.php">
                    <div class="form-inputs">
                        <div>
                            <label for="name">Name:</label>
                            <input type="text" name="name" required>
                        </div>
                        <div>
                            <label for="email">Email:</label>
                            <input type="email" name="email" required>
                        </div>
                        <div>
                            <label for="telephone">Telephone:</label>
                            <input type="tel" name="telephone" required>
                        </div>
                        <div>
                            <label for="location">Location:</label>
                            <input type="text" name="location" required>
                        </div>
                        <div>
                            <label for="breedOfCow">Breed of Cow:</label>
                            <select name="breedOfCow" required>
                                <option disabled selected>Select breed</option>
                                <option>Jersey</option>
                                <option>Ayrshire</option>
                                <option>Fresian</option>
                                <option>Guernsey</option>
                                <option>Sahiwal</option>
                                <option>Mixed</option>
                            </select>
                        </div>
                        <div>
                            <label for="password">Password:</label>
                            <input type="password" name="password" required>
                        </div>
                    </div>
                    <input type="submit" name="submit" value="Add Farmer">
                </form>
            </div>
        </div>
    </div>
</body>
</html>