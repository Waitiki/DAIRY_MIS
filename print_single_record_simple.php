<?php
session_start();
include 'db_connection.php';

// Get record ID from URL
$recordId = isset($_GET['recordId']) ? $_GET['recordId'] : '';

if (empty($recordId)) {
    die("No record ID provided");
}

// Simple query to get record
$query = "SELECT * FROM records WHERE record_Id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $recordId);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();

if (!$record) {
    die("Record not found");
}

$income = $record['quantity'] * $record['rate'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .receipt { max-width: 400px; margin: 0 auto; border: 1px solid #ccc; padding: 20px; }
        .header { text-align: center; background: #2c3e50; color: white; padding: 20px; margin: -20px -20px 20px -20px; }
        .info { margin: 10px 0; }
        .total { font-weight: bold; font-size: 18px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>🥛 DAIRY MIS</h1>
            <p>Milk Collection Receipt</p>
        </div>
        
        <div class="info">
            <strong>Receipt #:</strong> <?php echo htmlspecialchars($record['record_Id']); ?>
        </div>
        
        <div class="info">
            <strong>Farmer ID:</strong> <?php echo htmlspecialchars($record['farmer_Id']); ?>
        </div>
        
        <div class="info">
            <strong>Farmer Name:</strong> <?php echo htmlspecialchars($record['farmer_name']); ?>
        </div>
        
        <div class="info">
            <strong>Date:</strong> <?php echo date('F j, Y', strtotime($record['date_time'])); ?>
        </div>
        
        <div class="info">
            <strong>Time:</strong> <?php echo date('g:i A', strtotime($record['date_time'])); ?>
        </div>
        
        <div class="info">
            <strong>Quantity:</strong> <?php echo number_format($record['quantity'], 2); ?> kg
        </div>
        
        <div class="info">
            <strong>Rate:</strong> Ksh <?php echo number_format($record['rate'], 2); ?>
        </div>
        
        <div class="total">
            <strong>Total Amount:</strong> Ksh <?php echo number_format($income, 2); ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <button onclick="window.print()">Print Receipt</button>
            <button onclick="window.close()">Close</button>
        </div>
    </div>
</body>
</html> 