<?php
session_start();
include('../student/db.php');

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

// Fetch statistics
$user_count = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$exam_count = $conn->query("SELECT COUNT(*) AS count FROM exams")->fetch_assoc()['count'];
$result_count = $conn->query("SELECT COUNT(*) AS count FROM results")->fetch_assoc()['count'];

// Fetch average score per exam (subject)
$score_data = [];
$score_query = "SELECT e.title, AVG(r.score) AS avg_score 
                FROM exams e 
                LEFT JOIN results r ON e.id = r.exam_id 
                GROUP BY e.id, e.title";
$score_results = $conn->query($score_query);
while ($row = $score_results->fetch_assoc()) {
    $score_data[] = [
        'title' => $row['title'],
        'avg_score' => round($row['avg_score'], 2)
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card { margin-bottom: 20px; }
    </style>
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

    <!-- Dashboard Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text"><?php echo $user_count; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Exams</h5>
                    <p class="card-text"><?php echo $exam_count; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Results</h5>
                    <p class="card-text"><?php echo $result_count; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart for Subject-Wise Scores -->
    <div class="row mt-4">
        <div class="col-md-12">
            <canvas id="subjectScoreChart"></canvas>
        </div>
    </div>

    <!-- Form to Add New Exam -->
    <h3 class="mt-5">Add New Exam</h3>
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
    <table class="table table-striped">
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

<script>
    // Subject-wise average scores from PHP
    const subjectScores = <?php echo json_encode($score_data); ?>;
    const labels = subjectScores.map(item => item.title);
    const data = subjectScores.map(item => item.avg_score);

    // Render the chart using Chart.js
    const ctx = document.getElementById('subjectScoreChart').getContext('2d');
    const subjectScoreChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Average Score',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the statement if it was created
if ($stmt) {
    $stmt->close();
}
$conn->close();
?>