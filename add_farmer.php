
<!DOCTYPE html>
<html>
<head>
    <title>Add Farmer</title>
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

        .add-farmer-icon {
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

        .add-farmer-icon:hover {
            background: #4CAF50;
            transform: scale(1.1);
        }

        /* Responsive styles */
        @media screen and (max-width: 768px) {
            table {
                width: 100%;
                margin-left: 0;
            }

            .add-farmer-icon {
                right: 50%;
                transform: translateX(50%);
            }
        }

        /* Sidebar styles (added for reference) */
        .sidebar {
            /* Your existing styles for the sidebar */
        }

        .sidebar header {
            /* Your existing styles for the sidebar header */
        }

        .sidebar ul li {
            /* Your existing styles for sidebar list items */
        }

        .sidebar ul li a {
            /* Your existing styles for sidebar links */
        }
    </style>
	<link rel="stylesheet" href="style.css">
</head>
<body>
<input  type="checkbox" id="check">
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



<a href="farmerform.php" class="add-farmer-icon">+</a>

        <h2>Farmers Management</h2>

    <div class="table-container">
        <table>
            <tr>
                <th>Farmer Id </th>
                <th>Name</th>
                <th>Email</th>
                <th>Telephone</th>
                <th>Location</th>
				<th>Cow Breed</th>
            </tr>
                  <?php
	  $host = "localhost";
$user = "root";  // Replace with your actual database username
$password = "";  // Replace with your actual database password
$db = "dairy";

                // Replace with your database connection code
                $db = new mysqli($host, $user, $password, $db);

                if ($db->connect_error) {
                    die("Connection failed: " . $db->connect_error);
                }

                $query = "SELECT name ,id, email, telephone, location, breedOfCow FROM farmers";
                $result = $db->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['name'] . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . $row['telephone'] . "</td>";
                        echo "<td>" . $row['location'] . "</td>";
						echo "<td>" . $row['breedOfCow'] . "</td>";
                        echo "</tr>";
                    }
                }

                $db->close();
            ?>
      
        </table>
    </div>

   
</div>

<script>
    // JavaScript to toggle the visibility of the form
    const addFarmerIcon = document.getElementById('addFarmerIcon');
    const addFarmerForm = document.getElementById('addFarmerForm');

    addFarmerIcon.addEventListener('click', () => {
        addFarmerForm.style.display = addFarmerForm.style.display === 'block' ? 'none' : 'block';
    });
</script>
</body>
</html>