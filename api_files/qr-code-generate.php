<?php

// Database connection details
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



// Query to select all records from the attendance table
if(isset($_GET['roll_no'])) {
    $roll=$_GET['roll_no'];
}else{
    $roll=200130;
}

 $sql = "SELECT DISTINCT roll_no AS app_id , name,roll_no AS hall_id,reg_no  FROM attendance where roll_no='$roll'";
$result = mysqli_query($conn, $sql);

$dataArray = [];

// Fetch data and store it in an array
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $dataArray[] = $row; // Add each row to the data array
    }
} else {
    echo "No records found.";
}
// print_r($dataArray);
// Encode the data array as JSON
$jsonData = json_encode($dataArray);
$json_arr=json_decode($jsonData,1);
$jsonData=json_encode($json_arr[0]);
// Include the phpqrcode library
include('phpqrcode/qrlib.php');
// Define the file path for saving the QR code
$qrFilePath = 'qrcode.png';
// QR code error correction level
$errorCorrectionLevel = 'H'; 
// QR code matrix point size (controls resolution)
$matrixPointSize = 10; // Higher value = higher resolution (e.g., 10 for high quality)

// Generate the QR code and save it as a high-quality PNG image
echo QRcode::png($jsonData, $qrFilePath, $errorCorrectionLevel, $matrixPointSize, 2);
?>
<img src="qrcode.png" alt="" srcset="" width="500px">