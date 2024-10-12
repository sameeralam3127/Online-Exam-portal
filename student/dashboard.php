<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Online Exam Portal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Welcome, <?php echo $username; ?></h2>
    <h3>Your Exams</h3>
    <!-- Fetch and display available exams from the database -->
    <?php
    $sql = "SELECT * FROM exams";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<ul class='list-group'>";
        while ($row = $result->fetch_assoc()) {
            echo "<li class='list-group-item'><a href='exam.php?id=".$row['id']."'>".$row['title']."</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No exams available at the moment.</p>";
    }
    ?>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>
</body>
</html>
