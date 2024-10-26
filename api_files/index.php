<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Records Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #343a40;
        }

        .dashboard {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            width: 200px;
        }

        .card h3 {
            margin: 0;
            color: #007bff;
        }

        .card p {
            font-size: 24px;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background-color: #007bff;
            color: #ffffff;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .download-link {
            display: block;
            margin: 20px auto;
            text-align: center;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            width: 200px;
        }

        .download-link:hover {
            background-color: #218838;
        }

        .no-records {
            text-align: center;
            padding: 20px;
            font-size: 18px;
            color: #dc3545;
        }
    </style>
</head>
<body>

    <h1>Meal Records Dashboard</h1>


    <a href="https://mashallah.shop/app/scan_bite.apk" class="download-link" target="_blank">Download ScanBite App</a>

    <div class="dashboard">
        <div class="card">
            <h3>Total Meals</h3>
            <p id="totalMeals">0</p>
        </div>
        <div class="card">
            <h3>Total Students</h3>
            <p id="totalStudents">0</p>
        </div>
    </div>

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

    // Query to count total meals and total students
    $totalMealsQuery = "SELECT COUNT(*) AS total_meals FROM meal_records";
    $totalStudentsQuery = "SELECT COUNT(DISTINCT app_id) AS total_students FROM meal_records";

    // Execute the total meals query
    $totalMealsResult = mysqli_query($conn, $totalMealsQuery);
    $totalMealsRow = mysqli_fetch_assoc($totalMealsResult);
    $totalMeals = $totalMealsRow['total_meals'];

    // Execute the total students query
    $totalStudentsResult = mysqli_query($conn, $totalStudentsQuery);
    $totalStudentsRow = mysqli_fetch_assoc($totalStudentsResult);
    $totalStudents = $totalStudentsRow['total_students'];

    // Display the counts in the respective cards
    echo "<script>
            document.getElementById('totalMeals').textContent = '$totalMeals';
            document.getElementById('totalStudents').textContent = '$totalStudents';
          </script>";

    // Query to fetch all data from the meal_records table
    $query = "SELECT * FROM meal_records";
    $result = mysqli_query($conn, $query);

    // Check if the query was successful
    if ($result) {
        // Check if there are records
        if (mysqli_num_rows($result) > 0) {
            // Start the HTML table
            echo "<table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>App ID</th>
                            <th>Name</th>
                            <th>Hall ID</th>
                            <th>Date</th>
                            <th>Meal Type</th>
                            <th>Added On</th>
                        </tr>
                    </thead>
                    <tbody>";

            // Fetch and display each row in the table
            while ($row = mysqli_fetch_assoc($result)) {
                $timestamp=$row['added_on'];
                echo "<tr>
                    <td>" . $row['id'] . "</td>
                    <td>" . $row['app_id'] . "</td>
                    <td>" . $row['name'] . "</td>
                    <td>" . $row['hall_id'] . "</td>
                    <td>" . $row['date'] . "</td>
                    <td>" . $row['meal_type'] . "</td>
                    <td>" . (!empty($timestamp) ? date("h:i A m-d-Y", $timestamp) : "Time not available") . "</td>
                  </tr>";

            }

            // Close the table
            echo "</tbody></table>";
        } else {
            // If no records are found
            echo "<div class='no-records'>No records found</div>";
        }
    } else {
        // If the query fails
        echo "<div class='no-records'>Error: " . mysqli_error($conn) . "</div>";
    }

    // Close the MySQLi connection
    mysqli_close($conn);
    ?>
</body>
</html>
