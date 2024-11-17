<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Data</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        @media screen {
            .row-f { background-color: #ffcccc; }
            .cell-positive { background-color: #ccffcc; }
            .cell-a { background-color: #ccf2ff; }
        }

        @media print {
            @page { margin: 20mm; }
            body { margin: 0; padding: 0; }
            table { margin: 20px auto; width: calc(100% - 40px); }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            .row-f, .cell-positive, .cell-a { background-color: transparent !important; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; margin: 20px;">
    <form method="GET">
        <label for="month">Select Month:</label>
        <select id="month" name="month" required>
            <?php
            for ($m = 1; $m <= 12; $m++) {
                $monthName = date("F", mktime(0, 0, 0, $m, 1));
                echo "<option value=\"$m\" " . ($_GET['month'] == $m ? 'selected' : '') . ">$monthName</option>";
            }
            ?>
        </select>
        <label for="year">Select Year:</label>
        <select id="year" name="year" required>
            <?php
            $currentYear = date("Y");
            for ($y = $currentYear - 5; $y <= $currentYear; $y++) {
                echo "<option value=\"$y\" " . ($_GET['year'] == $y ? 'selected' : '') . ">$y</option>";
            }
            ?>
        </select>
        <button type="submit">Filter</button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Roll No</th>
            <th>Reg No</th>
            <th>Name</th>
            <?php for ($day = 1; $day <= 31; $day++): ?>
                <th><?php echo $day; ?></th>
            <?php endfor; ?>
            <th>Total Meals</th>
            <th>Total Meal Off</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $conn = mysqli_connect("localhost", "mashalla_Dhrubo", "Dhrubo@123", "mashalla_Dhrubo");
    if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

    $month = isset($_GET['month']) ? $_GET['month'] : date("n");
    $year = isset($_GET['year']) ? $_GET['year'] : date("Y");

    $sql = "SELECT roll_no, reg_no, name, batch_name, day, meal_value FROM attendance WHERE month = $month AND year = $year ORDER BY batch_name, roll_no, day";
    $result = mysqli_query($conn, $sql);

    $data = [];
    $dailyTotals = array_fill(1, 31, 0);
    $dailyMealOffs = array_fill(1, 31, 0);
    $batchTotals = [];
    $grandTotalMeals = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $roll_no = $row['roll_no'];
        $batch_name = $row['batch_name'];

        if (!isset($data[$roll_no])) {
            $data[$roll_no] = [
                'roll_no' => $row['roll_no'],
                'reg_no' => $row['reg_no'],
                'name' => $row['name'],
                'batch_name' => $row['batch_name'],
                'meals' => array_fill(1, 31, null)
            ];
        }
        $data[$roll_no]['meals'][$row['day']] = $row['meal_value'];
        
        if (!isset($batchTotals[$batch_name])) {
            $batchTotals[$batch_name] = array_fill(1, 31, 0);
        }
    }

    foreach ($data as $student) {
        $totalMeals = 0;
        $mealOffCount = 0;
        $batch_name = $student['batch_name'];

        echo "<tr>";
        echo "<td>{$student['roll_no']}</td>";
        echo "<td>{$student['reg_no']}</td>";
        echo "<td>{$student['name']}</td>";

        for ($day = 1; $day <= 31; $day++) {
            $meal_value = $student['meals'][$day];
            $class = "";

            if ($meal_value === 'F') {
                $class = "row-f";
            } elseif ($meal_value === 'A') {
                $class = "cell-a";
            } elseif ($meal_value > 0) {
                $class = "cell-positive";
            }

            echo "<td class='$class'>" . ($meal_value !== null ? $meal_value : '') . "</td>";

            if ($meal_value !== 'F' && $meal_value !== "A") {
                if ($meal_value > 0) {
                    $totalMeals += $meal_value;
                    $dailyTotals[$day] += $meal_value;
                    $batchTotals[$batch_name][$day] += $meal_value;
                } elseif ($meal_value === '0') {
                    $mealOffCount++;
                    $dailyMealOffs[$day]++;
                }
            }
        }

        $grandTotalMeals += $totalMeals;

        echo "<td>$totalMeals</td>";
        echo "<td>$mealOffCount</td>";
        echo "</tr>";
    }

    mysqli_close($conn);
    ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>Daily Meal Totals > 1:</strong></td>
            <?php
            for ($day = 1; $day <= 31; $day++) {
                echo "<td><strong>" . ($dailyTotals[$day] > 1 ? $dailyTotals[$day] : '') . "</strong></td>";
            }
            ?>
            <td colspan="2"></td>
        </tr>

        <?php foreach ($batchTotals as $batch_name => $totals): ?>
            <tr>
                <td colspan="3"><strong><?php echo $batch_name; ?> Totals:</strong></td>
                <?php
                for ($day = 1; $day <= 31; $day++) {
                    echo "<td><strong>" . ($totals[$day] > 0 ? $totals[$day] : '') . "</strong></td>";
                }
                ?>
                <td colspan="2"></td>
            </tr>
        <?php endforeach; ?>

        <tr>
            <td colspan="3"><strong>Grand Total Meals:</strong></td>
            <td colspan="31" style="text-align: center;"><strong><?php echo $grandTotalMeals; ?></strong></td>
            <td></td>
        </tr>

        <tr>
            <td colspan="3"><strong>Daily Meal Off Totals:</strong></td>
            <?php
            for ($day = 1; $day <= 31; $day++) {
                echo "<td><strong>" . ($dailyMealOffs[$day] > 0 ? $dailyMealOffs[$day] : '') . "</strong></td>";
            }
            ?>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
