<!DOCTYPE html>
<html>
<head>
    <title>Add Admin</title>
    <style>
        h2{
            text-align: center;
            color: #4CAF50;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.7);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            margin-left: 200px;
        }

        table th,
        table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }

        table th {
            background: #4CAF50;
            color: white;
        }

        .add-admin-icon {
            position: absolute;
            right: 100px;
            top: 10px;
            background: #4CAF50;
            color: white;
            font-size: 24px;
            padding: 10px;
            border-radius: 50%;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }

        .add-admin-icon:hover {
            background: #4CAF50;
            transform: scale(1.1);
        }

        .add-admin-form {
            display: none;
            margin-top: 20px;
        }

        /* Responsive styles */
        @media screen and (max-width: 768px) {
            table {
                width: 100%;
                margin-left: 0;
            }

            .add-admin-icon {
                right: 50%;
                transform: translateX(50%);
            }
        }

       
		#adminForm {
    max-width: 400px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    border-radius: 5px;
}


        #adminForm {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }

        #adminForm label {
            display: block;
            margin-bottom: 8px;
        }

        #adminForm input[type="text"],
        #adminForm input[type="tel"],
        #adminForm input[type="email"],
        #adminForm input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        #adminForm input[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        #adminForm input[type="submit"]:hover {
            background-color: #45a049;
        }

        #adminForm .error-message {
            color: #ff0000;
            margin-bottom: 10px;
        }
    </style>
    <link rel="stylesheet" href="style.css">
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

    <a href="javascript:void(0);" class="add-admin-icon" onclick="toggleForm()">+</a>

    <div class="add-admin-form" id="adminForm">
        <h2>Add Admin</h2>
        <form action="add_admin.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <input type="submit" value="Add Admin">
        </form>
    </div>

    <h2>Admins Management</h2>

    <div class="table-container">
        <table>
            <tr>
                <th>Admin Id</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone Number</th>
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

                // Handle form submission
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $username = $_POST["username"];
                    $email = $_POST["email"];
                    $phone = $_POST["phone"];
					$password = $_POST["password"];
					

                    $insertQuery = "INSERT INTO admins (username, email, phone_number , password) VALUES ('$username', '$email', '$phone', '$password')";
                    $db->query($insertQuery);
                }

            $query = "SELECT id,username, email, phone_number FROM admins";
$result = $db->query($query);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['phone_number'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No admins found</td></tr>";
    }
} else {
    echo "Error: " . $query . "<br>" . $db->error;
}

                $db->close();
            ?>
        </table>
    </div>

    <script>
        function toggleForm() {
            var addAdminForm = document.querySelector('.add-admin-form');
            addAdminForm.style.display = addAdminForm.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>
