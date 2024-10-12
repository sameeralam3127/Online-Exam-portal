<?php
session_start();
include('../student/db.php'); 

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle adding a new question
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    // Collect data from the form
    $exam_id = $_POST['exam_id'];
    $question_text = $_POST['question_text'];
    $options = $_POST['options']; // Comma-separated options
    $correct_answer = $_POST['correct_answer'];

    // Prepare and bind for adding questions
    $stmt = $conn->prepare("INSERT INTO questions (exam_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    // Split options into separate variables
    $optionsArray = explode(',', $options);
    if (count($optionsArray) < 4) {
        echo "<div class='alert alert-danger'>Please provide at least four options.</div>";
    } else {
        // Bind parameters, ensuring the correct types
        $stmt->bind_param("isssss", $exam_id, $question_text, $optionsArray[0], $optionsArray[1], $optionsArray[2], $optionsArray[3], $correct_answer);
        
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Question added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
    }
    $stmt->close(); // Close the statement after executing
}

// Handle editing a question
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $question_id = $_POST['question_id'];
    $exam_id = $_POST['exam_id'];
    $question_text = $_POST['question_text'];
    $options = $_POST['options'];
    $correct_answer = $_POST['correct_answer'];

    // Prepare statement for updating a question
    $edit_stmt = $conn->prepare("UPDATE questions SET exam_id = ?, question = ?, options = ?, correct_answer = ? WHERE id = ?");
    if ($edit_stmt === false) {
        die("Error preparing edit statement: " . $conn->error);
    }
    
    $edit_stmt->bind_param("isssi", $exam_id, $question_text, $options, $correct_answer, $question_id);
    
    if ($edit_stmt->execute()) {
        echo "<div class='alert alert-success'>Question updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $edit_stmt->error . "</div>";
    }
    $edit_stmt->close();
}

// Fetch exams for the dropdown
$sql = "SELECT * FROM exams";
$exams = $conn->query($sql);

// Fetch all questions
$questions_sql = "SELECT * FROM questions";
$questions = $conn->query($questions_sql);
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
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Admin Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_questions.php">Manage Questions</a></li>
            <li class="nav-item"><a class="nav-link" href="view_records.php">View Student Results</a></li>
            <li class="nav-item"><a class="nav-link" href="reset_student_password.php">Reset Student Password</a></li>
        </ul>
        <a href="admin_logout.php" class="btn btn-outline-danger my-2 my-sm-0">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <h2>Manage Questions</h2>
    
    <!-- Form to Add New Question -->
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label for="exam_id">Select Exam:</label>
            <select class="form-control" id="exam_id" name="exam_id" required>
                <?php while ($row = $exams->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['title']); ?></option>
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

    <!-- Existing Questions -->
    <h3 class="mt-5">Existing Questions</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Exam ID</th>
                <th>Question</th>
                <th>Options</th>
                <th>Correct Answer</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($question = $questions->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($question['id']); ?></td>
                    <td><?php echo htmlspecialchars($question['exam_id']); ?></td>
                    <td><?php echo htmlspecialchars($question['question']); ?></td>
                    <td><?php echo htmlspecialchars($question['options']); ?></td>
                    <td><?php echo htmlspecialchars($question['correct_answer']); ?></td>
                    <td>
                        <button class="btn btn-warning" data-toggle="modal" data-target="#editModal<?php echo $question['id']; ?>">Edit</button>
                        <!-- Edit Modal (same as before) -->
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
