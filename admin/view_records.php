<?php
session_start();
include '../db.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Records - Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Student Records</h2>
    <?php
    $sql = "SELECT users.username, exams.title, results.score FROM results JOIN users ON results.user_id = users.id JOIN exams ON results.exam_id = exams.id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>Username</th><th>Exam Title</th><th>Score</th></tr></thead><tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row['username']."</td><td>".$row['title']."</td><td>".$row['score']."</td></tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No student records found.</p>";
    }
    ?>
    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>
</body>
</html>
