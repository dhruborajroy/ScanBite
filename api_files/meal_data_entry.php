<?php
$ptime=time();
// Database connection settings
$servername = "localhost";
$username = "mashalla_Dhrubo";
$password = "Dhrubo@123";
$dbname = "mashalla_Dhrubo";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Load JSON data from file
$jsonData = file_get_contents("meal_data.json");
$data = json_decode($jsonData, true); // Convert JSON data to associative array

// Set the month and year for these records
$month = date("n"); // Example: October
$year = date("Y"); // Example: 2024

// Loop through each student in the data
foreach ($data as $student) {
    $rollNo = $student["ROLL"];
    $regNo = $student["REG"];
    $name = $student["NAME"];
    if($rollNo==0 || $rollNo==""){
        
    }else{
        // Loop through each day's value for the current student
        for ($day = 1; $day <= 31; $day++) {
            if (isset($student[(string)$day])) {
                if($student[(string)2]=='F' || $student[(string)3]=='U'){
                    if (!is_numeric($student[(string)2]) || $student[(string)3]) {
                        $mealValue = $student[(string)$day];
                        // Query to check if there's an existing meal value in the database
                        $checkQuery = "SELECT meal_value FROM attendance 
                                       WHERE roll_no = '$rollNo' AND day = '$day' 
                                       AND month = '$month' AND year = '$year' 
                                       LIMIT 1";
                        $result = mysqli_query($conn, $checkQuery);
                        
                        // If a record exists, fetch it
                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $existingMealValue = $row['meal_value'];
    
                            // Check if the existing meal value matches the current day's value
                            if ($existingMealValue == $mealValue) {
                                // If they match, skip insertion
                                continue;
                            }
                        }else{
                            // Prepare the insert query
                            $query = "INSERT INTO attendance (roll_no, reg_no, name, meal_value, day, month, year) 
                                  VALUES ('$rollNo', '$regNo', '$name', 'F', '$day', '$month', '$year')";
                            // print_r($student[(string)2]);
                            mysqli_query($conn, $query);
                        }
                    } else {
                        echo "error";
                    }
                    
                }else{
                    $mealValue = $student[(string)$day];
                    // Query to check if there's an existing meal value in the database
                    $checkQuery = "SELECT meal_value FROM attendance 
                                   WHERE roll_no = '$rollNo' AND day = '$day' 
                                   AND month = '$month' AND year = '$year' 
                                   LIMIT 1";
                    $result = mysqli_query($conn, $checkQuery);
                    
                    // If a record exists, fetch it
                    if ($result && mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $existingMealValue = $row['meal_value'];
                        
                        // Check if the existing meal value matches the current day's value
                        if ($existingMealValue == $mealValue) {
                            // If they match, skip insertion
                            continue;
                        }
                    }else{
                        
                        // Skip if meal value is empty
                        if ($mealValue === "") {
                            continue;
                        }
            
                        // Prepare the insert query
                        $query = "INSERT INTO attendance (roll_no, reg_no, name, meal_value, day, month, year) 
                                  VALUES ('$rollNo', '$regNo', '$name', '$mealValue', '$day', '$month', '$year')";
                                  
                        // Execute the query
                        if (!mysqli_query($conn, $query)) {
                            echo "Error: " . mysqli_error($conn);
                        }
            
                    }
                }
            }
        }
    }
    }

// Close the connection
mysqli_close($conn);
echo "<h1>Data inserted successfully for ".$month." ".$year;  
echo "<br> Took ".time()-$ptime." Seconds</h1>";
?>
