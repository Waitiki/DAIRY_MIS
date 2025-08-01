<?php
include 'deleteUpdateRecords.php';
checkAdminAuthentication();
include 'db_connection.php';

$currentDate = date('Y-m-d');
$firstDayOfMonth = date('Y-m-01');

$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : $firstDayOfMonth;
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : $currentDate;

$allRecordsQuery = "SELECT record_Id, farmer_Id, farmer_name, breedOfCow, quantity, rate, date_time FROM records WHERE DATE(date_time) BETWEEN ? AND ?";
$stmt = $conn->prepare($allRecordsQuery);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$allRecordsResult = $stmt->get_result();

$allRecords = $allRecordsResult ? $allRecordsResult->fetch_all(MYSQLI_ASSOC) : [];
if (!$allRecordsResult) {
    $errorMessage = "Error fetching records: " . $conn->error;
}

$query = "SELECT id, name, breedOfCow FROM farmers";
$farmerResult = $conn->query($query);
$farmerData = $farmerResult ? $farmerResult->fetch_all(MYSQLI_ASSOC) : [];
if (!$farmerResult) {
    $errorMessage = "Error fetching farmer data: " . $conn->error;
}

$rateQuery = "SELECT value FROM rates WHERE name = 'rate'";
$rateResult = $conn->query($rateQuery);
$rate = $rateResult && $rateResult->num_rows > 0 ? $rateResult->fetch_assoc()['value'] : 0;
if (!$rateResult) {
    $errorMessage = "Error fetching rate: " . $conn->error;
}

$successMessage = "";
$errorMessage = isset($errorMessage) ? $errorMessage : "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['farmerId'])) {
    $farmerId = $_POST['farmerId'];
    $farmerName = $_POST['farmerName'];
    $breedOfCow = $_POST['breedOfCow'];
    $quantity = $_POST['quantity'];
    $dateTime = $_POST['dateTime'];
    $rate = $_POST['rate'];

    $stmt = $conn->prepare("INSERT INTO records (farmer_Id, farmer_name, breedOfCow, quantity, date_time, rate) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssssd", $farmerId, $farmerName, $breedOfCow, $quantity, $dateTime, $rate);
        if ($stmt->execute()) {
            $successMessage = "Record added successfully.";
            header("Location: records.php");
            exit();
        } else {
            $errorMessage = "Error adding record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "Error preparing statement: " . $conn->error;
    }
}

$filterQuery = "SELECT record_Id, farmer_Id, farmer_name, breedOfCow, quantity, rate, date_time FROM records WHERE 1";
$params = [];
$types = "";

if (isset($_POST['filterName']) && !empty($_POST['filterName'])) {
    $filterName = "%" . $_POST['filterName'] . "%";
    $filterQuery .= " AND farmer_Id LIKE ?";
    $params[] = $filterName;
    $types .= "s";
}

if (isset($_POST['startDate']) && !empty($_POST['startDate'])) {
    $filterQuery .= " AND DATE(date_time) >= ?";
    $params[] = $startDate;
    $types .= "s";
}

if (isset($_POST['endDate']) && !empty($_POST['endDate'])) {
    $filterQuery .= " AND DATE(date_time) <= ?";
    $params[] = $endDate;
    $types .= "s";
}

$stmt = $conn->prepare($filterQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$numRows = $result ? $result->num_rows : 0;
if (!$result) {
    $errorMessage = "Error fetching records: " . $conn->error;
}

$totalIncome = 0;
if ($numRows > 0) {
    while ($row = $result->fetch_assoc()) {
        $totalIncome += $row['quantity'] * $row['rate'];
    }
    $result->data_seek(0);
}

if (isset($_POST['specificFarmerId']) && !empty($_POST['specificFarmerId'])) {
    $specificFarmerId = $_POST['specificFarmerId'];
    $stmt = $conn->prepare("SELECT SUM(quantity * rate) as totalIncome FROM records WHERE farmer_Id = ?");
    $stmt->bind_param("s", $specificFarmerId);
    $stmt->execute();
    $specificTotalIncomeResult = $stmt->get_result();
    if ($specificTotalIncomeResult) {
        $specificTotalIncome = $specificTotalIncomeResult->fetch_assoc()['totalIncome'];
        $infoMessage = "Total Income for Farmer ID $specificFarmerId: Ksh " . number_format($specificTotalIncome, 2);
    } else {
        $errorMessage = "Error fetching specific total income: " . $conn->error;
    }
}

$conn->close();
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
            font-weight: 600;
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
            font-weight: 600;
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
            box-shadow: var(--shadow);
            cursor: pointer;
            z-index: 1000;
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

        /* Enhanced Filter Styles */
        .filter-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(76, 175, 80, 0.1);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-header {
            margin-bottom: 25px;
            text-align: center;
        }

        .filter-header h3 {
            color: var(--primary-color);
            margin: 0 0 8px 0;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .filter-subtitle {
            color: #6c757d;
            margin: 0;
            font-size: 0.95rem;
            font-style: italic;
        }

        .enhanced-filter-form {
            width: 100%;
        }

        .filter-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px !important;
            align-items: end;
        }

        .filter-item {
            margin: 0;
        }

        .filter-item .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #495057;
            margin-bottom: 8px;
        }

        .filter-item .form-label i {
            color: var(--primary-color);
            font-size: 0.85rem;
        }

        .filter-item .form-control {
            background-color: var(--white);
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            width: 80%;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .filter-item .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
            transform: translateY(-1px);
        }

        .filter-item .form-control:hover {
            border-color: #ced4da;
        }

        .filter-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            margin-top: 10px;
        }

        .filter-btn {
            padding: 12px 24px;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 140px;
            justify-content: center;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: var(--white);
            border: 1px solid #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }

        /* Responsive adjustments for enhanced filter */
        @media (max-width: 768px) {
            .filter-section {
                padding: 20px 15px;
            }

            .filter-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .filter-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-btn {
                width: 100%;
                min-width: auto;
            }

            .filter-header h3 {
                font-size: 1.2rem;
            }
        }

        .form-group {
            flex: 1;
            min-width: 200px;
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
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }

        select.form-control {
            appearance: none;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path fill="%234CAF50" d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center;
            background-size: 12px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
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

        .total-row {
            font-weight: 600;
            background-color: rgba(76, 175, 80, 0.1);
        }

        .success-message {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--primary-dark);
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
            display: flex;
            align-items: center;
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

        .info-message {
            background-color: rgba(23, 162, 184, 0.1);
            color: #0c5460;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
            display: flex;
            align-items: center;
        }

        .success-message i, .error-message i, .info-message i {
            margin-right: 10px;
        }

        /* Sidebar styles */
        .sidebar {
            background-color: #2c3e50;
            color: var(--white);
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
            font-weight: 600;
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
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .sidebar ul li.active {
            background-color: var(--primary-color);
            border-left: 4px solid var(--white);
        }

        .sidebar ul li.active a {
            color: var(--white);
            font-weight: 600;
        }

        .sidebar ul li.active:hover {
            background-color: var(--primary-dark);
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
            color: var(--white);
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

            .filter-container {
                flex-direction: column;
                gap: 10px;
            }

            .form-group {
                min-width: 100%;
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

            .add-record-btn {
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <input type="checkbox" id="check">
    <label for="check">
        <i class="fas fa-bars" id="btn"></i>
        <i class="fas fa-times" id="cancel"></i>
    </label>
    
    <div class="sidebar">
        <div class="sidebar-header">ADMIN PANEL</div>
        <ul>
            <li><a href="admindb.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="add_admin.php"><i class="fas fa-user-shield"></i> Admins</a></li>
            <li><a href="add_farmer.php"><i class="fas fa-users"></i> Farmers</a></li>
            <li class="active"><a href="records.php"><i class="fas fa-clipboard-list"></i> Records</a></li>
            <li><a href="sendNotifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
            <li><a href="viewfeedbacks.php"><i class="fas fa-comment-alt"></i> Feedback</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
            <h2><i class="fas fa-clipboard-list"></i> Milk Collection Records</h2>
            
            <?php if (!empty($successMessage)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($infoMessage)): ?>
                <div class="info-message">
                    <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($infoMessage); ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="filter-section">
                    <div class="filter-header">
                        <h3><i class="fas fa-filter"></i> Filter Records</h3>
                        <p class="filter-subtitle">Use the filters below to search and filter milk collection records</p>
                    </div>
                    <form method="post" id="filterForm" class="enhanced-filter-form">
                        <div class="filter-grid">
                            <div class="filter-row">
                                <div class="form-group filter-item">
                                    <label for="filterName" class="form-label">
                                        <i class="fas fa-user"></i> Farmer ID
                                    </label>
                                    <input type="text" class="form-control" id="filterName" name="filterName" 
                                           placeholder="Enter Farmer ID to search..." 
                                           value="<?php echo isset($_POST['filterName']) ? htmlspecialchars($_POST['filterName']) : ''; ?>">
                                </div>
                                <div class="form-group filter-item">
                                    <label for="startDate" class="form-label">
                                        <i class="fas fa-calendar-alt"></i> From Date
                                    </label>
                                    <input type="date" class="form-control" id="startDate" name="startDate" 
                                           value="<?php echo htmlspecialchars($startDate); ?>">
                                </div>
                                <div class="form-group filter-item">
                                    <label for="endDate" class="form-label">
                                        <i class="fas fa-calendar-alt"></i> To Date
                                    </label>
                                    <input type="date" class="form-control" id="endDate" name="endDate" 
                                           value="<?php echo htmlspecialchars($endDate); ?>" 
                                           max="<?php echo htmlspecialchars($currentDate); ?>">
                                </div>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary filter-btn">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <button type="button" class="btn btn-outline filter-btn" onclick="resetFilters()">
                                    <i class="fas fa-undo"></i> Reset Filters
                                </button>
                                <button type="button" class="btn btn-secondary filter-btn" onclick="exportFilteredData()">
                                    <i class="fas fa-download"></i> Export Data
                                </button>
                                <button type="button" class="btn btn-secondary filter-btn" onclick="printFilteredRecords()" style="background-color: #17a2b8;">
                                    <i class="fas fa-print"></i> Print Records
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
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
                                while ($row = $result->fetch_assoc()) {
                                    $recordId = htmlspecialchars($row['record_Id']);
                                    $farmerId = htmlspecialchars($row['farmer_Id']);
                                    $farmerName = htmlspecialchars($row['farmer_name']);
                                    $breedOfCow = htmlspecialchars($row['breedOfCow']);
                                    $quantity = htmlspecialchars($row['quantity']);
                                    $rate = htmlspecialchars($row['rate']);
                                    $income = number_format($quantity * $rate, 2);
                                    $dateTime = htmlspecialchars($row['date_time']);
                                    
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
                                    echo "<button onclick=\"printSingleRecord('$recordId')\" class='btn btn-outline' style='background-color: #17a2b8; color: white;'><i class='fas fa-print'></i> Print</button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                echo "<tr class='total-row'>";
                                echo "<td colspan='5' style='text-align: right;'><strong>Total Income:</strong></td>";
                                echo "<td colspan='4'>Ksh " . number_format($totalIncome, 2) . "</td>";
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

    <div class="add-record-btn" onclick="openAddRecordForm()">
        <i class="fas fa-plus"></i>
    </div>

    <div class="modal" id="addRecordModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAddRecordForm()">&times;</span>
            <h3><i class="fas fa-plus-circle"></i> Add New Record</h3>
            <form method="post" action="records.php">
                <div class="form-group">
                    <label for="farmerId" class="form-label">Farmer</label>
                    <select id="farmerId" name="farmerId" class="form-control" onchange="autocompleteFarmerName()" required>
                        <option value="">Select a Farmer</option>
                        <?php
                        foreach ($farmerData as $farmer) {
                            echo '<option value="' . htmlspecialchars($farmer['id']) . '">' . htmlspecialchars($farmer['name']) . ' (ID: ' . htmlspecialchars($farmer['id']) . ')</option>';
                        }
                        ?>
                    </select>
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
                    <input type="number" step="0.01" id="rate" name="rate" class="form-control" value="<?php echo htmlspecialchars($rate); ?>" required>
                </div>
                <div class="form-group">
                    <label for="dateTime" class="form-label">Date/Time</label>
                    <input type="datetime-local" id="dateTime" name="dateTime" class="form-control" 
                           value="<?php echo htmlspecialchars($currentDateTime); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Record</button>
            </form>
        </div>
    </div>

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
            filteredData = originalData.filter(row => {
                const farmerId = row.farmer_Id.toLowerCase();
                return farmerId.includes(filterName);
            });
            updateTable();
        }

        function updateTable() {
            const recordsTable = document.querySelector('#recordsTable tbody');
            let totalIncome = 0;
            recordsTable.innerHTML = '';

            if (filteredData.length > 0) {
                filteredData.forEach(row => {
                    const recordId = row.record_Id;
                    const farmerId = row.farmer_Id;
                    const farmerName = row.farmer_name;
                    const cowBreed = row.breedOfCow;
                    const quantity = row.quantity;
                    const rate = row.rate;
                    const income = (quantity * rate).toFixed(2);
                    const dateTime = row.date_time;
                    totalIncome += parseFloat(income);

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
                            <button onclick="printSingleRecord('${recordId}')" class="btn btn-outline" style="background-color: #17a2b8; color: white;">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </td>
                    `;
                    recordsTable.appendChild(rowElement);
                });

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
            document.getElementById('filterName').value = '';
            document.getElementById('startDate').value = '<?php echo htmlspecialchars($firstDayOfMonth); ?>';
            document.getElementById('endDate').value = '<?php echo htmlspecialchars($currentDate); ?>';
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
            document.getElementById('updateDateTime').value = dateTime.replace(' ', 'T');
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
                        setTimeout(() => location.reload(), 1500);
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
            const farmerIdSelect = document.getElementById('farmerId');
            const farmerNameInput = document.getElementById('farmerName');
            const breedOfCowInput = document.getElementById('breedOfCow');
            const farmerId = farmerIdSelect.value;
            const matchingFarmer = <?php echo json_encode($farmerData); ?>.find(farmer => farmer.id === farmerId);

            if (matchingFarmer) {
                farmerNameInput.value = matchingFarmer.name;
                breedOfCowInput.value = matchingFarmer.breedOfCow;
            } else {
                farmerNameInput.value = '';
                breedOfCowInput.value = '';
            }
        }

        function exportFilteredData() {
            const filterName = document.getElementById('filterName').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            // Create a form to submit the export request
            const exportForm = document.createElement('form');
            exportForm.method = 'POST';
            exportForm.action = 'export_records.php';
            
            // Add filter parameters
            const addField = (name, value) => {
                const field = document.createElement('input');
                field.type = 'hidden';
                field.name = name;
                field.value = value;
                exportForm.appendChild(field);
            };
            
            addField('filterName', filterName);
            addField('startDate', startDate);
            addField('endDate', endDate);
            addField('export', 'csv');
            
            document.body.appendChild(exportForm);
            exportForm.submit();
            document.body.removeChild(exportForm);
        }

        function printFilteredRecords() {
            const filterName = document.getElementById('filterName').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            const printUrl = `print_filtered_records.php?filterName=${encodeURIComponent(filterName)}&startDate=${encodeURIComponent(startDate)}&endDate=${encodeURIComponent(endDate)}`;
            window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
        }

        function printSingleRecord(recordId) {
            const printUrl = `print_single_record_simple.php?recordId=${encodeURIComponent(recordId)}`;
            window.open(printUrl, '_blank', 'width=600,height=400,scrollbars=yes,resizable=yes');
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('endDate').max = '<?php echo htmlspecialchars($currentDate); ?>';
            filterTable();
        });
    </script>
</body>
</html>