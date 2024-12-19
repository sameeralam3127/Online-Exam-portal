<?php
$servername = "localhost"; // Use the service name defined in docker-compose.yml
$username = "root"; // default username
$password = ""; // default password
$dbname = "online_exam_portal_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully"; // Comment this out if you don't need this message
?>
