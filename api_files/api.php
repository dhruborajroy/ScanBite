<?php


$server_status='on';







// Set header to accept JSON data
header("Content-Type: application/json");
date_default_timezone_set('Asia/Dhaka'); // Set timezone to Dhaka

// Database connection details
$servername = "localhost";
$username = "mashalla_Dhrubo";
$password = "Dhrubo@123";
$dbname = "mashalla_Dhrubo";

// Create a MySQLi connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check if the connection was successful
if (!$conn) {
    $response = array(
        "status_code" => "500",
        "status" => "error",
        "message" => "Database connection failed: " . mysqli_connect_error()
    );
    echo json_encode($response);
    exit();
}

// Check if the connection was successful
if ($server_status=='off') {
    $response = array(
        "status_code" => "600",
        "status" => "error",
        "message" => "Server Turned off."
    );
    echo json_encode($response);
    exit();
}

// Read the input JSON data
$data = file_get_contents("php://input");
$json_data = json_decode($data, true);

// Check if JSON decoding was successful
if (json_last_error() === JSON_ERROR_NONE) {
    // Retrieve data from the JSON input
    $app_id = isset($json_data['app_id']) ? mysqli_real_escape_string($conn, $json_data['app_id']) : null;
    $name = isset($json_data['name']) ? mysqli_real_escape_string($conn, $json_data['name']) : null;
    $hall_id = isset($json_data['hall_id']) ? mysqli_real_escape_string($conn, $json_data['hall_id']) : null;
    $date = isset($json_data['date']) ? mysqli_real_escape_string($conn, $json_data['date']) : date('Y-m-d'); // Use today's date if not provided
    $meal_type = isset($json_data['meal_type']) ? mysqli_real_escape_string($conn, $json_data['meal_type']) : null;
    $date=date('Y-n-j', strtotime($date));
    $added_on = date('Y-m-d H:i:s');
    
    // Validate required fields
    if ($app_id && $hall_id && $date && $meal_type) {
        // Get the current hour and minutes for validation
        $current_time = date("H:i");

        // Validate meal type based on time
        if (($meal_type == 'breakfast' && ($current_time < '08:00' || $current_time > '10:30')) ||
            ($meal_type == 'lunch' && ($current_time < '13:00' || $current_time > '15:30')) ||
            ($meal_type == 'dinner' && ($current_time < '19:30' || $current_time > '22:00'))) {
            $response = array(
                "status_code" => "406",
                "status" => "error",
                "message" => "Invalid time for selected meal type."
            );
            echo json_encode($response);
            exit();
        }

        // Check if the entry already exists to prevent duplicates for today
        $check_query = "SELECT * FROM meal_records 
                        WHERE hall_id = '$hall_id' 
                        AND date = '$date' 
                        AND meal_type = '$meal_type'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            // Duplicate entry found
            $response = array(
                "status_code" => "409",
                "status" => "error",
                "message" => ucwords($meal_type)." Meal already recorded for ".$name
            );
        } else {
            // Format date as day, month, year for checking in attendance table
            $day = date('j', strtotime($date));
            $month = date('n', strtotime($date));
            $year = date('Y', strtotime($date));

            // Query to check meal status in the attendance table
            $sql = "SELECT * FROM attendance 
                    WHERE roll_no = '$app_id' 
                    AND day = '$day' 
                    AND month = '$month' 
                    AND year = '$year' 
                    LIMIT 1";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                // Process based on meal_value
                if ($row['meal_value'] == '1') {
                    $insert_query = "INSERT INTO meal_records (app_id, name, hall_id, date, meal_type, added_on, status) 
                                     VALUES ('$app_id', '$name', '$hall_id', '$date', '$meal_type', '$added_on', 1)";
                    $insert_result = mysqli_query($conn, $insert_query);

                    if ($insert_result) {
                        $response = [
                            "status_code"=> "200",
                            "status" => "success",
                            "message" => "Meal is on for " . $row['name']." & Recorded.",
                            "name" => $row['name'],
                            "app_id" => $row['roll_no'],
                            "meal_status"=> "on"
                        ];
                    } else {
                        $response = array(
                            "status_code" => "500",
                            "status" => "error",
                            "message" => "Failed to insert data: " . mysqli_error($conn)
                        );
                    }
                } elseif ($row['meal_value'] == 'A') {
                    $response = [
                        "status_code"=> "204",
                        "status" => "success",
                        "message" => "Dining was off for " . $row['name'],
                        "name" => $row['name'],
                        "app_id" => $row['roll_no'],
                        "meal_status"=> "off"
                    ];
                } elseif ($row['meal_value'] == 'F') {
                    $response = [
                        "status_code"=> "215",
                        "status" => "success",
                        "message" => "Full month off for " . $row['name'],
                        "name" => $row['name'],
                        "app_id" => $row['roll_no'],
                        "meal_status"=> "off"
                    ];
                } elseif ($row['meal_value'] == '') {
                    $response = [
                        "status_code"=> "216",
                        "status" => "success",
                        "message" => $row['name'] . " has an issue with the meal record, notify the manager",
                        "name" => $row['name'],
                        "app_id" => $row['roll_no'],
                        "meal_status"=> "off"
                    ];
                } elseif ($row['meal_value'] > '1') {
                    for($i=0;$i<floor($row['meal_value']);$i++){
                        $insert_query = "INSERT INTO meal_records (app_id, name, hall_id, date, meal_type, added_on, status) 
                                         VALUES ('$app_id', '$name', '$hall_id', '$date', '$meal_type', '$added_on', 1)";
                        $insert_result = mysqli_query($conn, $insert_query);
                    }
                    $response = [
                        "status_code"=> "202",
                        "status" => "success",
                        "message" => "Meal is on with guest meal for " . $row['name'],
                        "name" => $row['name'],
                        "app_id" => $row['roll_no'],
                        "meal_status"=> "on"
                    ];
                } else {
                    $response = [            
                        "status_code"=> "201",
                        "status" => "error",
                        "message" => "Meal is off for " . $row['name'],
                        "name" => $row['name'],
                        "app_id" => $row['roll_no'],
                        "meal_status"=> "off"
                    ];
                }
            } else {
                $response = [
                    "status_code"=> "210",
                    "status" => "error",
                    "message" => "Meal may be off."
                ];
            }
        }
    } else {
        $response = array(
            "status_code" => "400",
            "status" => "error",
            "message" => "Invalid or missing required fields"
        );
    }
} else {
    $response = array(
        "status_code" => "400",
        "status" => "error",
        "message" => "Invalid JSON format"
    );
}

echo json_encode($response);
mysqli_close($conn);
?>
