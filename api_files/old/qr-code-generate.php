<?php

// Database connection details
$servername = "localhost";
$username = "mashalla_Dhrubo";
$password = "Dhrubo@123";
$dbname = "mashalla_Dhrubo";

// Connect to the database
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch roll number from the GET request or use a default value
$roll = isset($_GET['roll_no']) ? $_GET['roll_no'] : 200130;

// Query to select the specific student's data
$sql = "SELECT DISTINCT roll_no AS app_id, name, roll_no AS hall_id, reg_no FROM attendance WHERE roll_no='$roll'";
$result = mysqli_query($conn, $sql);

$dataArray = [];

// Fetch data and store it in an array
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $dataArray[] = $row;
    }
} else {
    echo "No records found.";
    exit;
}

// Encode the data array as JSON
$jsonData = json_encode($dataArray[0]);

// Include the phpqrcode library
include('phpqrcode/qrlib.php');

// Define the file path for saving the QR code
$qrFilePath = 'qrcode.png';

// Generate the QR code and save it as a high-quality PNG image
QRcode::png($jsonData, $qrFilePath, 'H', 4, 2);

?>

<!-- <img src="qrcode.png" alt="QR Code" width="500px"> -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f9;
        }
        .card {
            width: 350px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            text-align: center;
        }
        .photo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .qr-code img {
            width: 500px;
            height: 500px;
            margin-top: 15px;
        }
        .info h2 {
            margin: 10px 0 5px;
            font-size: 20px;
            color: #333;
        }
        .info p {
            margin: 5px 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="photo">
            <img src="placeholder-photo.jpg" alt="Passport Size Photo">
        </div>
        <div class="info">
            <h2><p><?php echo htmlspecialchars($dataArray[0]['name']); ?></p></h2>
            <p><strong>Roll No:</strong> <p><?php echo htmlspecialchars($dataArray[0]['hall_id']); ?></p></p>
            <p><strong>Registration No:</strong> <p><?php echo htmlspecialchars($dataArray[0]['reg_no']); ?></p></p>
            <p><strong>Phone:</strong> +8801234567890</p>
            <p><strong>Department:</strong> Civil Engineering</p>
        </div>
        <div class="qr-code">
            <img src="qrcode.png" alt="QR Code">
        </div>
    </div>
</body>
</html>
