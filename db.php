<?php
$host = "localhost";
$user = "root";     // default in XAMPP
$pass = "";         // default is empty
$dbname = "copo_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
