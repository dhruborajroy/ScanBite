<?php
// Database connection details
$servername = "localhost";
$username = "mashalla_Dhrubo";
$password = "Dhrubo@123";
$dbname = "mashalla_Dhrubo";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch student data
$sql = "SELECT DISTINCT roll_no AS app_id, name, roll_no AS hall_id, reg_no FROM attendance";
$result = mysqli_query($conn, $sql);

// Include the phpqrcode library
include('phpqrcode/qrlib.php');

// Directory to save QR codes
$qrDir = '/qrcodes/';
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0777, true); // Create directory if it doesn't exist
}

// Array to store student data with QR code paths
$studentsWithQR = [];

// Check if records were found
if (mysqli_num_rows($result) > 0) {
    // Loop through each student record
    while ($row = mysqli_fetch_assoc($result)) {
        // Encode each student's data as JSON
        $jsonData = json_encode($row);

        // Define a unique file path for each QR code based on student ID
        $qrFilePath = $qrDir . $row['app_id'] . '_qrcode.png';

        // Check if QR code already exists
        if (!file_exists($qrFilePath)) {
            // Generate and save QR code as a PNG image with 1000px size
            QRcode::png($jsonData, $qrFilePath, 'H', 40, 2); // Size 1000px (40 * 25 = 1000)
        }

        // Add student's data with QR code path to the array
        $row['qr_code_path'] = $qrFilePath;
        $studentsWithQR[] = $row;
    }
} else {
    echo "No records found.";
    exit;
}

// Close the database connection
$conn->close();
?>
