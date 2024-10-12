<?php
session_start();
include('../db.php'); // Ensure the path is correct to include your database connection

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php"); // Redirect to login if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Exam Portal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Admin Dashboard</h2>
    <p>Welcome, Admin!</p>
    
    <a href="manage_questions.php" class="btn btn-primary">Manage Questions</a>
    <a href="view_records.php" class="btn btn-primary">View Student Records</a>
    <a href="admin_logout.php" class="btn btn-danger">Logout</a>
</div>
</body>
</html>
