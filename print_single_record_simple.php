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
    <title>Milk Collection Receipt</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .watermark { opacity: 0.1 !important; }
            .receipt-container { 
                max-width: 148mm; 
                margin: 0 auto; 
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }
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
            line-height: 1.3;
        }

        .receipt-container {
            max-width: 148mm; /* A5 width */
            min-height: 210mm; /* A5 height */
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 4rem;
            color: rgba(0,0,0,0.03);
            pointer-events: none;
            z-index: -1;
            font-weight: bold;
        }

        .receipt-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 15px;
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
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .company-subtitle {
            font-size: 0.8rem;
            opacity: 0.9;
            margin-bottom: 3px;
        }

        .receipt-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .receipt-body {
            padding: 15px;
        }

        .receipt-number {
            background: #ecf0f1;
            padding: 8px;
            border-radius: 4px;
            margin: 10px 0;
            text-align: center;
            font-weight: bold;
            color: #2c3e50;
            font-size: 0.8rem;
        }

        .receipt-info {
            margin-bottom: 15px;
        }

        .info-section {
            margin-bottom: 12px;
        }

        .info-section h3 {
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #3498db;
            padding-bottom: 3px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            padding: 2px 0;
            border-bottom: 1px dotted #e0e0e0;
            font-size: 0.8rem;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            min-width: 80px;
        }

        .info-value {
            color: #2c3e50;
            text-align: right;
        }

        .transaction-details {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin: 12px 0;
            border-left: 3px solid #27ae60;
        }

        .transaction-details h3 {
            color: #27ae60;
            font-size: 0.9rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .amount-section {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 12px;
            text-align: center;
            margin: 12px 0;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(39, 174, 96, 0.3);
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .amount-label {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .amount-value {
            font-size: 1rem;
            font-weight: bold;
        }

        .total-amount {
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 8px;
            margin-top: 8px;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .receipt-footer {
            background: #2c3e50;
            color: white;
            padding: 12px;
            text-align: center;
        }

        .footer-content {
            margin-bottom: 12px;
        }

        .footer-section h4 {
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.7rem;
        }

        .footer-section p {
            font-size: 0.65rem;
            opacity: 0.9;
            line-height: 1.3;
        }

        .signature-section {
            margin-top: 12px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .signature-box {
            text-align: center;
            padding: 8px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }

        .signature-line {
            width: 100px;
            height: 1px;
            background: rgba(255,255,255,0.5);
            margin: 6px auto;
        }

        .print-actions {
            background: white;
            padding: 10px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }

        .btn {
            padding: 8px 15px;
            margin: 0 3px;
            border: none;
            border-radius: 4px;
            font-size: 0.8rem;
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
            transform: translateY(-1px);
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-1px);
        }

        .thank-you {
            text-align: center;
            margin: 12px 0;
            font-size: 0.9rem;
            color: #27ae60;
            font-weight: bold;
        }

        .security-features {
            margin-top: 8px;
            font-size: 0.6rem;
            opacity: 0.8;
            text-align: center;
        }

        @media (max-width: 768px) {
            .receipt-container {
                margin: 10px;
                border-radius: 0;
                max-width: 100%;
            }
            
            .signature-section {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .receipt-body {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="watermark">DAIRY MIS</div>
    
    <div class="receipt-container">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <div class="company-logo">🥛 DAIRY MIS</div>
            <div class="company-subtitle">Milk Collection Management System</div>
            <div class="receipt-title">Milk Collection Receipt</div>
        </div>

        <!-- Receipt Body -->
        <div class="receipt-body">
            <div class="receipt-number">
                Receipt #: <?php echo htmlspecialchars($record['record_Id']); ?>
            </div>

            <div class="receipt-info">
                <div class="info-section">
                    <h3>👤 Farmer Information</h3>
                    <div class="info-row">
                        <span class="info-label">Farmer ID:</span>
                        <span class="info-value"><?php echo htmlspecialchars($record['farmer_Id']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($record['farmer_name']); ?></span>
                    </div>
                </div>

                <div class="info-section">
                    <h3>📅 Transaction Details</h3>
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span class="info-value"><?php echo date('F j, Y', strtotime($record['date_time'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Time:</span>
                        <span class="info-value"><?php echo date('g:i A', strtotime($record['date_time'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cow Breed:</span>
                        <span class="info-value"><?php echo htmlspecialchars($record['breedOfCow']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Transaction Details -->
            <div class="transaction-details">
                <h3>🥛 Milk Collection Details</h3>
                <div class="info-row">
                    <span class="info-label">Quantity:</span>
                    <span class="info-value"><?php echo number_format($record['quantity'], 2); ?> kg</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Rate per kg:</span>
                    <span class="info-value">Ksh <?php echo number_format($record['rate'], 2); ?></span>
                </div>
            </div>

            <!-- Amount Section -->
            <div class="amount-section">
                <div class="amount-row">
                    <span class="amount-label">Quantity:</span>
                    <span class="amount-value"><?php echo number_format($record['quantity'], 2); ?> kg</span>
                </div>
                <div class="amount-row">
                    <span class="amount-label">Rate:</span>
                    <span class="amount-value">Ksh <?php echo number_format($record['rate'], 2); ?></span>
                </div>
                <div class="amount-row total-amount">
                    <span class="amount-label">Total Amount:</span>
                    <span class="amount-value">Ksh <?php echo number_format($income, 2); ?></span>
                </div>
            </div>

            <div class="thank-you">
                Thank you for your business! 🥛
            </div>
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
            </div>

            <div class="signature-section">
                <div class="signature-box">
                    <div>_________________________</div>
                    <div style="margin-top: 8px; font-size: 0.8rem;">Farmer's Signature</div>
                </div>
                <div class="signature-box">
                    <div>_________________________</div>
                    <div style="margin-top: 8px; font-size: 0.8rem;">Authorized Signature</div>
                </div>
            </div>

            <div class="security-features">
                <p>This is a computer-generated receipt. No physical signature is required.</p>
                <p>Receipt generated on: <?php echo date('F j, Y \a\t g:i:s A'); ?></p>
                <p>Security Code: <?php echo strtoupper(substr(md5($record['record_Id'] . date('Y-m-d')), 0, 8)); ?></p>
            </div>
        </div>
    </div>

    <!-- Print Actions -->
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Receipt</button>
        <button onclick="window.close()" class="btn btn-secondary">❌ Close</button>
        <a href="records.php" class="btn btn-success">📋 Back to Records</a>
    </div>

    <script>
        // Auto-print when page loads (optional)
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