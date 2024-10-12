<?php
session_start();
include 'student/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Exam Portal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f4f4f4; /* Light gray background for overall brightness */
        }
        .navbar {
            background: linear-gradient(90deg, #6a11cb, #2575fc); /* Matching gradient with hero section */
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important; /* White text for navbar items */
            font-size: 1.3rem; /* Enhanced font size */
            font-weight: bold; /* Bold text for navbar items */
        }
        .nav-link:hover {
            color: #ffffff !important; /* Keep white text on hover */
            background: rgba(255, 255, 255, 0.3); /* Light background on hover */
            border-radius: 5px; /* Rounded corners for hover effect */
        }
        .hero {
            background: linear-gradient(135deg, #6a11cb, #2575fc); /* Purple to blue gradient */
            color: white; /* White text for good contrast */
            padding: 120px 0; /* Increased padding for a more spacious feel */
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .hero h1 {
            font-size: 4rem; /* Increased font size for hero title */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Adding shadow for better visibility */
        }
        .hero p {
            font-size: 1.5rem; /* Increased font size for hero paragraph */
            margin-bottom: 30px;
        }
        .btn-custom {
            background-color: #ffffff; /* White button background */
            color: #6a11cb; /* Text color matching the theme */
            border-radius: 25px; /* Rounded button */
            padding: 10px 30px; /* Increased padding for buttons */
            font-size: 1.2rem; /* Increased font size */
        }
        .btn-custom:hover {
            background-color: #e3f2fd; /* Light hover background */
            color: #2575fc; /* Darker text color on hover */
        }
        .container {
            margin-top: 30px;
        }
        .feature-card {
            margin: 20px 0;
            transition: transform 0.2s;
            background-color: #ffffff; /* White background for cards */
            border: 1px solid #e0e0e0; /* Light border for card definition */
            border-radius: 8px; /* Rounded corners for cards */
            padding: 30px; /* Increased padding for content */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Soft shadow for cards */
        }
        .feature-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
        }
        footer {
            background-color: #343a40; /* Dark footer color */
            color: white;
            padding: 30px 0;
        }
        footer a {
            color: #007bff; /* Blue links in footer */
        }
        .social-icons a {
            margin: 0 10px;
            color: white;
            transition: color 0.3s;
        }
        .social-icons a:hover {
            color: #007bff; /* Change color on hover */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-book-reader"></i> Exam Portal <!-- Icon for the Exam Portal -->
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/admin_login.php">Feedback</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="hero">
    <div class="container">
        <h1>Welcome to the Online Exam Portal</h1>
        <p>Your gateway to knowledge and assessment!</p>
        <a href="student/register.php" class="btn btn-custom">Register</a>
        <a href="student/login.php" class="btn btn-custom">Login</a>
    </div>
</div>

<div class="container" id="about">
    <h2 class="text-center mb-4">About the Portal</h2>
    <p class="text-center mb-5">This portal allows students to register, take exams, and view results. Administrators can manage exams and user accounts efficiently.</p>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card feature-card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-graduate fa-3x mb-3" style="color: #007bff;"></i>
                    <h5 class="card-title">User-Friendly Interface</h5>
                    <p class="card-text">Simple and intuitive design for easy navigation.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-3x mb-3" style="color: #007bff;"></i>
                    <h5 class="card-title">Comprehensive Exam Management</h5>
                    <p class="card-text">Manage exams seamlessly with built-in tools.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x mb-3" style="color: #007bff;"></i>
                    <h5 class="card-title">Instant Results</h5>
                    <p class="card-text">Receive immediate feedback on your performance.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="text-center" id="contact">
    <div class="container">
        <p>&copy; <?php echo date("Y"); ?> Online Exam Portal. All Rights Reserved.</p>
        <div class="social-icons">
            <a href="#" class="btn btn-outline-light btn-sm"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="btn btn-outline-light btn-sm"><i class="fab fa-twitter"></i></a>
            <a href="#" class="btn btn-outline-light btn-sm"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
