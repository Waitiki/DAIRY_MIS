<?php
include 'functions.php';
checkAdminAuthentication();

$host = "localhost";
$user = "root";
$dbPassword = ""; // Changed variable name to avoid conflict
$dbName = "dairy";

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $location = $_POST['location'];
	$breedOfCow = $_POST['breedOfCow'];
    $userPassword = $_POST['password']; // Changed variable name to avoid conflict

    // Check if the password is not empty
    if (empty($userPassword)) {
        die("Error: Password cannot be empty.");
    }

    // Create a database connection
    $conn = new mysqli($host, $user, $dbPassword, $dbName);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the SQL query to insert data
    $query = "INSERT INTO farmers (name, email, telephone, location, breedOfCow, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("ssssss", $name, $email, $telephone, $location, $breedOfCow, $userPassword);

    if ($stmt->execute()) {
        echo "Farmer added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Farmer</title>
    <style>
        body {
            background: url('glass_texture.jpg');
            /* Replace with your glass texture image */
        }

        form {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
            padding: 20px;
            width: 600px;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            
        }
        .form-inputs{
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: .8rem;
        }

        label {
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
		select{
            /* width: 100%; */
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background: #4CAF50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            background: #45a049;
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

    <div class="container">
        <center>
            <h2>Add a Farmer</h2>
        </center>
        <form method="post" action="farmerform.php">
            <div class="form-inputs">
                <label for="name">Name:</label>
                <label for="email">Email:</label>

                <input type="text" name="name" required>
                <input type="email" name="email" required>

                <label for="telephone">Telephone:</label>
                <label for="location">Location:</label>
                
                <input type="tel" name="telephone" required>
                <input type="text" name="location" required>
                
                <label for="quantity">Breed of cow:</label>
                <label for="password">Password:</label>
            
                <select name="breedOfCow">
                <option disabled selected>Select breed</option>
                <option>Jersey</option>
                <option>Ayrshire</option>
                <option>Fresian</option>
                <option>Guernsey</option>
                <option>Sahiwal</option>
                <option>Mixed</option>
                </select>
                <input type="password" name="password" required>
            
            </div>

            <div class="form-btn">
                <input type="submit" name="submit" value="Add Farmer">
            </div>
            
        </form>
    </div>

</body>

</html>
