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
$qrDir = 'qrcodes/';
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
        $qrFilePath = $qrDir . $row['hall_id'] . '_qrcode.png';

        // Generate and save QR code as a PNG image with 1000px size
        QRcode::png($jsonData, $qrFilePath, 'H', 40, 2); // Size 1000px (40 * 25 = 1000)

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student QR Code Sheet</title>
    <style>
        /* A4 Page Setup */
        @page {
            size: A4;
            margin: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #f4f4f4;
        }

        /* Page container to ensure 12 cards per page (3 columns and 4 rows) */
        .page {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 3 columns */
            gap: 15px; /* Space between cards */
            width: 100%;
            max-width: 850px;
            margin: 20px auto;
            padding: 10px;
            box-sizing: border-box;
            page-break-after: always;
        }

        /* QR card styling */
        .qr-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 10px; /* Adjust padding as needed */
            background-color: #fff;
            height: 500px; /* Adjust height for larger QR codes */
            width: 500px; /* Adjust width for larger QR codes */
        }

        /* QR code image size adjustment */
        .qr-card img {
            width: 500px; /* Set QR code size to 1000px */
            height: 500px; /* Set QR code size to 1000px */
            margin-bottom: 10px;
        }

        /* Student name and ID styling */
        .student-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            text-align: center;
        }

        .student-code {
            font-size: 14px;
            color: #555;
            text-align: center;
        }
    </style>
</head>
<body>

<?php
// Display each page with up to 12 QR cards (4 rows of 3)
$cardsPerPage = 12;
$totalStudents = count($studentsWithQR);
$currentCard = 0;

while ($currentCard < $totalStudents): ?>

    <div class="page">
        <?php //for ($i = 0; $i < $cardsPerPage && $currentCard < $totalStudents; $i++, $currentCard++): ?>
        <?php for ($i = 0; $i < $cardsPerPage && $currentCard < 25; $i++, $currentCard++): ?>
            <div class="qr-card">
                <img src="<?php echo htmlspecialchars($studentsWithQR[$currentCard]['qr_code_path']); ?>" alt="QR Code">
                <div class="student-name"><?php echo htmlspecialchars($studentsWithQR[$currentCard]['name']); ?></div>
                <div class="student-code">ID: <?php echo htmlspecialchars($studentsWithQR[$currentCard]['app_id']); ?></div>
            </div>
        <?php endfor; ?>
    </div>

<?php endwhile; ?>

</body>
</html>
