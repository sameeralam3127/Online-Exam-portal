<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Exam Portal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Arial', sans-serif;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            overflow-y: auto;
            transition: all 0.3s ease;
        }
        .sidebar.hidden {
            margin-left: -250px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .content.expanded {
            margin-left: 0;
        }
        .navbar-custom {
            background-color: #007bff;
            color: white;
        }
        .navbar-custom a {
            color: white;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .form-control {
            border-radius: 8px;
        }
    </style>
    <script>
        $(document).ready(function () {
            // Sidebar toggle functionality
            $('#toggle-sidebar').click(function () {
                $('.sidebar').toggleClass('hidden');
                $('.content').toggleClass('expanded');
            });

            // Fix for modal not displaying
            $('#profileModal').on('shown.bs.modal', function () {
                $(this).find('input:first').focus();
            });
        });
    </script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center py-3">Exam Portal</h4>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#" data-toggle="modal" data-target="#profileModal"><i class="fas fa-user"></i> Profile</a>
        <a href="#available-exams"><i class="fas fa-book"></i> Available Exams</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-custom">
            <button class="btn btn-light mr-3" id="toggle-sidebar">☰</button>
            <a class="navbar-brand" href="#">Dashboard</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-toggle="modal" data-target="#profileModal">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Welcome Section -->
        <div class="container mt-4">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?></h2>

            <!-- Exams Section -->
            <div id="available-exams" class="mt-5">
                <h3>Your Exams</h3>
                <div class="card">
                    <div class="card-header">Available Exams</div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT * FROM exams";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            echo "<ul class='list-group'>";
                            while ($row = $result->fetch_assoc()) {
                                echo "<li class='list-group-item'><a href='exam.php?id=".$row['id']."'>".$row['title']."</a></li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>No exams available at the moment.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">Profile Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="update_profile.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number">
                        </div>
                        <div class="form-group">
                            <label for="profile-picture">Profile Picture</label>
                            <input type="file" class="form-control-file" id="profile-picture" name="profile_picture">
                        </div>
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
