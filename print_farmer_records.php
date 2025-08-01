<?php
include 'deleteUpdateRecords.php';
checkAdminAuthentication();
include 'db_connection.php';

// Get parameters from URL
$farmerId = isset($_GET['farmerId']) ? $_GET['farmerId'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

// Validate parameters
if (empty($farmerId) || empty($startDate) || empty($endDate)) {
    die("Invalid parameters. Please provide farmer ID, start date, and end date.");
}

// Get farmer details
$farmerQuery = "SELECT id, name, phone, address FROM farmers WHERE id = ?";
$stmt = $conn->prepare($farmerQuery);
$stmt->bind_param("s", $farmerId);
$stmt->execute();
$farmerResult = $stmt->get_result();
$farmer = $farmerResult->fetch_assoc();

if (!$farmer) {
    die("Farmer not found.");
}

// Get farmer's records for the specified date range
$recordsQuery = "SELECT record_Id, quantity, rate, date_time FROM records 
                 WHERE farmer_Id = ? AND DATE(date_time) BETWEEN ? AND ? 
                 ORDER BY date_time ASC";
$stmt = $conn->prepare($recordsQuery);
$stmt->bind_param("sss", $farmerId, $startDate, $endDate);
$stmt->execute();
$recordsResult = $stmt->get_result();

$records = [];
$totalQuantity = 0;
$totalIncome = 0;

while ($row = $recordsResult->fetch_assoc()) {
    $records[] = $row;
    $totalQuantity += $row['quantity'];
    $totalIncome += $row['quantity'] * $row['rate'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Records - Print</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-page { page-break-after: always; }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.4;
        }

        .print-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .receipt-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .receipt-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .company-logo {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .company-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .receipt-info {
            padding: 30px;
            background: #fafafa;
            border-bottom: 2px solid #e0e0e0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 20px;
        }

        .info-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .info-section h3 {
            color: #2c3e50;
            font-size: 1.1rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px dotted #e0e0e0;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            min-width: 120px;
        }

        .info-value {
            color: #2c3e50;
            text-align: right;
        }

        .records-section {
            padding: 30px;
        }

        .records-header {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .records-table th {
            background: #34495e;
            color: white;
            padding: 15px 10px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .records-table td {
            padding: 12px 10px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }

        .records-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .records-table tr:hover {
            background-color: #e8f4fd;
        }

        .total-section {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 25px;
            text-align: center;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .total-label {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .total-value {
            font-size: 1.3rem;
            font-weight: bold;
        }

        .grand-total {
            border-top: 2px solid rgba(255,255,255,0.3);
            padding-top: 15px;
            margin-top: 15px;
            font-size: 1.4rem;
            font-weight: bold;
        }

        .receipt-footer {
            background: #2c3e50;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .footer-section h4 {
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .footer-section p {
            font-size: 0.85rem;
            opacity: 0.9;
            line-height: 1.5;
        }

        .signature-section {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .signature-box {
            text-align: center;
            padding: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        .signature-line {
            width: 200px;
            height: 2px;
            background: rgba(255,255,255,0.5);
            margin: 15px auto;
        }

        .print-actions {
            background: white;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }

        .btn {
            padding: 12px 25px;
            margin: 0 10px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }

        .date-range {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            color: #2c3e50;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 8rem;
            color: rgba(0,0,0,0.03);
            pointer-events: none;
            z-index: -1;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .print-container {
                margin: 10px;
                border-radius: 0;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .signature-section {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .records-table {
                font-size: 0.8rem;
            }
            
            .records-table th,
            .records-table td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="watermark">DAIRY MIS</div>
    
    <div class="print-container">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <div class="company-logo">🥛 DAIRY MIS</div>
            <div class="company-subtitle">Milk Collection Management System</div>
            <div class="receipt-title">Farmer Records Report</div>
        </div>

        <!-- Receipt Information -->
        <div class="receipt-info">
            <div class="info-grid">
                <div class="info-section">
                    <h3>📋 Farmer Information</h3>
                    <div class="info-row">
                        <span class="info-label">Farmer ID:</span>
                        <span class="info-value"><?php echo htmlspecialchars($farmer['id']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($farmer['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($farmer['phone']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span class="info-value"><?php echo htmlspecialchars($farmer['address']); ?></span>
                    </div>
                </div>

                <div class="info-section">
                    <h3>📅 Report Details</h3>
                    <div class="info-row">
                        <span class="info-label">Report Date:</span>
                        <span class="info-value"><?php echo date('F j, Y'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Generated By:</span>
                        <span class="info-value">Admin</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Report ID:</span>
                        <span class="info-value"><?php echo 'RPT-' . date('Ymd') . '-' . rand(1000, 9999); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value">✅ Verified</span>
                    </div>
                </div>
            </div>

            <div class="date-range">
                📊 Records from <strong><?php echo date('F j, Y', strtotime($startDate)); ?></strong> 
                to <strong><?php echo date('F j, Y', strtotime($endDate)); ?></strong>
            </div>
        </div>

        <!-- Records Section -->
        <div class="records-section">
            <div class="records-header">
                📋 Milk Collection Records
            </div>
            
            <?php if (!empty($records)): ?>
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Record ID</th>
                            <th>Date & Time</th>
                            <th>Quantity (kg)</th>
                            <th>Rate (Ksh)</th>
                            <th>Income (Ksh)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($records as $record): 
                            $income = $record['quantity'] * $record['rate'];
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($record['record_Id']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($record['date_time'])); ?></td>
                            <td><?php echo number_format($record['quantity'], 2); ?></td>
                            <td><?php echo number_format($record['rate'], 2); ?></td>
                            <td><?php echo number_format($income, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Total Section -->
                <div class="total-section">
                    <div class="total-row">
                        <span class="total-label">Total Records:</span>
                        <span class="total-value"><?php echo count($records); ?></span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Total Quantity:</span>
                        <span class="total-value"><?php echo number_format($totalQuantity, 2); ?> kg</span>
                    </div>
                    <div class="total-row grand-total">
                        <span class="total-label">Total Income:</span>
                        <span class="total-value">Ksh <?php echo number_format($totalIncome, 2); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #6c757d; margin-bottom: 10px;">📭 No Records Found</h3>
                    <p style="color: #6c757d;">No milk collection records found for the specified date range.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Receipt Footer -->
        <div class="receipt-footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>📞 Contact Information</h4>
                    <p>Dairy MIS System<br>
                    Email: info@dairymis.com<br>
                    Phone: +254 700 000 000</p>
                </div>
                <div class="footer-section">
                    <h4>📍 Location</h4>
                    <p>Dairy Management Office<br>
                    P.O. Box 12345<br>
                    Nairobi, Kenya</p>
                </div>
                <div class="footer-section">
                    <h4>💼 Business Hours</h4>
                    <p>Monday - Friday: 8:00 AM - 6:00 PM<br>
                    Saturday: 8:00 AM - 2:00 PM<br>
                    Sunday: Closed</p>
                </div>
            </div>

            <div class="signature-section">
                <div class="signature-box">
                    <div>_________________________</div>
                    <div style="margin-top: 10px;">Farmer's Signature</div>
                </div>
                <div class="signature-box">
                    <div>_________________________</div>
                    <div style="margin-top: 10px;">Authorized Signature</div>
                </div>
            </div>

            <div style="margin-top: 20px; font-size: 0.8rem; opacity: 0.8;">
                <p>This is a computer-generated report. No physical signature is required.</p>
                <p>Report generated on: <?php echo date('F j, Y \a\t g:i:s A'); ?></p>
            </div>
        </div>
    </div>

    <!-- Print Actions -->
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
        <button onclick="window.close()" class="btn btn-secondary">❌ Close</button>
        <a href="records.php" class="btn btn-success">📋 Back to Records</a>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Uncomment the line below to auto-print
            // window.print();
        };

        // Handle print events
        window.addEventListener('beforeprint', function() {
            document.querySelector('.print-actions').style.display = 'none';
        });

        window.addEventListener('afterprint', function() {
            document.querySelector('.print-actions').style.display = 'block';
        });
    </script>
</body>
</html> 