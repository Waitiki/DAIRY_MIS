<?php
include 'deleteUpdateRecords.php';
checkAdminAuthentication();
include 'db_connection.php';

// Get current date and first day of current month
$currentDate = date('Y-m-d');
$firstDayOfMonth = date('Y-m-01');

// Set default filter dates
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : $firstDayOfMonth;
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : $currentDate;

// Fetch all records for initial display with default month filter
$allRecordsQuery = "SELECT record_Id, farmer_Id, farmer_name, breedOfCow, quantity, rate, date_time FROM records 
                   WHERE DATE(date_time) BETWEEN '$startDate' AND '$endDate'";
$allRecordsResult = $conn->query($allRecordsQuery);

// Check if the query was successful
if ($allRecordsResult) {
    $allRecords = $allRecordsResult->fetch_all(MYSQLI_ASSOC);
} else {
    echo "<div class='error-message'>Error in fetching all records: " . $conn->error . "</div>";
    $allRecords = array();
}

// Fetch farmer names and IDs from the database
$query = "SELECT id, name, breedOfCow FROM farmers";
$result = $conn->query($query);

// Check if the query was successful
if ($result) {
    $farmerData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "<div class='error-message'>Error in fetching farmer data: " . $conn->error . "</div>";
    $farmerData = array();
}

$rateQuery = "SELECT value FROM rates WHERE name = 'rate'";
$rateResult = $conn->query($rateQuery);

if ($rateResult) {
    $rateRow = $rateResult->fetch_assoc();
    $rate = $rateRow['value'];
} else {
    echo "<div class='error-message'>Error fetching rate: " . $conn->error . "</div>";
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['farmerId'])) {
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
        die("<div class='error-message'>Error: " . $conn->error . "</div>");
    }

    $stmt->bind_param("sssssd", $farmerId, $farmerName, $breedOfCow, $quantity, $dateTime, $rate);

    if ($stmt->execute()) {
        echo "<div class='success-message'>Record added successfully.</div>";
    } else {
        echo "<div class='error-message'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Build the SQL query for fetching records with filtering
$filterQuery = "SELECT record_Id, farmer_Id, farmer_name, breedOfCow, quantity, date_time, rate FROM records WHERE 1";

if (isset($_POST['filterName']) && !empty($_POST['filterName'])) {
    $filterName = $_POST['filterName'];
    $filterQuery .= " AND farmer_Id LIKE '%$filterName%'";
}

if (isset($_POST['startDate']) && !empty($_POST['startDate'])) {
    $startDate = $_POST['startDate'];
    $filterQuery .= " AND DATE(date_time) >= '$startDate'";
}

if (isset($_POST['endDate']) && !empty($_POST['endDate'])) {
    $endDate = $_POST['endDate'];
    $filterQuery .= " AND DATE(date_time) <= '$endDate'";
}

$result = $conn->query($filterQuery);

// Check if the query was successful
if ($result) {
    $numRows = $result->num_rows;
} else {
    echo "<div class='error-message'>Error in fetching records: " . $conn->error . "</div>";
    $numRows = 0;
}

// Initialize total income variable
$totalIncome = 0;

// Check if there are rows before entering the loop
if ($numRows > 0) {
    // Calculate total income for filtered records
    while ($row = $result->fetch_assoc()) {
        $quantity = $row['quantity'];
        $income = $quantity * $rate;
        $totalIncome += $income;
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
        echo "<div class='info-message'>Total Income for Farmer ID $specificFarmerId: $specificTotalIncome</div>";
    } else {
        echo "<div class='error-message'>Error in fetching specific total income: " . $conn->error . "</div>";
    }
}

// Close the database connection
$conn->close();

// Get current date/time in format for datetime-local input
$currentDateTime = date('Y-m-d\TH:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milk Records Management</title>
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

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--medium-gray);
        }

        th {
            background-color: var(--primary-color);
            color: var(--white);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: rgba(76, 175, 80, 0.05);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }

        .btn i {
            margin-right: 5px;
        }

        .add-record-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s ease;
        }

        .add-record-btn:hover {
            background-color: var(--primary-dark);
            transform: scale(1.1);
        }

        .filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-input {
            padding: 10px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 0.9rem;
            flex: 1;
            min-width: 200px;
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: var(--white);
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
            color: var(--dark-gray);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .total-row {
            font-weight: 600;
            background-color: rgba(76, 175, 80, 0.1);
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        .info-message {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }

        @media screen and (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                gap: 10px;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.85rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn {
                width: 100%;
                padding: 6px 10px;
            }
        }

        /* Sidebar styles */
        .sidebar {
            background-color: #2c3e50;
            color: white;
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
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 10px;
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
            color: white;
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
        <div class="sidebar-header">ADMIN DASHBOARD</div>
        <ul>
            <li><a href="add_admin.php"><i class="fas fa-user-shield"></i> Admins</a></li>
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
            <h2><i class="fas fa-clipboard-list"></i> Milk Collection Records</h2>
            
            <div class="card">
                <form method="post" id="filterForm">
                    <div class="filter-container">
                        <input type="text" class="filter-input" id="filterName" name="filterName" placeholder="Filter by Farmer ID..." 
                               value="<?php echo isset($_POST['filterName']) ? htmlspecialchars($_POST['filterName']) : ''; ?>">
                        
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label for="startDate" class="form-label">From Date</label>
                            <input type="date" class="filter-input" id="startDate" name="startDate" 
                                   value="<?php echo $startDate; ?>">
                        </div>
                        
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label for="endDate" class="form-label">To Date</label>
                            <input type="date" class="filter-input" id="endDate" name="endDate" 
                                   value="<?php echo $endDate; ?>" max="<?php echo $currentDate; ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="align-self: flex-end;">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        
                        <button type="button" class="btn btn-outline" style="align-self: flex-end;" 
                                onclick="resetFilters()">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table id="recordsTable">
                        <thead>
                            <tr>
                                <th>Record ID</th>
                                <th>Farmer ID</th>
                                <th>Farmer Name</th>
                                <th>Cow Breed</th>
                                <th>Quantity (kg)</th>
                                <th>Rate (Ksh)</th>
                                <th>Income (Ksh)</th>
                                <th>Date/Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($numRows > 0) {
                                $result->data_seek(0);
                                while ($row = $result->fetch_assoc()) {
                                    $recordId = $row['record_Id'];
                                    $farmerId = $row['farmer_Id'];
                                    $farmerName = $row['farmer_name'];
                                    $breedOfCow = $row['breedOfCow'];
                                    $quantity = $row['quantity'];
                                    $rate = $row['rate'];
                                    $income = $quantity * $rate;
                                    $dateTime = $row['date_time'];
                                    
                                    echo "<tr>";
                                    echo "<td>$recordId</td>";
                                    echo "<td>$farmerId</td>";
                                    echo "<td>$farmerName</td>";
                                    echo "<td>$breedOfCow</td>";
                                    echo "<td>$quantity</td>";
                                    echo "<td>$rate</td>";
                                    echo "<td>$income</td>";
                                    echo "<td>$dateTime</td>";
                                    echo "<td class='action-buttons'>";
                                    echo "<button onclick=\"openUpdateForm('$recordId', '$quantity', '$rate', '$dateTime')\" class='btn btn-outline'><i class='fas fa-edit'></i> Edit</button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                
                                echo "<tr class='total-row'>";
                                echo "<td colspan='5' style='text-align: right;'><strong>Total Income:</strong></td>";
                                echo "<td colspan='4'>Ksh $totalIncome</td>";
                                echo "</tr>";
                            } else {
                                echo "<tr><td colspan='9' style='text-align: center;'>No records found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Record Button -->
    <div class="add-record-btn" onclick="openAddRecordForm()">
        <i class="fas fa-plus"></i>
    </div>

    <!-- Add Record Modal -->
    <div class="modal" id="addRecordModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAddRecordForm()">&times;</span>
            <h3><i class="fas fa-plus-circle"></i> Add New Record</h3>
            <form method="post" action="records.php">
                <div class="form-group">
                    <label for="farmerId" class="form-label">Farmer ID</label>
                    <input type="text" id="farmerId" name="farmerId" class="form-control" oninput="autocompleteFarmerName()" required>
                </div>
                
                <div class="form-group">
                    <label for="farmerName" class="form-label">Farmer Name</label>
                    <input type="text" id="farmerName" name="farmerName" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label for="breedOfCow" class="form-label">Breed of Cow</label>
                    <input type="text" id="breedOfCow" name="breedOfCow" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label for="quantity" class="form-label">Quantity (kg)</label>
                    <input type="number" step="0.01" id="quantity" name="quantity" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="rate" class="form-label">Rate (Ksh)</label>
                    <input type="number" step="0.01" id="rate" name="rate" class="form-control" value="<?php echo $rate; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="dateTime" class="form-label">Date/Time</label>
                    <input type="datetime-local" id="dateTime" name="dateTime" class="form-control" 
                           value="<?php echo $currentDateTime; ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Record</button>
            </form>
        </div>
    </div>
    
    <!-- Update Record Modal -->
    <div class="modal" id="updateRecordModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeUpdateForm()">&times;</span>
            <h3><i class="fas fa-edit"></i> Update Record</h3>
            <form onsubmit="event.preventDefault(); submitUpdateForm();">
                <input type="hidden" id="updateRecordId" name="updateRecordId">
                
                <div class="form-group">
                    <label for="updateQuantity" class="form-label">Quantity (kg)</label>
                    <input type="number" step="0.01" id="updateQuantity" name="updateQuantity" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="updateRate" class="form-label">Rate (Ksh)</label>
                    <input type="number" step="0.01" id="updateRate" name="updateRate" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="updateDateTime" class="form-label">Date/Time</label>
                    <input type="datetime-local" id="updateDateTime" name="updateDateTime" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Record</button>
            </form>
            <div id="updateMessage" class="mt-3"></div>
        </div>
    </div>

    <script>
        let originalData = <?php echo json_encode($allRecords); ?>;
        let filteredData = originalData.slice();

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
        }

        function updateTable() {
            const recordsTable = document.querySelector('#recordsTable tbody');
            let totalIncome = 0;

            // Clear the table body
            recordsTable.innerHTML = '';

            // Display filtered records
            if (filteredData.length > 0) {
                filteredData.forEach(row => {
                    const recordId = row.record_Id;
                    const farmerId = row.farmer_Id;
                    const farmerName = row.farmer_name;
                    const cowBreed = row.breedOfCow;
                    const quantity = row.quantity;
                    const rate = row.rate;
                    const income = quantity * rate;
                    const dateTime = row.date_time;
                    
                    totalIncome += income;

                    const rowElement = document.createElement('tr');
                    rowElement.innerHTML = `
                        <td>${recordId}</td>
                        <td>${farmerId}</td>
                        <td>${farmerName}</td>
                        <td>${cowBreed}</td>
                        <td>${quantity}</td>
                        <td>${rate}</td>
                        <td>${income}</td>
                        <td>${dateTime}</td>
                        <td class="action-buttons">
                            <button onclick="openUpdateForm('${recordId}', '${quantity}', '${rate}', '${dateTime}')" class="btn btn-outline">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </td>
                    `;
                    recordsTable.appendChild(rowElement);
                });

                // Add total row
                const totalRow = document.createElement('tr');
                totalRow.className = 'total-row';
                totalRow.innerHTML = `
                    <td colspan="5" style="text-align: right;"><strong>Total Income:</strong></td>
                    <td colspan="4">Ksh ${totalIncome.toFixed(2)}</td>
                `;
                recordsTable.appendChild(totalRow);
            } else {
                const noRecordsRow = document.createElement('tr');
                noRecordsRow.innerHTML = '<td colspan="9" style="text-align: center;">No records found</td>';
                recordsTable.appendChild(noRecordsRow);
            }
        }

        function resetFilters() {
            // Reset to current month (1st to today)
            const firstDay = new Date();
            firstDay.setDate(1);
            const firstDayStr = firstDay.toISOString().split('T')[0];
            
            const today = new Date().toISOString().split('T')[0];
            
            document.getElementById('filterName').value = '';
            document.getElementById('startDate').value = firstDayStr;
            document.getElementById('endDate').value = today;
            
            // Submit the form to apply changes
            document.getElementById('filterForm').submit();
        }
        
        function openAddRecordForm() {
            document.getElementById('addRecordModal').style.display = 'flex';
        }

        function closeAddRecordForm() {
            document.getElementById('addRecordModal').style.display = 'none';
        }

        function openUpdateForm(recordId, quantity, rate, dateTime) {
            document.getElementById('updateRecordId').value = recordId;
            document.getElementById('updateQuantity').value = quantity;
            document.getElementById('updateRate').value = rate;
            document.getElementById('updateDateTime').value = dateTime;
            document.getElementById('updateMessage').innerHTML = '';
            document.getElementById('updateRecordModal').style.display = 'flex';
        }

        function closeUpdateForm() {
            document.getElementById('updateRecordModal').style.display = 'none';
        }

        function submitUpdateForm() {
            const recordId = document.getElementById('updateRecordId').value;
            const quantity = document.getElementById('updateQuantity').value;
            const rate = document.getElementById('updateRate').value;
            const dateTime = document.getElementById('updateDateTime').value;

            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        document.getElementById('updateMessage').innerHTML = `
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i> ${xhr.responseText}
                            </div>
                        `;
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        document.getElementById('updateMessage').innerHTML = `
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> Error updating record. Please try again.
                            </div>
                        `;
                    }
                }
            };

            const data = new FormData();
            data.append('action', 'update');
            data.append('updateRecordId', recordId);
            data.append('updateQuantity', quantity);
            data.append('updateRate', rate);
            data.append('updateDateTime', dateTime);

            xhr.open('POST', 'deleteUpdateRecords.php', true);
            xhr.send(data);
        }

        function autocompleteFarmerName() {
            const farmerIdInput = document.getElementById('farmerId');
            const farmerNameInput = document.getElementById('farmerName');
            const breedOfCowInput = document.getElementById('breedOfCow');

            const farmerId = farmerIdInput.value.trim();
            const matchingFarmer = <?php echo json_encode($farmerData); ?>.find(farmer => farmer.id.includes(farmerId));

            if (matchingFarmer) {
                farmerNameInput.value = matchingFarmer.name;
                breedOfCowInput.value = matchingFarmer.breedOfCow;
            } else {
                farmerNameInput.value = '';
                breedOfCowInput.value = '';
            }
        }

        // Set max date for end date picker to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('endDate').max = today;
            
            // Initialize the table
            filterTable();
        });
    </script>
</body>
</html>