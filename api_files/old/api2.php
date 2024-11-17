<?php
// Set header to accept JSON data
header("Content-Type: application/json");

// Database connection details
$servername = "localhost";
$username = "mashalla_Dhrubo";
$password = "Dhrubo@123";
$dbname = "mashalla_Dhrubo";


// Create a MySQLi connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check if the connection was successful
if (!$conn) {
    // 500: Internal Server Error (database connection failed)
    $response = array(
        "status_code" => "500", // Internal Server Error
        "status" => "error",
        "message" => "Database connection failed: " . mysqli_connect_error()
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
    $date = isset($json_data['date']) ? mysqli_real_escape_string($conn, $json_data['date']) : null;
    $meal_type = isset($json_data['meal_type']) ? mysqli_real_escape_string($conn, $json_data['meal_type']) : null;

    // Basic validation of required fields
    if ($app_id && $name && $hall_id && $date && $meal_type) {
        // Check if the entry already exists to prevent duplicates
        $check_query = "SELECT * FROM meal_records 
                        WHERE app_id = '$app_id' 
                        AND date = '$date' 
                        AND meal_type = '$meal_type'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            // 409: Conflict (duplicate entry found)
            $response = array(
                "status_code" => "409", // Conflict
                "status" => "error",
                "message" => "Duplicate entry: Meal already recorded for this app_id, date, and meal_type"
            );
        } else {
            // Insert data into MySQL database
            $insert_query = "INSERT INTO meal_records (app_id, name, hall_id, date, meal_type) 
                             VALUES ('$app_id', '$name', '$hall_id', '$date', '$meal_type')";

            $insert_result = mysqli_query($conn, $insert_query);

            // Check if the query was successful
            if ($insert_result) {
                // 200: OK (successful insertion)
                $meal_status = "on"; // Assuming the meal status is 'on'
                $response = array(
                    "status_code" => "200", // OK
                    "status" => "success",
                    "message" => "Meal is ON",
                    "name" => $name,
                    "app_id" => $app_id,
                    "meal_status" => $meal_status
                );
            } else {
                // 500: Internal Server Error (SQL query failed)
                $response = array(
                    "status_code" => "500", // Internal Server Error
                    "status" => "error",
                    "message" => "Failed to insert data: " . mysqli_error($conn)
                );
            }
        }
    } else {
        // 400: Bad Request (missing or invalid required fields)
        $response = array(
            "status_code" => "400", // Bad Request
            "status" => "error",
            "message" => "Invalid or missing required fields"
        );
    }
} else {
    // 400: Bad Request (invalid JSON format)
    $response = array(
        "status_code" => "400", // Bad Request
        "status" => "error",
        "message" => "Invalid JSON format"
    );
}

// Send the response back as JSON
echo json_encode($response);

// Close the MySQLi connection
mysqli_close($conn);
?>
