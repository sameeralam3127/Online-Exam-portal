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
    $sql = "SELECT * FROM questions WHERE exam_id = $exam_id";
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
    <style>
        body {
            background-color: #f8f9fa; /* Light background for better contrast */
        }
        .container {
            background: white; /* White background for the form */
            padding: 30px; /* Add padding for a better layout */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        }
        .navbar {
            margin-bottom: 20px; /* Space below navbar */
        }
        .question-container {
            display: none;
        }
        .question-container.active {
            display: block;
        }
        .form-check-label {
            margin-left: 10px; /* Space between radio and label */
        }
        .button-container {
            margin-top: 20px; /* Spacing above the button group */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Online Exam Portal</a>
    <div class="collapse navbar-collapse">
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
    <h2 class="mb-4">Exam Questions</h2>
    <form id="examForm" method="POST" action="submit_exam.php">
        <?php
        $questions = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $questions[] = $row; // Store questions in an array
            }
            $totalQuestions = count($questions);

            foreach ($questions as $index => $row) {
                echo "<div class='form-group question-container' id='question_$index'>";
                echo "<label class='font-weight-bold'>" . $row['question_text'] . "</label>";
                $options = explode(',', $row['options']); // Assuming options are comma-separated
                foreach ($options as $option) {
                    echo "<div class='form-check'>";
                    echo "<input class='form-check-input' type='radio' name='question_" . $row['id'] . "' value='" . trim($option) . "' required>";
                    echo "<label class='form-check-label'>" . trim($option) . "</label>";
                    echo "</div>";
                }
                echo "</div>";
            }

            echo "<input type='hidden' name='exam_id' value='" . $exam_id . "'>";
            echo "<div class='button-container'>";
            echo "<button type='button' class='btn btn-secondary' id='prevButton' onclick='prevQuestion()' disabled>Previous</button>";
            echo "<button type='button' class='btn btn-primary' id='nextButton' onclick='nextQuestion()'>Next</button>";
            echo "<button type='submit' class='btn btn-success' id='submitButton' style='display:none;'>Submit Exam</button>";
            echo "</div>";

            echo "<script>
                let currentQuestion = 0;
                const totalQuestions = $totalQuestions;

                function showQuestion(index) {
                    const questions = document.querySelectorAll('.question-container');
                    questions.forEach((q, i) => {
                        q.classList.remove('active');
                        if (i === index) {
                            q.classList.add('active');
                        }
                    });
                    document.getElementById('prevButton').disabled = index === 0;
                    document.getElementById('nextButton').style.display = index === totalQuestions - 1 ? 'none' : 'inline-block';
                    document.getElementById('submitButton').style.display = index === totalQuestions - 1 ? 'inline-block' : 'none';
                }

                function nextQuestion() {
                    if (currentQuestion < totalQuestions - 1) {
                        currentQuestion++;
                        showQuestion(currentQuestion);
                    }
                }

                function prevQuestion() {
                    if (currentQuestion > 0) {
                        currentQuestion--;
                        showQuestion(currentQuestion);
                    }
                }

                // Show the first question
                showQuestion(currentQuestion);
            </script>";
        } else {
            echo "<p>No questions available for this exam.</p>";
        }
        ?>
    </form>
</div>
</body>
</html>
