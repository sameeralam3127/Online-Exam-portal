<?php
session_start();
include '../db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Initialize statement variable
$stmt = null;

// Handle adding new exam
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_exam'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Prepare and bind the statement
    $stmt = $conn->prepare("INSERT INTO exams (title, description, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $title, $description);

    // Execute the statement and provide feedback
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Exam added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}

// Fetch exams for displaying
$sql = "SELECT * FROM exams";
$exams = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Admin Dashboard</a>
    <div class="collapse navbar-collapse">
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
    <h2>Admin Dashboard</h2>

    <!-- Form to Add New Exam -->
    <h3>Add New Exam</h3>
    <form method="POST" action="">
        <div class="form-group">
            <label for="title">Exam Title:</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control" id="description" name="description" required></textarea>
        </div>
        <button type="submit" name="add_exam" class="btn btn-primary">Add Exam</button>
    </form>

    <hr>

    <!-- Display Existing Exams -->
    <h3>Existing Exams</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $exams->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <a href="edit_exam.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
// Close the statement if it was created
if ($stmt) {
    $stmt->close();
}
$conn->close();
?>
