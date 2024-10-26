<?php
// Database connection details
$servername = "localhost";
$username = "mashalla_Dhrubo";
$password = "Dhrubo@123";
$dbname = "mashalla_Dhrubo";

// Create a MySQLi connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to fetch all data from the meal_records table
$query = "SELECT * FROM meal_records";
$result = mysqli_query($conn, $query);

// Check if the query was successful
if ($result) {
    // Start the HTML table
    echo "<table border='1' cellpadding='10' cellspacing='0'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>App ID</th>
                    <th>Name</th>
                    <th>Hall ID</th>
                    <th>Date</th>
                    <th>Meal Type</th>
                </tr>
            </thead>
            <tbody>";

    // Check if there are records
    if (mysqli_num_rows($result) > 0) {
        // Fetch and display each row in the table
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>" . $row['id'] . "</td>
                    <td>" . $row['app_id'] . "</td>
                    <td>" . $row['name'] . "</td>
                    <td>" . $row['hall_id'] . "</td>
                    <td>" . $row['date'] . "</td>
                    <td>" . $row['meal_type'] . "</td>
                </tr>";
        }
    } else {
        // If no records are found
        echo "<tr><td colspan='6'>No records found</td></tr>";
    }

    // Close the table
    echo "</tbody></table>";
} else {
    // If the query fails
    echo "Error: " . mysqli_error($conn);
}

// Close the MySQLi connection
mysqli_close($conn);
?>
