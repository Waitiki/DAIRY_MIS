<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$db = "dairy";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $userType = $_POST["user_type"];

    $table = $userType === "admin" ? "admins" : "farmers";
    $query = "SELECT * FROM $table WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $_SESSION['user_id'] = $userData['id'];
            $redirect = $userType === "admin" ? "admindb.php" : "farmerdb.php";
            header("Location: $redirect");
            exit();
        } else {
            $errorMessage = "Invalid email or password. Please try again.";
        }
        $stmt->close();
    } else {
        $errorMessage = "Error preparing query: " . htmlspecialchars($conn->error);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dairy Management System</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--dark-gray);
            line-height: 1.6;
        }

        .login-container {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
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

        .user-type-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .user-type-btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-weight: 600;
            flex: 1;
        }

        .user-type-btn.active {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .user-type-btn:not(.active) {
            background-color: var(--medium-gray);
            color: var(--dark-gray);
        }

        .user-type-btn:not(.active):hover {
            background-color: #d0d0d0;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-gray);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn i {
            margin-right: 8px;
        }

        .error-message {
            background-color: rgba(255, 0, 0, 0.1);
            color: #721c24;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            display: flex;
            align-items: center;
        }

        .error-message i {
            margin-right: 10px;
        }

        @media (max-width: 600px) {
            .login-container {
                padding: 20px;
                margin: 20px;
            }

            .user-type-btn {
                padding: 6px 12px;
                font-size: 0.85rem;
            }

            .form-control {
                padding: 8px 12px;
                font-size: 0.85rem;
            }

            .btn {
                padding: 8px 16px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2><i class="fas fa-sign-in-alt"></i> Dairy Management System</h2>
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        <div class="user-type-toggle">
            <button class="user-type-btn active" onclick="showFarmerForm()">Farmer</button>
            <button class="user-type-btn" onclick="showAdminForm()">Admin</button>
        </div>
        <div class="login-form" id="farmerForm">
            <form action="login.php" method="post">
                <input type="hidden" name="user_type" value="farmer">
                <div class="form-group">
                    <label for="farmerEmail" class="form-label">Email</label>
                    <input type="email" id="farmerEmail" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="farmerPassword" class="form-label">Password</label>
                    <input type="password" id="farmerPassword" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Login</button>
            </form>
        </div>
        <div class="login-form" id="adminForm" style="display:none;">
            <form action="login.php" method="post">
                <input type="hidden" name="user_type" value="admin">
                <div class="form-group">
                    <label for="adminEmail" class="form-label">Email</label>
                    <input type="email" id="adminEmail" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="adminPassword" class="form-label">Password</label>
                    <input type="password" id="adminPassword" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Login</button>
            </form>
        </div>
    </div>

    <script>
        function showFarmerForm() {
            document.getElementById('farmerForm').style.display = 'block';
            document.getElementById('adminForm').style.display = 'none';
            document.querySelectorAll('.user-type-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector('.user-type-btn:nth-child(1)').classList.add('active');
        }

        function showAdminForm() {
            document.getElementById('farmerForm').style.display = 'none';
            document.getElementById('adminForm').style.display = 'block';
            document.querySelectorAll('.user-type-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector('.user-type-btn:nth-child(2)').classList.add('active');
        }
    </script>
</body>
</html>