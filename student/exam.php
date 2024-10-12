<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the exam ID and question ID from the URL
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 1;
$question_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Fetch the current question
$sql = "SELECT * FROM questions WHERE exam_id = $exam_id AND id = $question_id";
$result = $conn->query($sql);
$question = $result->fetch_assoc();

if (!$question) {
    echo "Question not found.";
    exit();
}

// Fetch total questions for navigation
$total_questions_sql = "SELECT COUNT(*) as total FROM questions WHERE exam_id = $exam_id";
$total_result = $conn->query($total_questions_sql);
$total_questions = $total_result->fetch_assoc()['total'];

// Convert options from comma-separated string to an array
$options = explode(',', $question['options']);
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
            background-color: #f4f7fa;
            font-family: 'Arial', sans-serif;
        }
        .navbar {
            margin-bottom: 30px;
        }
        .container {
            margin-top: 50px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h2, h3 {
            color: #343a40;
            margin-bottom: 20px;
        }
        .table th, .table td {
            text-align: center;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Online Exam Portal</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <h2>Exam - Question <?php echo $question_id; ?> of <?php echo $total_questions; ?></h2>
        <h3><?php echo htmlspecialchars($question['question_text']); ?></h3>

        <form action="submit_exam.php" method="POST">
            <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
            <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($options as $index => $option): ?>
                        <tr>
                            <td>
                                <input type="radio" name="selected_option" id="option-<?php echo $index; ?>" value="<?php echo htmlspecialchars(trim($option)); ?>">
                                <label for="option-<?php echo $index; ?>"><?php echo htmlspecialchars(trim($option)); ?></label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-between">
                <?php if ($question_id > 1): ?>
                    <a href="exam.php?exam_id=<?php echo $exam_id; ?>&id=<?php echo $question_id - 1; ?>" class="btn btn-primary">Previous</a>
                <?php endif; ?>
                <?php if ($question_id < $total_questions): ?>
                    <a href="exam.php?exam_id=<?php echo $exam_id; ?>&id=<?php echo $question_id + 1; ?>" class="btn btn-primary">Next</a>
                <?php else: ?>
                    <button type="submit" class="btn btn-success">Submit Exam</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
