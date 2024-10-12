<?php
$servername = "localhost";
$username = "root"; // default username
$password = ""; // default password
$dbname = "online_exam_portal_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
