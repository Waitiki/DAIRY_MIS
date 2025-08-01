<?php
include 'deleteUpdateRecords.php';
checkAdminAuthentication();
include 'db_connection.php';

// Get filter parameters
$filterName = isset($_POST['filterName']) ? $_POST['filterName'] : '';
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : '';

// Build the query based on filters
$query = "SELECT record_Id, farmer_Id, farmer_name, breedOfCow, quantity, rate, date_time FROM records WHERE 1";
$params = [];
$types = "";

if (!empty($filterName)) {
    $filterName = "%" . $filterName . "%";
    $query .= " AND farmer_Id LIKE ?";
    $params[] = $filterName;
    $types .= "s";
}

if (!empty($startDate)) {
    $query .= " AND DATE(date_time) >= ?";
    $params[] = $startDate;
    $types .= "s";
}

if (!empty($endDate)) {
    $query .= " AND DATE(date_time) <= ?";
    $params[] = $endDate;
    $types .= "s";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="milk_records_' . date('Y-m-d_H-i-s') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write CSV header
fputcsv($output, array(
    'Record ID',
    'Farmer ID', 
    'Farmer Name',
    'Cow Breed',
    'Quantity (kg)',
    'Rate (Ksh)',
    'Income (Ksh)',
    'Date/Time'
));

// Write data rows
$totalIncome = 0;
while ($row = $result->fetch_assoc()) {
    $income = $row['quantity'] * $row['rate'];
    $totalIncome += $income;
    
    fputcsv($output, array(
        $row['record_Id'],
        $row['farmer_Id'],
        $row['farmer_name'],
        $row['breedOfCow'],
        $row['quantity'],
        $row['rate'],
        number_format($income, 2),
        $row['date_time']
    ));
}

// Write total row
fputcsv($output, array('', '', '', '', '', 'TOTAL INCOME:', number_format($totalIncome, 2), ''));

fclose($output);
$conn->close();
?> 