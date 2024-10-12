<?php
session_start();
include('../student/db.php'); 


// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch student results with correct column names and student names
$sql = "SELECT r.user_id, r.exam_id, r.score, r.created_at AS date, u.username 
        FROM results r 
        JOIN users u ON r.user_id = u.id"; // Join with the users table to get student names
$results = $conn->query($sql);

// Check if the query was successful
if ($results === false) {
    echo "Error: " . $conn->error;
    exit();
}

// Export to CSV
if (isset($_POST['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="student_results.csv"');

    // Output the CSV header
    $output = fopen('php://output', 'w');
    fputcsv($output, ['User ID', 'Username', 'Exam ID', 'Score', 'Date']);

    // Fetch results again for CSV export
    $results->data_seek(0); // Reset the result pointer
    while ($row = $results->fetch_assoc()) {
        fputcsv($output, [
            $row['user_id'],
            $row['username'],
            $row['exam_id'],
            $row['score'],
            $row['date']
        ]);
    }
    fclose($output);
    exit(); // Stop further execution
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Results</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Student Results</h2>
    <form method="POST" action="">
        <button type="submit" name="export" class="btn btn-success mb-3">Export to CSV</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>User ID (Student ID)</th>
                <th>Username</th> <!-- Added Username column -->
                <th>Exam ID</th>
                <th>Score</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($results && $results->num_rows > 0): ?>
                <?php while ($row = $results->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td> <!-- Display Username -->
                        <td><?php echo htmlspecialchars($row['exam_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['score']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No results found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>

<?php
$conn->close();
?>
