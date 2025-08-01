<?php

include 'deleteUpdateRecords.php';
checkAdminAuthentication();
include 'db_connection.php';

// Fetch all records for initial display
$allRecordsQuery = "SELECT record_Id, farmer_Id, farmer_name, breedOfCow, quantity, rate, date_time FROM records";
$allRecordsResult = $conn->query($allRecordsQuery);

// Check if the query was successful
if ($allRecordsResult) {
    $allRecords = $allRecordsResult->fetch_all(MYSQLI_ASSOC);
} else {
    echo "Error in fetching all records: " . $conn->error;
    $allRecords = array();
}

// Fetch farmer names and IDs from the database
$query = "SELECT id, name, breedOfCow FROM farmers";
$result = $conn->query($query);

// Check if the query was successful
if ($result) {
    $farmerData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "Error in fetching farmer data: " . $conn->error;
    $farmerData = array();
}

$rateQuery = "SELECT value FROM rates WHERE name = 'rate'";
$rateResult = $conn->query($rateQuery);

if ($rateResult) {
    $rateRow = $rateResult->fetch_assoc();
    $rate = $rateRow['value'];
} else {
    // Default rate if there is an issue fetching from the database
    // $rate = 50;
    echo "Error fetching rate: " . $conn->error;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $farmerId = $_POST['farmerId'];
    $farmerName = $_POST['farmerName'];
    $breedOfCow = $_POST['breedOfCow'];
    $quantity = $_POST['quantity'];
    $dateTime = $_POST['dateTime'];
    $rate = $_POST['rate'];

    // Prepare and execute the SQL query to insert data
    $query = "INSERT INTO records (farmer_Id, farmer_name, breedOfCow, quantity, date_time, rate) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error: " . $conn->error);
    }

    $stmt->bind_param("sssssd", $farmerId, $farmerName, $breedOfCow, $quantity, $dateTime, $rate);

    if ($stmt->execute()) {
        echo "Record added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}


// Build the SQL query for fetching records with filtering
$filterQuery = "SELECT record_Id, farmer_Id, farmer_name, breedOfCow, quantity, date_time, rate FROM records WHERE 1";

if (isset($_POST['filterDate']) && !empty($_POST['filterDate'])) {
    $filterDate = $_POST['filterDate'];
    $filterQuery .= " AND DATE(date_time) = '$filterDate'";
}

$result = $conn->query($filterQuery);

// Check if the query was successful
if ($result) {
    $numRows = $result->num_rows;
} else {
    echo "Error in fetching records: " . $conn->error;
    $numRows = 0;
}

// Initialize total income variable
$totalIncome = 0;

// Check if there are rows before entering the loop
if ($numRows > 0) {
    // Calculate total income for filtered records
    while ($row = $result->fetch_assoc()) {
        $quantity = $row['quantity'];
        $income = $quantity * $rate; // Calculate income based on quantity
        $totalIncome += $income; // Accumulate income for each row
    }
}

// Display specific total income for an individual farmer
if (isset($_POST['specificFarmerId']) && !empty($_POST['specificFarmerId'])) {
    $specificFarmerId = $_POST['specificFarmerId'];
    $specificTotalIncomeQuery = "SELECT SUM(quantity * $rate) as totalIncome FROM records WHERE farmer_Id = '$specificFarmerId'";
    $specificTotalIncomeResult = $conn->query($specificTotalIncomeQuery);

    if ($specificTotalIncomeResult) {
        $specificTotalIncomeRow = $specificTotalIncomeResult->fetch_assoc();
        $specificTotalIncome = $specificTotalIncomeRow['totalIncome'];
        echo "Total Income for Farmer ID $specificFarmerId: $specificTotalIncome";
    } else {
        echo "Error in fetching specific total income: " . $conn->error;
    }
}

// Close the database connection
$conn->close();


?>
<!DOCTYPE html>
<html>

<head>
    <title>Records Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('background.jpg'); /* Replace with your background image */
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #4CAF50;
        }

        .table-container {
            margin: 20px auto;
			margin-left:200px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.9);
            border-radius: 5px;
            overflow: hidden;
            width: 80%; /* Adjust the width as needed */
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #4CAF50;
            color: white;
        }

        .add-record{
			margin-left:300px;
			margin-top:100px;
		    font-size:40px;
			
		}
        .update-record,
        .delete-record {
            font-size: 20px;
            cursor: pointer;
            margin: 5px;
            color: #4CAF50;
        }

        .add-record:hover,
        .update-record:hover,
        .delete-record:hover {
            color: #45a049;
        }

        .search-bar,
        .filter-bar {
            margin: 20px;
            float: right;
        }

        .search-input,
        .filter-input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .add-record-form,
        .update-record-form,
        .delete-record-form {
            display: none;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
        }

        .blurred-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            z-index: 1;
        }

        .close-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        .symbol {
            font-size: 18px;
            margin-right: 5px;
        }

        /* Style for update and delete buttons in the table */
        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .update-record-button,
        .delete-record-button {
            font-size: 14px;
            padding: 8px 12px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            outline: none;
        }

        .update-record-button:hover,
        .delete-record-button:hover {
            background-color: #45a049;
        }

        .records-btns{
            background-color: white;
            color: black;
            padding: 3px;
            font-size: medium;
            font-weight: 300;
            font-family: ui-monospace;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0px 0px 5px red;
            cursor: pointer;
        }

		 @media screen and (max-width: 768px) {
    .table-container {
        width: 100%;
        max-width: none;
        margin: 10px; /* Add some margin to improve spacing */
    }

    table {
        font-size: 14px; /* Decrease font size for better readability */
    }

    th, td {
        padding: 8px 10px; /* Reduce padding for better spacing */
    }

    .search-bar,
    .filter-bar {
        margin: 10px; /* Adjust margin for better spacing */
        text-align: center; /* Center the search and filter bars */
    }

    .search-input,
    .filter-input {
        width: 100%; /* Make search and filter inputs full width */
        box-sizing: border-box; /* Include padding and border in the width */
        margin-bottom: 10px; /* Add some bottom margin for spacing */
    }

    .add-record,
    .update-record,
    .delete-record {
        font-size: 16px; /* Increase button font size for better tap targets */
        cursor: pointer;
    }

    .add-record-form,
    .update-record-form,
    .delete-record-form {
        padding: 10px; /* Adjust padding for better spacing */
        cursor: pointer;
    }
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

    <h2>Records Management</h2>

  

    <div class="table-container">
	  <span class="add-record" onclick="openAddRecordForm()">+</span>

        <div class="filter-bar">
            <input type="text" class="filter-input" id="filterName" placeholder="Filter by ID..." oninput="filterTable()">
            <input type="date" class="filter-input" id="filterDate" oninput="filterTable()">
        </div>

    <!-- Display records fetched from the database -->
    <table id="recordsTable">
    <tr>
        <th>RecordID</th>
        <th>FarmerID</th>
        <th>Farmer Name</th>
        <th>Cow Breed</th>
        <th>Quantity</th>
        <th>Rate</th>
        <th>Income</th>
        <th>Date/Time Taken</th>
        <th>Action</th>
    </tr>
    <!-- Display records fetched from the database -->
    <?php

       
        
    if ($numRows > 0) {
        $result->data_seek(0); // Reset result set pointer to the beginning
        while ($row = $result->fetch_assoc()) {

            $recordId = $row['record_Id'];
            $farmerId = $row['farmer_Id'];
            $farmerName = $row['farmer_name'];
            $breedOfCow = $row['breedOfCow'];
            $quantity = $row['quantity'];
            $rate = $row['rate'];
            $dateTime = $row['date_time'];

            while ($row = $result->fetch_assoc()) {
                $quantity = $row['quantity'];
                $income = $quantity * $rate; // Calculate income based on quantity and dynamic rate
                $totalIncome += $income; // Accumulate income for each row
            }

           
            

            echo "<tr>";
            echo "<td>" . $recordId . "</td>";
            echo "<td>" . $farmerId . "</td>";
            echo "<td>" . $farmerName . "</td>";
            echo "<td>" . $breedOfCow . "</td>";
            echo "<td>" . $quantity . " kg</td>";
            echo "<td>" . $rate . " </td>";
            
            echo "<td>" . $income . "</td>";
            echo "<td class='action-buttons'>
            <button onclick=\"openUpdateForm('$recordId', '$quantity', '$rate', '$dateTime')\" class='update-record-button'>Update</button>
                 </td>";

            echo "<td>" . $dateTime . "</td>";
            echo "</tr>";
        }

        // Display total income row
        echo "<tr>";
        echo "<td colspan='6' style='text-align: right;'>Total Income:</td>";
        echo "<td>" . $totalIncome . "</td>";
        echo "<td></td>"; // Empty column for actions in total income row
        echo "</tr>";
    } else {
        echo "<tr><td colspan='7'>No records found</td></tr>";
    }
    ?>
    </table>


    </div>

    <!-- Blurred Background -->
    <div class="blurred-background" id="blurredBackground"></div>

    <!-- Add Record Form (Hidden by default) -->
    <div class="add-record-form" id="addRecordForm">
        <span class="close-icon" onclick="closeAddRecordForm()">✖</span>
        <h3>Add a Record</h3>
        <form method="post" action="records.php">
            <label for="farmerId">Farmer ID:</label>
            <input type="text" id="farmerId" name="farmerId" oninput="autocompleteFarmerName()" required><br><br>

            <label for="farmerName">Farmer Name:</label>
            <input type="text" id="farmerName" name="farmerName" readonly><br><br>
			
			<label for="breedOfCow">Breed of cow:</label>
			 <input type="text" id="breedOfCow" name="breedOfCow" readonly oninput="autocompleteBreedOfCow()"><br><br>


            <label for="quantity">Quantity:</label>
            <input type="text" id="quantity" name="quantity" required><br><br>

            <label for="rate">Rate:</label>
            <input type="text" id="rate" name="rate" value="<?php echo $rate; ?>" required><br><br>
			

            <label for="dateTime">Date/Time Taken:</label>
            <input type="datetime-local" id="dateTime" name="dateTime" required><br><br>

            <input class="records-btns" type="submit" value="Add Record">
        </form>
    </div>
	
	 <!--Update form -->
     <div class="update-record-form" id="updateRecordForm">
        <span class="close-icon" onclick="closeUpdateForm()">✖</span>
        <h3>Update Record</h3>
        <form onsubmit="event.preventDefault(); submitUpdateForm();">
            <input type="hidden" id="updateRecordId" name="updateRecordId">
            <label for="updateQuantity">Quantity:</label>
            <input type="text" id="updateQuantity" name="updateQuantity" required><br><br>

            <label for="updateRate">Rate:</label>
            <input type="text" id="updateRate" name="updateRate" required><br><br>

            <label for="updateDateTime">Date/Time Taken:</label>
            <input type="datetime-local" id="updateDateTime" name="updateDateTime" required><br><br>

            <input class="records-btns" type="submit" value="Update Record">
        </form>
        <div id="updateMessage"></div> <!-- Display the update message here -->
        </div>

   <script>
    let originalData = <?php echo json_encode($allRecords); ?>;
    let filteredData = originalData.slice(); // Copy the original data for initial display

    function filterTable() {
        const filterName = document.getElementById('filterName').value.toLowerCase();
        const filterDate = document.getElementById('filterDate').value;

        filteredData = originalData.filter(row => {
            const farmerId = row.farmer_Id.toLowerCase();
            const dateTime = row.date_time.split(' ')[0].toLowerCase();

            const idMatch = farmerId.includes(filterName);
            const dateMatch = filterDate === '' || dateTime === filterDate;

            return idMatch && dateMatch;
        });

        updateTable();
        updateTotalIncome();
    }

	
    function openUpdateForm(recordId, quantity, rate, dateTime) {
        document.getElementById('updateRecordId').value = recordId;
        document.getElementById('updateQuantity').value = quantity;
        document.getElementById('updateRate').value = rate;
        document.getElementById('updateDateTime').value = dateTime;
        document.getElementById('updateMessage').innerHTML = ''; // Clear previous update message
        document.getElementById('updateRecordForm').style.display = 'block';
        const blurredBackground = document.getElementById('blurredBackground');
        blurredBackground.style.display = 'block';
    }

    function closeUpdateForm() {
        document.getElementById('updateRecordForm').style.display = 'none';
        const blurredBackground = document.getElementById('blurredBackground');
        blurredBackground.style.display = 'none';
    }

    function submitUpdateForm() {
        var recordId = document.getElementById('updateRecordId').value;
        var quantity = document.getElementById('updateQuantity').value;
        var rate = document.getElementById('updateRate').value;
        var dateTime = document.getElementById('updateDateTime').value;

        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                    // Display the update message
                    document.getElementById('updateMessage').innerHTML = xhr.responseText;
                    // Optionally, you can reload the page after a successful update
                    setTimeout(function () {
                    location.reload();
                }, 2500);
                } else {
                    console.error('Error:', xhr.statusText);
                    // Display the error message
                    document.getElementById('updateMessage').innerHTML = 'Update action failed. Please check the console for details.';
                }
            }
        };

        var data = new FormData();
        data.append('action', 'update');
        data.append('updateRecordId', recordId);
        data.append('updateQuantity', quantity);
        data.append('updateRate', rate);
        data.append('updateDateTime', dateTime);

        xhr.open('POST', 'deleteUpdateRecords.php', true);
        xhr.send(data);
    }








    function updateTable() {
        const recordsTable = document.getElementById('recordsTable');
        const totalIncomeElement = document.getElementById('totalIncome');
        let totalIncome = 0;

        // Clear the table
        recordsTable.innerHTML = '';

        // Display filtered records
        if (filteredData.length > 0) {
            let tableHTML = '<tr><th>RecordID</th><th>FarmerID</th><th>Farmer Name</th><th>Cow Breed</th><th>Quantity</th><th>Rate</th><th>Income</th><th>Date/Time Taken</th><th>Action</th></tr>';
            for (let i = 0; i < filteredData.length; i++) {
                const row = filteredData[i];
                const recordId = row.record_Id;
                const farmerId = row.farmer_Id;
                const farmerName = row.farmer_name;
                const cowBreed = row.breedOfCow;
                const quantity = row.quantity;
                const rate = row.rate;
                const income = quantity * rate;
                const dateTime = row.date_time;
                

                tableHTML += `<tr><td>${recordId}</td><td>${farmerId}</td><td>${farmerName}</td><td>${cowBreed}</td><td>${quantity} kg</td><td>${rate}</td><td>${income}</td><td>${dateTime}</td><td class='action-buttons'>
                    <button onclick="openUpdateForm('${recordId}', '${quantity}', '${rate}', '${dateTime}')" class='update-record-button'>Update</button>
                    </td></tr>`;
                totalIncome += income;
            }

            // Display total income row
            tableHTML += `<tr><td colspan='6' style='text-align: right;'>Total Income:</td><td>${totalIncome}</td><td></td></tr>`;
            recordsTable.innerHTML = tableHTML;
        } else {
            recordsTable.innerHTML = "<tr><td colspan='7'>No records found</td></tr>";
        }

        // Display total income for filtered data
        if (totalIncomeElement) {
            totalIncomeElement.textContent = totalIncome.toFixed(2);
        }
    }

    function updateTotalIncome() {
        let totalIncome = 0;

        // Calculate total income based on the filtered data
        for (let i = 0; i < filteredData.length; i++) {
            const row = filteredData[i];
            const quantity = parseFloat(row.quantity);
            totalIncome += quantity * $rate;
        }

        // Display total income for filtered data
        const totalIncomeElement = document.getElementById('totalIncome');
        if (totalIncomeElement) {
            totalIncomeElement.textContent = totalIncome.toFixed(2);
        }
    }

    // Initial display of the table and total income
    filterTable();

    function openAddRecordForm() {
        const addRecordForm = document.getElementById('addRecordForm');
        addRecordForm.style.display = 'block';
        const blurredBackground = document.getElementById('blurredBackground');
        blurredBackground.style.display = 'block';
    }

    function closeAddRecordForm() {
        const addRecordForm = document.getElementById('addRecordForm');
        addRecordForm.style.display = 'none';
        const blurredBackground = document.getElementById('blurredBackground');
        blurredBackground.style.display = 'none';
    }

    function autocompleteFarmerName() {
        const farmerIdInput = document.getElementById('farmerId');
        const farmerNameInput = document.getElementById('farmerName');
        const breedOfCowInput = document.getElementById('breedOfCow');

        // Get the entered farmer ID
        const farmerId = farmerIdInput.value.trim();

        // Find the corresponding farmer in the data
        const matchingFarmer = <?php echo json_encode($farmerData); ?>.find(farmer => farmer.id.includes(farmerId));

        if (matchingFarmer) {
            // Update the farmer name input field
            farmerNameInput.value = matchingFarmer.name;

            // Fetch the breed of cow information from the database using AJAX or another method
            // In this example, I'm assuming that the breed information is available in the farmer object
            const breedOfCow = matchingFarmer.breedOfCow;

            // Update the breed of cow input field
            breedOfCowInput.value = breedOfCow;
        } else {
            // Clear the inputs if no matching farmer is found
            farmerNameInput.value = '';
            breedOfCowInput.value = '';
        }
    }
</script>

</body>

</html>