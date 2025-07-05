<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Help Seeker - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #fff5f5;
            font-family: 'Segoe UI', sans-serif;
        }
        .container-box {
            max-width: 960px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.1);
        }
        .title {
            color: #dc3545;
            font-weight: bold;
            margin-bottom: 25px;
        }
        .option-card {
            padding: 25px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: #fdfdfd;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.03);
            transition: 0.2s ease;
        }
        .option-card:hover {
            transform: scale(1.02);
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
        }
        footer {
            margin-top: 40px;
            text-align: center;
            font-size: 0.9rem;
            color: #888;
        }
    </style>
</head>
<body>

<div class="container container-box">
    <div class="text-center">
        <img src="../assets/DonateX.png" class="mb-2" style="height:50px;" alt="DonateX">
        <h2 class="title">Help Seeker Portal</h2>
        <p class="text-muted">Find support or offer opportunities with ease</p>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <a href="search_blood_donors.php" class="text-decoration-none text-dark">
                <div class="option-card text-center">
                    <i class="bi bi-droplet-half display-5 text-danger"></i>
                    <h5 class="mt-3">Search Blood Donors</h5>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="search_volunteers.php" class="text-decoration-none text-dark">
                <div class="option-card text-center">
                    <i class="bi bi-person-check display-5 text-primary"></i>
                    <h5 class="mt-3">Search Volunteers</h5>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="search_donations.php" class="text-decoration-none text-dark">
                <div class="option-card text-center">
                    <i class="bi bi-box-seam display-5 text-success"></i>
                    <h5 class="mt-3">Search Donations</h5>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="post_volunteer_opportunity.php" class="text-decoration-none text-dark">
                <div class="option-card text-center">
                    <i class="bi bi-megaphone display-5 text-warning"></i>
                    <h5 class="mt-3">Post a Volunteering Opportunity</h5>
                </div>
            </a>
        </div>
    </div>

    <!-- Back to Dashboard Button -->
    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <footer class="mt-5">&copy; <?php echo date('Y'); ?> DonateX. All rights reserved.</footer>
</div>

</body>
</html>
