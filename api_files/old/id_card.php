<?php
// Load JSON data from file
$jsonFile = 'students.json';
$jsonData = file_get_contents($jsonFile);

// Decode JSON data
$students = json_decode($jsonData, true);
if ($students === null) {
    die("Error loading JSON data.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student ID Card Sheet</title>
    <style>
        /* A4 Page Setup */
        @page {
            size: A4;
            margin: 0;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #f4f4f4;
        }

        /* Main container for ID cards */
        .card-sheet {
            display: grid;
            grid-template-columns: repeat(2, 48%);
            gap: 20px;
            width: 100%;
            padding: 20px;
        }

        /* Individual ID card styling */
        .card {
            background-color: #fff;
            border: 2px solid #007BFF;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            width: 100%;
            height: 320px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        /* Front Side */
        .front {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ddd;
        }

        .front img {
            width: 70px;
            height: 90px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #007BFF;
            margin-bottom: 8px;
        }

        .student-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .student-id {
            font-size: 14px;
            color: #555;
            margin-top: 4px;
            background: #007BFF;
            color: #fff;
            padding: 2px 8px;
            border-radius: 12px;
        }

        /* Back Side */
        .back {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-top: 10px;
        }

        .back img {
            width: 80px;
            height: 80px;
            margin-bottom: 8px;
        }

        .qr-info {
            font-size: 12px;
            color: #007BFF;
        }
    </style>
</head>
<body>

<div class="card-sheet">
    <?php foreach ($students as $student): ?>
        <div class="card">
            <!-- Front of the card -->
            <div class="front">
                <img src="<?php echo htmlspecialchars($student['photo_url']); ?>" alt="Student Photo">
                <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
                <div class="student-id">ID: <?php echo htmlspecialchars($student['id']); ?></div>
            </div>
            <!-- Back of the card -->
            <div class="back">
                <img src="<?php echo htmlspecialchars($student['qr_code_url']); ?>" alt="QR Code">
                <div class="qr-info">Scan for More Info</div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
