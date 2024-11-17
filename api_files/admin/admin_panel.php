<?php
include('config.php');

// Get the selected month and year from the form or set default
$month = $_POST['month'] ?? date('m');
$year = $_POST['year'] ?? date('Y');

// Fetch filtered data based on selected month and year
$query = "SELECT id, roll_no, name, meal_value, day FROM attendance WHERE month = '$month' AND year = '$year'";
$result = mysqli_query($conn, $query);
?>

<html>
<body>
    <h2>Admin Panel - Simplified Attendance Management</h2>
    
    <!-- Filter form -->
    <form method="POST" action="simple_admin_panel.php">
        <label>Select Month:</label>
        <select name="month">
            <?php for ($m=1; $m<=12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo ($m == $month) ? 'selected' : ''; ?>>
                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                </option>
            <?php endfor; ?>
        </select>
        
        <label>Select Year:</label>
        <input type="number" name="year" value="<?php echo $year; ?>">
        <button type="submit">Filter</button>
    </form>
    
    <!-- Display the data -->
    <table border="1">
        <tr>
            <th>Roll No</th>
            <th>Name</th>
            <th>Meal Value</th>
            <th>Day</th>
            <th>Actions</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['roll_no']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['meal_value']; ?></td>
            <td><?php echo $row['day']; ?></td>
            <td>
                <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                <a href="delete.php?id=<?php echo $row['id']; ?>">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
