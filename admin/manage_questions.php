<?php
session_start();
include '../db.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exam_id = $_POST['exam_id'];
    $question_text = $_POST['question_text'];
    $options = $_POST['options']; // Comma-separated
    $correct_answer = $_POST['correct_answer'];

    $sql = "INSERT INTO questions (exam_id, question_text, options, correct_answer) VALUES ('$exam_id', '$question_text', '$options', '$correct_answer')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Question added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$sql = "SELECT * FROM exams";
$exams = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Manage Questions</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="exam_id">Select Exam:</label>
            <select class="form-control" id="exam_id" name="exam_id" required>
                <?php while ($row = $exams->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['title']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="question_text">Question:</label>
            <textarea class="form-control" id="question_text" name="question_text" required></textarea>
        </div>
        <div class="form-group">
            <label for="options">Options (comma-separated):</label>
            <input type="text" class="form-control" id="options" name="options" required>
        </div>
        <div class="form-group">
            <label for="correct_answer">Correct Answer:</label>
            <input type="text" class="form-control" id="correct_answer" name="correct_answer" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Question</button>
    </form>
    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>
</body>
</html>
