<?php
session_start(); // Start the session to access user data

$host = "localhost";
$user = "root";
$password = "";
$db = "dairy";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to the login page if not logged in
    exit();
}

// Get the logged-in user's ID from the session
$userID = $_SESSION['user_id'];

// Create a database connection
$conn = new mysqli($host, $user, $password, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $oldPassword = $_POST["old_password"];
    $newPassword = $_POST["new_password"];

    // Verify old password
    $verifyPasswordQuery = "SELECT password FROM farmers WHERE id = ?";
    $verifyPasswordStmt = $conn->prepare($verifyPasswordQuery);
    $verifyPasswordStmt->bind_param("i", $userID);
    $verifyPasswordStmt->execute();
    $verifyPasswordResult = $verifyPasswordStmt->get_result();
    $userData = $verifyPasswordResult->fetch_assoc();
    $verifyPasswordStmt->close();

    if ($oldPassword === $userData["password"]) {
        // Old password is correct, update the password
        $updatePasswordQuery = "UPDATE farmers SET password = ? WHERE id = ?";
        $updatePasswordStmt = $conn->prepare($updatePasswordQuery);
        $updatePasswordStmt->bind_param("si", $newPassword, $userID);
        $updatePasswordStmt->execute();
        $updatePasswordStmt->close();

        // Redirect to a success page or display a success message
        $successMessage = "Password updated successfully!";
    } else {
        $errorMessage = "Incorrect old password. Please try again.";
    }
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">




    <title>Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background-color: #2c3e50;
            padding: 20px;
            color: #ecf0f1;
            text-align: center;
        }

        nav {
            background-color: #34495e;
            padding: 10px;
        }

        nav a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 10px;
            margin: 0 10px;
        }

        section {
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .chart-container {
            width: 45%;
            margin: 10px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }

        footer {
            background-color: #2c3e50;
            padding: 10px;
            color: #ecf0f1;
            text-align: center;
        }

        /* Additional styling for the settings page */
        form {
            width: 400px;
            margin: 20px auto;
            padding: 50px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            justify-content: center;
            align-items: center;
        }

        form label {
            display: block;
            margin-bottom: 8px;
        }

        form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
        }

        form button {
            background-color: #3498db;
            color: #ecf0f1;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .success {
            background-color: #27ae60;
            color: #ecf0f1;
        }

        .error {
            background-color: #c0392b;
            color: #ecf0f1;
        }


        .password-container {
        position: relative;
    }

    .toggle-password-icon {
        position: absolute;
        top: 35%;
        right: 5px;
        transform: translateY(-50%);
        cursor: pointer;
    }
    </style>
</head>
<body>
    <header>
        <h1>Settings</h1>
    </header>

    <nav>
        <a href="farmerdb.php">Dashboard</a>
        <a href="farmerreports.php">Reports</a>
        <a href="profile.php">Profile</a>
        <a href="feedback.php">Feedback</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php">Logout</a>
    </nav>

    <section>
        

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <?php
        if (isset($successMessage)) {
            echo '<div class="message success">' . $successMessage . '</div>';
        } elseif (isset($errorMessage)) {
            echo '<div class="message error">' . $errorMessage . '</div>';
        }
        ?>
            <label for="old_password">Old Password:</label>
            <div class="password-container">
                <input type="password" name="old_password" id="old_password" required>
                <i class="toggle-password-icon far fa-eye" onclick="togglePasswordVisibility('old_password')"></i>
            </div>

            <label for="new_password">New Password:</label>
            <div class="password-container">
                <input type="password" name="new_password" id="new_password" required>
                <i class="toggle-password-icon far fa-eye" onclick="togglePasswordVisibility('new_password')"></i>
            </div>

            <button type="submit">Update Password</button>
        </form>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
        <script>
            function togglePasswordVisibility(elementId) {
                var passwordField = document.getElementById(elementId);
                var icon = document.querySelector(`#${elementId} + i`);

                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    icon.classList.remove("far", "fa-eye");
                    icon.classList.add("far", "fa-eye-slash");
                } else {
                    passwordField.type = "password";
                    icon.classList.remove("far", "fa-eye-slash");
                    icon.classList.add("far", "fa-eye");
                }
            }
        </script>
    </section>

    <footer>
        &copy; 2023 Farmer Dashboard
    </footer>

    <!-- Your existing scripts go here -->
</body>
</html>
