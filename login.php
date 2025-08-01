<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$db = "dairy";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Check in admins table
    $adminQuery = "SELECT * FROM admins WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($adminQuery);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $adminResult = $stmt->get_result();

    if ($adminResult && $adminResult->num_rows > 0) {
        // Admin login successful
        $adminData = $adminResult->fetch_assoc();
        
        // Set user ID in the session
        $_SESSION['user_id'] = $adminData['id'];

        header("Location: admindb.php");
        exit();
    }

    // Check in farmers table
    $farmerQuery = "SELECT * FROM farmers WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($farmerQuery);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $farmerResult = $stmt->get_result();

    if ($farmerResult && $farmerResult->num_rows > 0) {
        // Farmer login successful
        $farmerData = $farmerResult->fetch_assoc();
        
        // Set user ID in the session
        $_SESSION['user_id'] = $farmerData['id'];

        header("Location: farmerdb.php");
        exit();
    }

    // If no match is found, display an error message
    echo "Email and password combination not found. Please try again.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
    <style>
body {
    font-family: 'Arial', sans-serif;
    background-image: url(images/pic.JPG);
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.login-container {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    text-align: center;
    max-width: 400px;
    width: 100%;
    transition: background 0.3s;
}

.login-container:hover {
    background: rgba(255, 255, 255, 0.9);
}

.login-form {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.input-field {
    width: 100%;
    margin: 10px 0;
    padding: 8px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.login-button {
    background-color: #4CAF50;
    color: #fff;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.login-button:hover {
    background-color: #45a049;
}

@media screen and (max-width: 600px) {
    .login-container {
        max-width: 80%; /* Adjust as needed for your design */
    }

    .login-form button,
    .login-form input,
    .login-form h2 {
        font-size: 18px;
    }

    .input-field {
        padding: 12px; /* Increase padding for better touch interaction */
    }
}
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Welcome to the Dairy management System</h2>
        <p>Login as:</p>
        <button onclick="showFarmerForm()">Farmer</button>
        <button onclick="showAdminForm()">Admin</button>

        <div class="login-form" id="farmerForm" style="display:none;">
            <h3>Farmer Login</h3>
            <form action="login.php" method="post">
                <input class="input-field" type="email" name="email" placeholder="Email" required>
                <input class="input-field" type="password" name="password" placeholder="Password" required>
                <input class="login-button" type="submit" value="Login">
            </form>
        </div>

        <div class="login-form" id="adminForm" style="display:none;">
            <h3>Admin Login</h3>
            <form action="login.php" method="post">
                <input class="input-field" type="email" name="email" placeholder="Email" required>
                <input class="input-field" type="password" name="password" placeholder="Password" required>
                <input class="login-button" type="submit" value="Login">
            </form>
    </div>

    <script>
        function showFarmerForm() {
            document.getElementById('farmerForm').style.display = 'block';
            document.getElementById('adminForm').style.display = 'none';
        }

        function showAdminForm() {
            document.getElementById('farmerForm').style.display = 'none';
            document.getElementById('adminForm').style.display = 'block';
        }
    </script>
</body>

</html>
