<?php
include("php/config.php");

$reg_number = $_GET['reg_number']; // Get the registration number from the query string

// Check if the student exists in the database
$query = "SELECT qr_code FROM assets WHERE reg_number = '$reg_number' LIMIT 1";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) > 0) {
    // Student exists
    echo json_encode(['exists' => true]);
} else {
    // Student does not exist
    echo json_encode(['exists' => false]);
}
?>