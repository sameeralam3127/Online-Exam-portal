<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exam_id = $_POST['exam_id'];
    $user_id = $_SESSION['user_id'];
    $score = 0;

    // Fetch questions for the exam
    $sql = "SELECT * FROM questions WHERE exam_id=$exam_id";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $correct_answer = $row['correct_answer'];
        $user_answer = $_POST['question_'.$row['id']];
        if ($user_answer == $correct_answer) {
            $score++;
        }
    }

    // Save the result
    $sql = "INSERT INTO results (user_id, exam_id, score) VALUES ($user_id, $exam_id, $score)";
    if ($conn->query($sql) === TRUE) {
        echo "Exam submitted successfully! Your score: " . $score;
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
