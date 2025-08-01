<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers Management</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--medium-gray);
        }

        table th {
            background-color: var(--primary-color);
            color: var(--white);
            font-weight: 600;
        }

        table tr:hover {
            background-color: rgba(76, 175, 80, 0.05);
        }

        .add-farmer-icon {
            position: fixed;
            right: 40px;
            top: 20px;
            background: var(--primary-color);
            color: var(--white);
            font-size: 24px;
            width: 50px;
            height: 50px;
            line-height: 50px;
            border-radius: 50%;
            text-align: center;
            box-shadow: var(--shadow);
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
            text-decoration: none;
        }

        .add-farmer-icon:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
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

            .add-farmer-icon {
                right: 20px;
                top: 80px;
            }

            table {
                font-size: 0.9rem;
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
            <h2><i class="fas fa-users"></i> Farmers Management</h2>
            <a href="farmerform.php" class="add-farmer-icon"><i class="fas fa-plus"></i></a>
            <div class="card">
                <table>
                    <tr>
                        <th>Farmer Id</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Telephone</th>
                        <th>Location</th>
                        <th>Cow Breed</th>
                    </tr>
                    <?php
                    $host = "localhost";
                    $user = "root";
                    $password = "";
                    $db = "dairy";

                    $db = new mysqli($host, $user, $password, $db);

                    if ($db->connect_error) {
                        die("Connection failed: " . $db->connect_error);
                    }

                    $query = "SELECT id, name, email, telephone, location, breedOfCow FROM farmers";
                    $result = $db->query($query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['telephone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['breedOfCow']) . "</td>";
                            echo "</tr>";
                        }
                    }

                    $db->close();
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>