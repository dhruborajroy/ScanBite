<?php

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

                "message" => ucwords($meal_type)." Meal already recorded for ".$name

            );

        }else {

            // Convert date to day, month, year

            $dateParts = date_parse($date);

            $day = $dateParts['day'];

            $month = $dateParts['month'];

            $year = $dateParts['year'];

        

            // Query to check meal status

            $sql = "SELECT meal_value, name,roll_no FROM attendance WHERE roll_no = '$app_id' AND day = '$day' AND month = '$month' AND year = '$year' LIMIT 1";

            $result = mysqli_query($conn, $sql);

        

            $response = [];

        

            if (mysqli_num_rows($result) > 0) {

                $row = mysqli_fetch_assoc($result);

                if ($row['meal_value'] == '1') {

                    $added_on=time();

                    // Insert data into MySQL database

                    $insert_query = "INSERT INTO meal_records (app_id, name, hall_id, date, meal_type,added_on,status) 

                                     VALUES ('$app_id', '$name', '$hall_id', '$date', '$meal_type','$added_on',1)";

                    $insert_result = mysqli_query($conn, $insert_query);

        

                    // Check if the query was successful

                    if ($insert_result) {

                        $response = [

                            "status_code"=> "200", //normal meal 

                            "status" => "success",

                            "message" => ucwords($meal_type)." Meal is on for " . $row['name']." & Recorded.",

                            "name" => $row['name'],

                            "app_id" => $row['roll_no'],

                            "meal_status"=> "on"

                        ];

                    } else {

                        // 500: Internal Server Error (SQL query failed)

                        $response = array(

                            "status_code" => "500", // Internal Server  Error

                            "status" => "error",

                            "message" => "Failed to insert data: " . mysqli_error($conn)

                        );

                    }

                }elseif ($row['meal_value'] == 'A') {

                    $response = [

                        "status_code"=> "204", //Dining Was off

                        "status" => "success",

                        "message" => "Dining Was off Meal for " . $row['name'],

                        "name" => $row['name'],

                        "app_id" => $row['roll_no'],

                        "meal_status"=> "on"

                    ];

                }elseif ($row['meal_value'] > '1') {

                    $response = [

                        "status_code"=> "202", //guest meal

                        "status" => "success",

                        "message" => "Meal is on with guest meal for " . $row['name'],

                        "name" => $row['name'],

                        "app_id" => $row['roll_no'],

                        "meal_status"=> "on"

                    ];

                } else {

                    $response = [            

                        "status_code"=> "201", //meal off

                        "status" => "error",

                        "message" => "Meal is off for " . $row['name'],

                        "name" => $row['name'],

                        "app_id" => $row['roll_no'],

                        "meal_status"=> "off"

                    ];

                }

            } else {

                $response = [

                    "status_code"=> "201",

                    "status" => "error",

                    "message" => "No record found for the given details"

                ];

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

