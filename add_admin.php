<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admins Management</title>
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

        .add-admin-icon {
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

        .add-admin-icon:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }

        .add-admin-form {
            display: none;
            max-width: 600px;
            margin: 20px auto;
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
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid var(--medium-gray);
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
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

        .error-message {
            color: #ff0000;
            margin-bottom: 10px;
            text-align: center;
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

        .sidebar ul li.active {
            background-color: var(--primary-color);
            border-left: 4px solid var(--white);
        }

        .sidebar ul li.active a {
            color: var(--white);
            font-weight: 600;
        }

        .sidebar ul li.active:hover {
            background-color: var(--primary-dark);
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

            .add-admin-icon {
                right: 20px;
                top: 80px;
            }

            table {
                font-size: 0.9rem;
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
        <div class="sidebar-header">ADMIN PANEL</div>
        <ul>
            <li><a href="admindb.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="active"><a href="add_admin.php"><i class="fas fa-user-shield"></i> Admins</a></li>
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
            <h2><i class="fas fa-user-shield"></i> Admins Management</h2>
            <a href="javascript:void(0);" class="add-admin-icon" onclick="toggleForm()"><i class="fas fa-plus"></i></a>
            <div class="add-admin-form" id="adminForm">
                <div class="card">
                    <h2>Add Admin</h2>
                    <?php
                    $host = "localhost";
                    $user = "root";
                    $password = "";
                    $db = "dairy";

                    $db = new mysqli($host, $user, $password, $db);

                    if ($db->connect_error) {
                        die("Connection failed: " . $db->connect_error);
                    }

                    $error_message = '';
                    $success_message = '';

                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        $username = $_POST["username"];
                        $email = $_POST["email"];
                        $phone = $_POST["phone"];
                        $password = $_POST["password"];

                        if (empty($username) || empty($email) || empty($phone) || empty($password)) {
                            $error_message = "All fields are required.";
                        } else {
                            $stmt = $db->prepare("INSERT INTO admins (username, email, phone_number, password) VALUES (?, ?, ?, ?)");
                            if ($stmt) {
                                $stmt->bind_param("ssss", $username, $email, $phone, $password);
                                if ($stmt->execute()) {
                                    $success_message = "Admin added successfully.";
                                } else {
                                    $error_message = "Error: " . $stmt->error;
                                }
                                $stmt->close();
                            } else {
                                $error_message = "Error: " . $db->error;
                            }
                        }
                    }

                    if ($error_message) {
                        echo "<div class='error-message'>" . htmlspecialchars($error_message) . "</div>";
                    }
                    if ($success_message) {
                        echo "<div style='color: #4CAF50; text-align: center; margin-bottom: 10px;'>" . htmlspecialchars($success_message) . "</div>";
                    }
                    ?>
                    <form action="add_admin.php" method="post">
                        <div class="form-inputs">
                            <div>
                                <label for="username">Username:</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div>
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div>
                                <label for="phone">Phone Number:</label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                            <div>
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                        </div>
                        <input type="submit" value="Add Admin">
                    </form>
                </div>
            </div>
            <div class="card">
                <table>
                    <tr>
                        <th>Admin Id</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                    </tr>
                    <?php
                    $query = "SELECT id, username, email, phone_number FROM admins";
                    $result = $db->query($query);

                    if ($result) {
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No admins found</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>Error: " . htmlspecialchars($db->error) . "</td></tr>";
                    }

                    $db->close();
                    ?>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleForm() {
            var addAdminForm = document.querySelector('.add-admin-form');
            addAdminForm.style.display = addAdminForm.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>