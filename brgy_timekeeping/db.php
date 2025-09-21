<?php
$host = "localhost";
$user = "root"; // default sa XAMPP
$pass = "";     // default walang password
$db   = "brgy_timekeeping_db"; // ito ang pangalan ng DB mo

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
