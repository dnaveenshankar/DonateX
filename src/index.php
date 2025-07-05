<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DonateX - A Smart Donation Platform</title>
    <link rel="icon" type="image/png" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background: #ffffff;
            font-family: 'Segoe UI', sans-serif;
            color: #333;
        }

        .wrapper {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .main-container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.1);
            width: 100%;
            max-width: 700px;
        }

        .navbar-brand img {
            height: 40px;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: #555;
        }

        .quote-box {
            margin-top: 30px;
            padding: 20px;
            background: #ffeaea;
            border-left: 5px solid #dc3545;
            font-style: italic;
            color: #aa1e2f;
        }

        .btn-main {
            margin: 10px 8px;
            padding: 10px 25px;
            font-size: 1.1rem;
        }

        footer {
            margin-top: 40px;
            text-align: center;
            color: #999;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <div class="main-container">

            <!-- Logo Section -->
            <nav class="navbar navbar-light mb-4">
                <a class="navbar-brand d-flex align-items-center" href="#">
                    <img src="../assets/DonateX.png" alt="DonateX Logo" class="me-2">
                    <span class="fs-4 text-danger fw-bold">DonateX</span>
                </a>
            </nav>

            <!-- Hero Section -->
            <div class="text-center">
                <p class="hero-subtitle">A smart donation platform</p>

                <!-- Quote -->
                <div class="quote-box mt-4">
                    “The best way to find yourself is to lose yourself in the service of others.” – Mahatma Gandhi
                </div>

                <!-- CTA -->
                <div class="mt-4">
                    <a href="signup.php" class="btn btn-danger btn-main">Get Started</a>
                    <a href="login.php" class="btn btn-outline-secondary btn-main">Login to Continue</a>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-4">
                &copy; <?php echo date('Y'); ?> DonateX. All rights reserved.
            </footer>

        </div>
    </div>

</body>
</html>
