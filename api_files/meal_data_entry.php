<?php
// -----------------------------
// CONFIGURATION
// -----------------------------
set_time_limit(0);
ini_set('memory_limit', '1G');

$batchSize = 100;

$servername = "localhost";
$username   = "mashalla_Dhrubo";
$password   = "Dhrubo@123";
$dbname     = "mashalla_Dhrubo";

$jsonUrl = "https://bec.edu.bd/developer/test/sheet";

// -----------------------------
// CONNECT TO DATABASE
// -----------------------------
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("DB Connection failed: " . mysqli_connect_error());
}

// -----------------------------
// BACKUP TABLE SETUP
// -----------------------------
$backupTable = 'attendance_backup';
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$backupTable` LIKE attendance") 
    or die("Failed to create backup table: " . mysqli_error($conn));
mysqli_query($conn, "TRUNCATE TABLE `$backupTable`") 
    or die("Failed to truncate backup table: " . mysqli_error($conn));
mysqli_query($conn, "INSERT INTO `$backupTable` SELECT * FROM attendance") 
    or die("Failed to copy data to backup table: " . mysqli_error($conn));

// -----------------------------
// FETCH JSON
// -----------------------------
$jsonData = file_get_contents($jsonUrl);
if ($jsonData === false) die("Failed to fetch JSON data from URL");

$data = json_decode($jsonData, true);
if ($data === null) die("Invalid JSON data");

// -----------------------------
// CURRENT MONTH/YEAR/TODAY
// -----------------------------
$month = date("n");
$year  = date("Y");
$today = date("j");

// -----------------------------
// CLEAR FUTURE DAYS EXCEPT F AND A
// -----------------------------
$clearFutureQuery = "
    UPDATE attendance 
    SET meal_value = ''
    WHERE day > $today
      AND month = $month
      AND year = $year
      AND meal_value NOT IN ('F','A')
";
mysqli_query($conn, $clearFutureQuery) 
    or die("Failed to clear future days: " . mysqli_error($conn));

// -----------------------------
// LOG VARIABLES
// -----------------------------
$totalInserted = 0;
$totalUpdated  = 0;
$totalRestored = 0;

// -----------------------------
// PROCESS JSON IN BATCHES
// -----------------------------
$total = count($data);
echo "Total students: $total<br>";

for ($offset = 0; $offset < $total; $offset += $batchSize) {
    $batch = array_slice($data, $offset, $batchSize);
    $values = [];

    foreach ($batch as $student) {
        $rollNo = $student['ROLL'] ?? '';
        $regNo  = $student['REG'] ?? '';
        $name   = $student['NAME'] ?? '';
        if ($rollNo === '' || $rollNo == 0) continue;

        $forceF = (($student['9'] ?? '') === 'F') || (($student['10'] ?? '') === 'U');

        for ($day = 1; $day <= 31; $day++) {
            $mealValue = $forceF ? 'F' : ($student[(string)$day] ?? '');
            if ($mealValue === '') continue;

            $rollNoEsc = mysqli_real_escape_string($conn, $rollNo);
            $regNoEsc  = mysqli_real_escape_string($conn, $regNo);
            $nameEsc   = mysqli_real_escape_string($conn, $name);
            $mealEsc   = mysqli_real_escape_string($conn, $mealValue);

            $values[] = "('$rollNoEsc','$regNoEsc','$nameEsc','$mealEsc','$day','$month','$year')";
        }
    }

    if (count($values) > 0) {
        $insertQuery = "INSERT INTO attendance (roll_no, reg_no, name, meal_value, day, month, year) VALUES "
                     . implode(",", $values)
                     . " ON DUPLICATE KEY UPDATE 
                        reg_no = VALUES(reg_no),
                        name = VALUES(name),
                        meal_value = VALUES(meal_value)";
        if (mysqli_query($conn, $insertQuery)) {
            $affectedRows = mysqli_affected_rows($conn);
            $totalInserted += count($values);
            $totalUpdated  += max(0, $affectedRows - count($values));
        } else {
            echo "Error inserting/updating batch: " . mysqli_error($conn) . "<br>";
        }
    }
}

// -----------------------------
// RESTORE MISSING DATA FROM BACKUP
// -----------------------------
foreach ($data as $student) {
    $rollNo = $student['ROLL'] ?? '';
    if ($rollNo === '' || $rollNo == 0) continue;

    $forceF = (($student['9'] ?? '') === 'F') || (($student['10'] ?? '') === 'U');

    for ($day = 1; $day <= 31; $day++) {
        $mealValue = $forceF ? 'F' : ($student[(string)$day] ?? '');
        if ($mealValue !== '') continue; // Only restore missing entries

        $checkQuery = "SELECT * FROM attendance WHERE roll_no='$rollNo' AND day='$day' AND month='$month' AND year='$year'";
        $res = mysqli_query($conn, $checkQuery);
        if (mysqli_num_rows($res) == 0) {
            $restoreQuery = "INSERT INTO attendance (roll_no, reg_no, name, meal_value, day, month, year)
                             SELECT roll_no, reg_no, name, meal_value, day, month, year 
                             FROM `$backupTable` 
                             WHERE roll_no='$rollNo' AND day='$day' AND month='$month' AND year='$year'";
            if (mysqli_query($conn, $restoreQuery)) {
                $totalRestored += mysqli_affected_rows($conn);
            }
        }
    }
}

mysqli_close($conn);

// -----------------------------
// LOG REPORT
// -----------------------------
echo "<br>==== Import Report ====<br>";
echo "Total rows inserted/attempted: $totalInserted<br>";
echo "Total rows updated: $totalUpdated<br>";
echo "Total rows restored from backup: $totalRestored<br>";
echo "Future days cleared except 'F' and 'A'.<br>";
echo "Import process completed.<br>";
?>


<?php
// -----------------------------
// CONFIGURATION
// -----------------------------
$servername = "localhost";
$username   = "mashalla_Dhrubo";
$password   = "Dhrubo@123";
$dbname     = "mashalla_Dhrubo";

// JSON URL
$jsonUrl = "https://bec.edu.bd/developer/test/sheet";

// -----------------------------
// CONNECT TO DATABASE
// -----------------------------
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("DB Connection failed: " . mysqli_connect_error());
}

// -----------------------------
// FETCH JSON
// -----------------------------
$jsonData = file_get_contents($jsonUrl);
if ($jsonData === false) die("Failed to fetch JSON data from URL");

$data = json_decode($jsonData, true);
if ($data === null) die("Invalid JSON data");

// -----------------------------
// PROCESS JSON FOR FOOD PREFERENCES
// -----------------------------
$totalInserted = 0;
$totalUpdated  = 0;
$totalSkipped  = 0;

foreach ($data as $student) {
    $roll = mysqli_real_escape_string($conn, $student['ROLL'] ?? '');
    $egg  = mysqli_real_escape_string($conn, $student['egg_fish'] ?? '');
    $mutton = mysqli_real_escape_string($conn, $student['mutton'] ?? '');

    if ($roll === '') continue;

    // Check if roll exists
    $checkQuery = "SELECT egg_fish, mutton FROM food_preferences WHERE roll='$roll'";
    $res = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($res) > 0) {
        // Exists, check if update is needed
        $row = mysqli_fetch_assoc($res);
        if ($row['egg_fish'] === $egg && $row['mutton'] === $mutton) {
            $totalSkipped++;
            continue; // No change
        } else {
            // Update the changed values
            $updateQuery = "UPDATE food_preferences SET egg_fish='$egg', mutton='$mutton' WHERE roll='$roll'";
            if (mysqli_query($conn, $updateQuery)) {
                $totalUpdated++;
            }
        }
    } else {
        // Insert new
        $insertQuery = "INSERT INTO food_preferences (roll, egg_fish, mutton) VALUES ('$roll', '$egg', '$mutton')";
        if (mysqli_query($conn, $insertQuery)) {
            $totalInserted++;
        }
    }
}

mysqli_close($conn);

// -----------------------------
// LOG REPORT
// -----------------------------
echo "==== Food Preferences Import Report ====\n";
echo "Total inserted: $totalInserted\n";
echo "Total updated:  $totalUpdated\n";
echo "Total skipped:  $totalSkipped\n";
?>

