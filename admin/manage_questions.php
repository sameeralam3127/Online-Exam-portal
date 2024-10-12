<?php
session_start();
include('../student/db.php'); 


// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Prepare and bind for adding questions
$stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, options, correct_answer) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $exam_id, $question_text, $options, $correct_answer);

// Handle adding a new question
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $exam_id = $_POST['exam_id'];
    $question_text = $_POST['question_text'];
    $options = $_POST['options']; // Comma-separated
    $correct_answer = $_POST['correct_answer'];

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Question added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}

// Handle editing a question
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $question_id = $_POST['question_id'];
    $exam_id = $_POST['exam_id'];
    $question_text = $_POST['question_text'];
    $options = $_POST['options'];
    $correct_answer = $_POST['correct_answer'];

    // Prepare statement for updating a question
    $edit_stmt = $conn->prepare("UPDATE questions SET exam_id = ?, question_text = ?, options = ?, correct_answer = ? WHERE id = ?");
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
                    <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                    <td><?php echo htmlspecialchars($question['options']); ?></td>
                    <td><?php echo htmlspecialchars($question['correct_answer']); ?></td>
                    <td>
                        <!-- Edit Button -->
                        <button class="btn btn-warning" data-toggle="modal" data-target="#editModal<?php echo $question['id']; ?>">Edit</button>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo $question['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel">Edit Question</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="exam_id">Select Exam:</label>
                                                <select class="form-control" id="exam_id" name="exam_id" required>
                                                    <?php 
                                                    // Reset exams to ensure all are available for selection
                                                    $exams->data_seek(0);
                                                    while ($row = $exams->fetch_assoc()): ?>
                                                        <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo $question['exam_id'] == $row['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['title']); ?></option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="question_text">Question:</label>
                                                <textarea class="form-control" id="question_text" name="question_text" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="options">Options (comma-separated):</label>
                                                <input type="text" class="form-control" id="options" name="options" required value="<?php echo htmlspecialchars($question['options']); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="correct_answer">Correct Answer:</label>
                                                <input type="text" class="form-control" id="correct_answer" name="correct_answer" required value="<?php echo htmlspecialchars($question['correct_answer']); ?>">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Update Question</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
