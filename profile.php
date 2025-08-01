<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$db = "dairy";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

// Fetch farmer details from the database
$fetchDetailsQuery = "SELECT id, name, location, breedOfCow, email, telephone FROM farmers WHERE id = ?";
$fetchDetailsStmt = $conn->prepare($fetchDetailsQuery);
$fetchDetailsStmt->bind_param("i", $userID);
$fetchDetailsStmt->execute();
$detailsResult = $fetchDetailsStmt->get_result();
$farmerDetails = $detailsResult->fetch_assoc();
$fetchDetailsStmt->close();

// Initialize update message
$updateMessage = "";

// Check if the form is submitted for updating details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newName = $_POST["name"];
    $newLocation = $_POST["location"];
    $newBreed = $_POST["breedOfCow"];
    $newEmail = $_POST["email"];
    $newTelephone = $_POST["telephone"];

    // Update details in the database
    $updateDetailsQuery = "UPDATE farmers SET name = ?, location = ?, breedOfCow = ?, email = ?, telephone = ? WHERE id = ?";
    $updateDetailsStmt = $conn->prepare($updateDetailsQuery);
    $updateDetailsStmt->bind_param("sssssi", $newName, $newLocation, $newBreed, $newEmail, $newTelephone, $userID);
    $updateResult = $updateDetailsStmt->execute();
    $updateDetailsStmt->close();

    if ($updateResult) {
        $updateMessage = "Personal details updated successfully!";
    } else {
        $updateMessage = "Error updating personal details. Please try again.";
    }

    // Fetch updated details for display
    $fetchDetailsStmt = $conn->prepare($fetchDetailsQuery);
    $fetchDetailsStmt->bind_param("i", $userID);
    $fetchDetailsStmt->execute();
    $detailsResult = $fetchDetailsStmt->get_result();
    $farmerDetails = $detailsResult->fetch_assoc();
    $fetchDetailsStmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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
            flex-direction: column;
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



        form {
            display: grid;
            grid-template-columns: repeat(3,1fr);
            gap: .5rem;
            width: 600px;
            margin: 20px auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            justify-content: center;
            align-items: center;
        }

        label {
            margin-top: 10px;
        }

        input {
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 3px;
            transition: border 0.3s;
        }

        button {
            background-color: #3498db;;
            color: #ecf0f1;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #34495e;
        }

        .message {
        width: 631px;
        margin-top: 20px;
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
        
    }

    .message.success {
        background-color: #27ae60;
        color: #ecf0f1;
    }

    .message.error {
        background-color: #c0392b;
        color: #ecf0f1;
    }
    </style>
</head>
<body>
    <header>
        <h1>Profile</h1>
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

        <?php
        if (!empty($updateMessage)) {
            echo '<div id="updateMessage" class="message success">' . $updateMessage . '</div>';
        }
        ?>
          
        <form id="profileForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

            
            <label for="name">Name:</label>
            <label for="email">Email:</label>
            <label for="telephone">Telephone:</label>

            <input type="text" id="name" name="name" value="<?php echo $farmerDetails['name']; ?>" readonly>
            <input type="email" id="email" name="email" value="<?php echo $farmerDetails['email']; ?>" readonly>            
            <input type="tel" id="telephone" name="telephone" value="<?php echo $farmerDetails['telephone']; ?>" readonly>

            <label for="id">ID:</label>
            <label for="location">Location:</label>
            <label for="breed">Breed of Cow:</label>
            
            <input type="text" id="id" name="id" value="<?php echo $farmerDetails['id']; ?>" readonly>
            <input type="text" id="location" name="location" value="<?php echo $farmerDetails['location']; ?>" readonly>
            <input type="text" id="breed" name="breedOfCow" value="<?php echo $farmerDetails['breedOfCow']; ?>" readonly>

            
            <button type="button" onclick="toggleEdit()">Enable Edit</button>
            <button type="submit" style="display: none;">Update Details</button>
            
        </form>
    </section>

    <script>
        function toggleEdit() {
            var form = document.getElementById("profileForm");
            var inputs = form.getElementsByTagName("input");
            var editButton = form.querySelector("button[type='button']");
            var updateButton = form.querySelector("button[type='submit']");

            // for (var i = 0; i < inputs.length; i++) {
            //     inputs[i].readOnly = !inputs[i].readOnly;
            // }
            for (var i = 0; i < inputs.length; i++) {
            // Exclude the ID field from being editable
            if (inputs[i].id !== "id") {
                inputs[i].readOnly = !inputs[i].readOnly;
            }
            }

            editButton.style.display = "none";
            updateButton.style.display = "block";
        }

        setTimeout(function () {
        var updateMessage = document.getElementById("updateMessage");
        if (updateMessage) {
            updateMessage.style.display = "none";
        }
    }, 5000);

S
    </script>

    <footer>
        &copy; 2023 Farmer Dashboard
    </footer>

    <!-- Include your existing scripts here -->
</body>
</html>
