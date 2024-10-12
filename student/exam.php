<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $exam_id = $_GET['id'];
    
    // Fetch questions for the exam
    $sql = "SELECT * FROM questions WHERE exam_id=$exam_id";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam - Online Exam Portal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Exam Questions</h2>
    <form method="POST" action="submit_exam.php">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='form-group'>";
                echo "<label>".$row['question_text']."</label>";
                $options = explode(',', $row['options']); // Assuming options are comma-separated
                foreach ($options as $option) {
                    echo "<div class='form-check'>";
                    echo "<input class='form-check-input' type='radio' name='question_".$row['id']."' value='".trim($option)."'>";
                    echo "<label class='form-check-label'>".trim($option)."</label>";
                    echo "</div>";
                }
                echo "</div>";
            }
            echo "<input type='hidden' name='exam_id' value='".$exam_id."'>";
            echo "<button type='submit' class='btn btn-primary'>Submit Exam</button>";
        } else {
            echo "<p>No questions available for this exam.</p>";
        }
        ?>
    </form>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>
</body>
</html>
