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
            background-color: #f0f2f5; /* Slightly darker background for better contrast */
        }
        .container {
            background: #ffffff; /* White background for the form */
            padding: 40px; /* Increased padding for a more spacious layout */
            border-radius: 10px; /* More rounded corners */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Enhanced shadow for depth */
        }
        .navbar {
            margin-bottom: 30px; /* More space below navbar */
        }
        h2 {
            color: #333; /* Darker color for better readability */
        }
        .question-container {
            display: none;
        }
        .question-container.active {
            display: block;
        }
        .button-container {
            margin-top: 30px; /* Spacing above the button group */
        }
        .form-check {
            margin-bottom: 15px; /* Space between options */
        }
        .form-check-input {
            display: none; /* Hide the default radio button */
        }
        .btn-option {
            width: 100%; /* Full width buttons */
            text-align: left; /* Left align text */
            border: 2px solid #007bff; /* Default border */
            transition: background-color 0.3s, border-color 0.3s; /* Smooth transition */
        }
        .form-check-input:checked + .btn-option {
            background-color: #007bff; /* Bootstrap primary color */
            color: white; /* Text color for selected option */
            border-color: #007bff; /* Border color for selected option */
        }
        .btn-option:hover {
            background-color: #f8f9fa; /* Light background on hover */
            border-color: #0056b3; /* Darker border on hover */
        }
        .btn-option:focus {
            outline: none; /* Remove default focus outline */
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Custom focus outline */
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
                echo "<label class='font-weight-bold'>Question " . ($index + 1) . ": " . $row['question_text'] . "</label>";
                $options = explode(',', $row['options']); // Assuming options are comma-separated
                foreach ($options as $option) {
                    // Each option is a button
                    echo "<div class='form-check'>";
                    echo "<input class='form-check-input' type='radio' name='question_" . $row['id'] . "' id='option_" . $row['id'] . "_" . trim($option) . "' value='" . trim($option) . "' required>";
                    echo "<label class='btn btn-option' for='option_" . $row['id'] . "_" . trim($option) . "'>" . trim($option) . "</label>";
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
            echo "<p class='text-danger'>No questions available for this exam.</p>";
        }
        ?>
    </form>
</div>
</body>
</html>
