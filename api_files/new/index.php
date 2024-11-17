<?php
// Database connection details
$server = "localhost";
$username = "mashalla_Dhrubo";
$password = "Dhrubo@123";
$database = "mashalla_Dhrubo";

// Establish connection
$conn = mysqli_connect($server, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all students and their meal status
$query = "SELECT s.id, s.name, s.roll_number, s.hall_id, m.meal_date, 
          m.breakfast_taken, m.lunch_taken, m.dinner_taken 
          FROM students s 
          LEFT JOIN meals m ON s.id = m.student_id 
          AND m.meal_date = CURDATE()";
$result = mysqli_query($conn, $query);

// Update meal status on toggle request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $meal_type = $_POST['meal_type'];
    
    // Check if record exists for today
    $check_query = "SELECT * FROM meals WHERE student_id = $student_id AND meal_date = CURDATE()";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing record
        $update_query = "UPDATE meals SET $meal_type = NOT $meal_type 
                         WHERE student_id = $student_id AND meal_date = CURDATE()";
        mysqli_query($conn, $update_query);
    } else {
        // Insert new record with default meal status, set chosen meal to taken
        $insert_query = "INSERT INTO meals (student_id, meal_date, $meal_type) 
                         VALUES ($student_id, CURDATE(), 1)";
        mysqli_query($conn, $insert_query);
    }

    // Refresh the page after update
    header("Location: admin_meal_panel.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Meal Panel</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .status { cursor: pointer; font-size: 1.2em; }
        .taken { color: green; }
        .not-taken { color: red; }
    </style>
</head>
<body>

<h2>Meal Management Panel</h2>
<table>
    <tr>
        <th>Student Name</th>
        <th>Roll Number</th>
        <th>Hall ID</th>
        <th>Breakfast</th>
        <th>Lunch</th>
        <th>Dinner</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['roll_number']; ?></td>
        <td><?php echo $row['hall_id']; ?></td>

        <!-- Breakfast Status -->
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="meal_type" value="breakfast_taken">
                <button type="submit" class="status <?php echo $row['breakfast_taken'] ? 'taken' : 'not-taken'; ?>">
                    <?php echo $row['breakfast_taken'] ? '✔' : '✖'; ?>
                </button>
            </form>
        </td>

        <!-- Lunch Status -->
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="meal_type" value="lunch_taken">
                <button type="submit" class="status <?php echo $row['lunch_taken'] ? 'taken' : 'not-taken'; ?>">
                    <?php echo $row['lunch_taken'] ? '✔' : '✖'; ?>
                </button>
            </form>
        </td>

        <!-- Dinner Status -->
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="meal_type" value="dinner_taken">
                <button type="submit" class="status <?php echo $row['dinner_taken'] ? 'taken' : 'not-taken'; ?>">
                    <?php echo $row['dinner_taken'] ? '✔' : '✖'; ?>
                </button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>
