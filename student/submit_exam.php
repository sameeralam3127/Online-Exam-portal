<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables for correct and wrong answers and score
$correct_answers = [];
$wrong_answers = [];
$score = 0;

// Check if exam has already been submitted in the current session
if (!isset($_SESSION['exam_submitted'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $exam_id = $_POST['exam_id'];
        $user_id = $_SESSION['user_id'];

        // Fetch questions for the exam
        $sql = "SELECT * FROM questions WHERE exam_id=$exam_id";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $correct_answer = $row['correct_answer'];
            $question_id = $row['id'];

            // Check if the answer exists in the POST data
            if (isset($_POST['question_' . $question_id])) {
                $user_answer = $_POST['question_' . $question_id];

                // Store correct and wrong answers
                if ($user_answer == $correct_answer) {
                    $score++;
                    $correct_answers[] = [
                        'question' => $row['question_text'],
                        'user_answer' => $user_answer,
                        'correct_answer' => $correct_answer
                    ];
                } else {
                    $wrong_answers[] = [
                        'question' => $row['question_text'],
                        'user_answer' => $user_answer,
                        'correct_answer' => $correct_answer
                    ];
                }
            } else {
                // Handle unanswered questions
                $wrong_answers[] = [
                    'question' => $row['question_text'],
                    'user_answer' => 'No answer',
                    'correct_answer' => $correct_answer
                ];
            }
        }

        // Save the result
        $sql = "INSERT INTO results (user_id, exam_id, score) VALUES ($user_id, $exam_id, $score)";
        if ($conn->query($sql) === TRUE) {
            // Set session variable to indicate exam has been submitted
            $_SESSION['exam_submitted'] = true;

            echo "<div class='alert alert-success'>Exam submitted successfully! Your score: " . $score . "</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
} else {
    // Prevent form submission if exam has already been submitted
    echo "<div class='alert alert-warning'>You have already submitted this exam, Kindly logout and submit the Exam.</div>";
}

// Fetch attempt history
$user_id = $_SESSION['user_id'];
$sql_history = "SELECT results.exam_id, results.score, results.created_at, exams.title AS exam_title 
                FROM results 
                JOIN exams ON results.exam_id = exams.id 
                WHERE results.user_id = $user_id 
                ORDER BY results.created_at DESC";

$history_results = $conn->query($sql_history);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Exam Results</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .correct {
            background-color: #d4edda; /* Light green */
            color: #155724; /* Dark green */
        }
        .wrong {
            background-color: #f8d7da; /* Light red */
            color: #721c24; /* Dark red */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="dashboard.php">Online Exam Portal</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <h2>Your Exam Results</h2>

    <h4>Correct Answers</h4>
    <?php if (!empty($correct_answers)): ?>
        <ul class="list-group">
            <?php foreach ($correct_answers as $answer): ?>
                <li class="list-group-item correct">
                    <strong>Question:</strong> <?php echo htmlspecialchars($answer['question']); ?><br>
                    <strong>Your Answer:</strong> <?php echo htmlspecialchars($answer['user_answer']); ?><br>
                    <strong>Correct Answer:</strong> <?php echo htmlspecialchars($answer['correct_answer']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No correct answers.</p>
    <?php endif; ?>

    <h4>Wrong Answers</h4>
    <?php if (!empty($wrong_answers)): ?>
        <ul class="list-group">
            <?php foreach ($wrong_answers as $answer): ?>
                <li class="list-group-item wrong">
                    <strong>Question:</strong> <?php echo htmlspecialchars($answer['question']); ?><br>
                    <strong>Your Answer:</strong> <?php echo htmlspecialchars($answer['user_answer']); ?><br>
                    <strong>Correct Answer:</strong> <?php echo htmlspecialchars($answer['correct_answer']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No wrong answers.</p>
    <?php endif; ?>

    <h4>Your Attempt History</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Exam Title</th>
                <th>Score</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($history_results && $history_results->num_rows > 0): ?>
                <?php while ($row = $history_results->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['exam_title']); ?></td>
                        <td><?php echo htmlspecialchars($row['score']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">No attempt history found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
