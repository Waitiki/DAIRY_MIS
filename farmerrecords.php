<?php
session_start(); // Start the session to access user data
$host = "localhost";
$user = "root";
$dbPassword = ""; // Your database password
$dbName = "dairy";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Get the logged-in user's ID from the session
$userID = $_SESSION['user_id'];

// Create a database connection
$conn = new mysqli($host, $user, $dbPassword, $dbName);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve records for the logged-in user
$query = "SELECT * FROM records WHERE farmer_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error: " . $conn->error);
}

// Bind parameter
$stmt->bind_param("i", $userID);

// Execute the query
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

// Count the number of rows
$numRows = $result->num_rows;

// Calculate total income
$totalIncome = 0;
if ($numRows > 0) {
    $result->data_seek(0); // Reset result set pointer to the beginning
    while ($row = $result->fetch_assoc()) {
        $quantity = $row['quantity'];
        $totalIncome += $quantity * 50; // Calculate total income based on quantity
    }
}

// Close the statement
$stmt->close();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Records</title>
	<style>
  /* Reset some default styles for the body and margin of the page */
body, html {
  margin: 0;
  padding: 0;
  height: 100%;
  font-family: 'Arial', sans-serif;
  background-image: url('your-background-image.jpg'); /* Replace with your actual background image */
  background-size: cover;
  background-blur: 5px; /* Add a blur effect to the background */
}

/* Style for the container */
#container {
  display: flex;
  height: 100vh;
}

/* Style for the sidebar */
#sidebar {
  width: 250px;
  background-color: rgba(255, 255, 255, 0.8);
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
  transition: 0.5s;
  overflow-y: auto;
}

/* Style for the sidebar header */
#sidebar header {
  font-size: 22px;
  text-align: center;
  padding: 20px 0;
  color: #333; /* Adjust the color as needed */
  background-color: #4CAF50; /* Header background color */
  color: #fff; /* Header text color */
}

/* Style for the sidebar lists */
#sidebar ul {
  padding: 0;
  margin: 0;
  list-style: none;
}

#sidebar ul li {
  padding: 15px;
  text-align: center;
  transition: background-color 0.3s; /* Add a smooth transition effect for the background color */
}

#sidebar ul li a {
  text-decoration: none;
  color: #333; /* Adjust the color as needed */
  font-size: 18px;
  display: block;
}

/* Change list item background color on hover */
#sidebar ul li:hover {
  background-color: #ddd; /* Adjust the hover background color as needed */
}

/* Style for the main content */
#main-content {
  flex-grow: 1;
  text-align: center;
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}

h2 {
  color: #fff;
  margin-bottom: 20px;
}

/* Style for the filter form */
form {
  text-align: center;
  margin-bottom: 20px;
}

label {
  color: #fff;
}


/* Style for the submit button */
#submit {
  background-color: #4CAF50;
  color: #fff;
  padding: 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

/* Style for the table */
table {
  width: 100%;
  border-collapse: collapse;
  background-color: rgba(255, 255, 255, 0.8);
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
  border-radius: 10px; /* Adjust the border radius for a glass-like effect */
  margin-bottom: 400px 
   
}

th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

th {
  background-color: #4CAF50;
  color: #fff;
}

tr:nth-child(even) {
  background-color: #f2f2f2;
}

tr:hover {
  background-color: #ddd;
}

@media screen and (max-width: 768px) {
  /* Adjust styles for smaller screens */
  #container {
    flex-direction: column;
  }

  #sidebar {
    width: 100%;
    height: auto;
  }
}


</style>
</head>

<body>
    <div id="container">
        <div id="sidebar">
            <header>FARMER DASHBOARD</header>
            <ul>
                <li><a href="farmerrecords.php"><i class="fas fa-sliders-h"></i>records</a></li>
                <li><a href="farmerreports.php"><i class="fas fa-question-circle"></i>reports</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div id="main-content">
            <h2>Your Records</h2>

         

            <!-- Records table -->
            <table>
                <tr>
                    <th>ID</th>
                    <th>Farmer Name</th>
                    <th>Quantity</th>
                    <th>Date/Time Taken</th>
                    <th>Income</th>
                </tr>
                <!-- Display records fetched from the database -->
                <?php
                if ($numRows > 0) {
                    $result->data_seek(0); // Reset result set pointer to the beginning
                    while ($row = $result->fetch_assoc()) {
                        $farmerId = $row['farmer_Id'];
                        $farmerName = $row['farmer_name'];
                        $quantity = $row['quantity'];
                        $dateTime = $row['date_time'];
                        $income = $quantity * 50; // Calculate income based on quantity

                        echo "<tr>";
                        echo "<td>" . $farmerId . "</td>";
                        echo "<td>" . $farmerName . "</td>";
                        echo "<td>" . $quantity . " kg</td>";
                        echo "<td>" . $dateTime . "</td>";
                        echo "<td>" . $income . "</td>";
                        echo "</tr>";
                    }

                    // Display total income row
                    echo "<tr>";
                    echo "<td colspan='4' style='text-align: right;'>Total Income:</td>";
                    echo "<td>" . $totalIncome . "</td>";
                    echo "</tr>";
                } else {
                    echo "<tr><td colspan='5'>No records found</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>
