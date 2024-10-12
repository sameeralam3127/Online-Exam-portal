<?php
session_start();
include 'student/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Exam Portal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .hero {
            background: #007bff;
            color: white;
            padding: 50px 0;
            text-align: center;
        }
        .hero h1 {
            font-size: 3rem;
        }
        .hero p {
            font-size: 1.25rem;
        }
    </style>
</head>
<body>

<div class="hero">
    <div class="container">
        <h1>Welcome to the Online Exam Portal</h1>
        <p>Your gateway to knowledge and assessment!</p>
        <a href="student/register.php" class="btn btn-light btn-lg">Register</a>
        <a href="student/login.php" class="btn btn-light btn-lg">Login</a>
    </div>
</div>

<div class="container my-5">
    <h2>About the Portal</h2>
    <p>This portal allows students to register, take exams, and view results. Administrators can manage exams and user accounts efficiently.</p>
</div>

<footer class="text-center py-4">
    <p>&copy; <?php echo date("Y"); ?> Online Exam Portal. All Rights Reserved.</p>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
