<?php
include('config.php');

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM attendance WHERE id=$id");
$row = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meal_value = $_POST['meal_value'];
    mysqli_query($conn, "UPDATE attendance SET meal_value='$meal_value' WHERE id=$id");
    header("Location: admin_panel.php");
}
?>

<form method="POST">
    <label>Meal Value:</label>
    <input type="text" name="meal_value" value="<?php echo $row['meal_value']; ?>">
    <button type="submit">Update</button>
</form>
