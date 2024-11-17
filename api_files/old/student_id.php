<?php
// Database connection
$servername = "localhost";
$username = "mashalla_Dhrubo";
$password = "Dhrubo@123";
$dbname = "mashalla_Dhrubo";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch roll numbers from the database
$rollNumbers = [];
$sql = "SELECT roll_number FROM students";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rollNumbers[] = $row['roll_number'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student QR Code Generator</title>
    <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/umd/qrious.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <style>
        #qr-container { 
            width: 300px; 
            padding: 10px; 
            border: 1px solid #ddd; 
            text-align: center; 
            margin: auto; 
        }
        #qr-code { 
            width: 100%; 
            height: auto; 
        }
        .action-buttons {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<h2 style="text-align: center;">Student ID QR Code Generator</h2>

<div style="text-align: center;">
    <label for="roll-number">Select Roll Number:</label>
    <select id="roll-number" onchange="generateQRCode()">
        <option value="">Select Roll Number</option>
        <?php foreach ($rollNumbers as $rollNumber): ?>
            <option value="<?php echo htmlspecialchars($rollNumber); ?>"><?php echo htmlspecialchars($rollNumber); ?></option>
        <?php endforeach; ?>
    </select>
    <br><br>
    <label for="roll-input">Or Enter Roll Number:</label>
    <input type="text" id="roll-input" oninput="generateQRCode()" placeholder="Enter Roll Number">
</div>

<div id="qr-container">
    <canvas id="qr-code"></canvas>
</div>

<div class="action-buttons" style="text-align: center;">
    <button onclick="printQRCode()">Print ID Card</button>
    <button onclick="saveImage()">Save as Image</button>
    <button onclick="savePDF()">Save as PDF</button>
</div>

<script>
    // Initialize QRious for QR code generation
    const qr = new QRious({
        element: document.getElementById('qr-code'),
        size: 200
    });

    function generateQRCode() {
        const selectRoll = document.getElementById('roll-number').value;
        const inputRoll = document.getElementById('roll-input').value;
        const rollNumber = selectRoll || inputRoll; // Use either selected or entered roll number
        if (rollNumber) {
            qr.value = rollNumber;  // Set the QR code content to the roll number
        }
    }

    function printQRCode() {
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Print QR Code</title></head><body>');
        printWindow.document.write('<div style="text-align: center;">' + document.getElementById('qr-container').outerHTML + '</div>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }

    function saveImage() {
        html2canvas(document.getElementById('qr-container')).then(canvas => {
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = 'qrcodes/'+rollNumber+'_qrcode.png';
            link.click();
        });
    }

    function savePDF() {
        html2canvas(document.getElementById('qr-container')).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF();
            pdf.addImage(imgData, 'PNG', 15, 40, 180, 160);
            pdf.save('Student_ID_QR.pdf');
        });
    }
</script>

</body>
</html>
