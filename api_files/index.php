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
        h1, h2 {
            text-align: center;
            color: #343a40;
        }
        .dashboard {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            width: 200px;
            margin: 10px;
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
        .no-records {
            text-align: center;
            color: #dc3545;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>Meal Records Dashboard</h1>
    
    <div class="dashboard">
        <div class="card">
            <h3>Total Meals On</h3>
            <p id="totalMeals">0</p>
        </div>
        <div class="card">
            <h3>Breakfast</h3>
            <p id="breakfastCount">0</p>
        </div>
        <div class="card">
            <h3>Lunch</h3>
            <p id="lunchCount">0</p>
        </div>
        <div class="card">
            <h3>Dinner</h3>
            <p id="dinnerCount">0</p>
        </div>
        <div class="card">
            <h3>Total Students</h3>
            <p id="totalStudents">0</p>
        </div>
    </div>

    <?php
    date_default_timezone_set('Asia/Dhaka');

    // Database connection details
    $servername = "localhost";
    $username = "mashalla_Dhrubo";
    $password = "Dhrubo@123";
    $dbname = "mashalla_Dhrubo";
    
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $date= date('d'); 
    $month=date('m'); 
    $year=date('Y');

    // Fetch count values for dashboard
    $totalMealsQuery = "SELECT sum(meal_value) AS total_meals FROM attendance WHERE meal_value >= 1 AND day = '$date' and month='$month' and year='$year'";
    $totalStudentsQuery = "SELECT COUNT(DISTINCT roll_no) AS total_students FROM attendance";
    $breakfastQuery = "SELECT COUNT(*) AS breakfast_count FROM meal_records WHERE meal_type = 'breakfast'";
    $lunchQuery = "SELECT COUNT(*) AS lunch_count FROM meal_records WHERE meal_type = 'lunch'";
    $dinnerQuery = "SELECT COUNT(*) AS dinner_count FROM meal_records WHERE meal_type = 'dinner'";
    
    $totalMeals = mysqli_fetch_assoc(mysqli_query($conn, $totalMealsQuery))['total_meals'];
    $totalStudents = mysqli_fetch_assoc(mysqli_query($conn, $totalStudentsQuery))['total_students'];
    $breakfastCount = mysqli_fetch_assoc(mysqli_query($conn, $breakfastQuery))['breakfast_count'];
    $lunchCount = mysqli_fetch_assoc(mysqli_query($conn, $lunchQuery))['lunch_count'];
    $dinnerCount = mysqli_fetch_assoc(mysqli_query($conn, $dinnerQuery))['dinner_count'];
    
    echo "<script>
            document.getElementById('totalMeals').textContent = '$totalMeals';
            document.getElementById('breakfastCount').textContent = '$breakfastCount';
            document.getElementById('lunchCount').textContent = '$lunchCount';
            document.getElementById('dinnerCount').textContent = '$dinnerCount';
            document.getElementById('totalStudents').textContent = '$totalStudents';
          </script>";

    // Function to generate table for each meal type
    function generateTable($conn, $mealType) {
        echo "<h2>" . ucfirst($mealType) . " Records</h2>";
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
        
        $query = "SELECT * FROM meal_records WHERE meal_type = '$mealType'";
        $result = mysqli_query($conn, $query);
        
        // Check if there are any records
        if ($result && mysqli_num_rows($result) > 0) {
            // Fetch and display each row in the table
            while ($row = mysqli_fetch_assoc($result)) {
                $timestamp = $row['added_on'];
                $formattedTime = !empty($timestamp) ? date('g:i A d-M-Y', strtotime($timestamp)) : "Time not available";

                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['app_id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['hall_id']}</td>
                        <td>{$row['date']}</td>
                        <td>" . ucwords($row['meal_type']) . "</td>
                        <td>$formattedTime</td>
                      </tr>";
            }
        } else {
            // Display a message in an empty row if no records are found
            echo "<tr><td colspan='7' class='no-records'>No $mealType records found</td></tr>";
        }
        
        echo "</tbody></table>";
    }
    
    // Generate tables for breakfast, lunch, and dinner
    generateTable($conn, 'breakfast');
    generateTable($conn, 'lunch');
    generateTable($conn, 'dinner');
    
    mysqli_close($conn);
    ?>
</body>
</html>
