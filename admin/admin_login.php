<?php
session_start();
include('../student/db.php'); // Ensure the path to your db.php is correct

// Check if the admin is already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_dashboard.php'); // Redirect to dashboard if already logged in
    exit;
}

$error_message = ''; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Fetch the user from the database
    $query = "SELECT * FROM users WHERE username = '$username' AND role = 'admin'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $user = mysqli_fetch_assoc($result);
        
        // Check if user exists and verify password
        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, log the user in
            $_SESSION['admin_logged_in'] = true; // Set admin login session
            $_SESSION['user_id'] = $user['id']; // Optional: keep user ID
            header('Location: admin_dashboard.php'); // Redirect to admin dashboard
            exit;
        } else {
            // Invalid username or password
            $error_message = "Invalid username or password.";
        }
    } else {
        // Query failed
        $error_message = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Admin Login</title>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 400px;
            margin-top: 100px;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Login</h2>
        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
