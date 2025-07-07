<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

require_once 'db.php';

$stats = [
    'my_blood_requests' => 0,
    'my_blood_volunteered' => 0,
    'my_donations_posted' => 0,
    'my_donation_claims' => 0,
    'my_volunteer_requests' => 0,
    'my_volunteer_responses' => 0,
];

// 1. Blood requests raised by me
$res1 = $conn->query("SELECT COUNT(*) AS total FROM blood_requests WHERE user_id = $user_id");
$stats['my_blood_requests'] = $res1->fetch_assoc()['total'] ?? 0;

// 2. Blood request responses given by me
$res2 = $conn->query("SELECT COUNT(*) AS total FROM blood_request_responses WHERE donor_id = $user_id");
$stats['my_blood_volunteered'] = $res2->fetch_assoc()['total'] ?? 0;

// 3. Things donations posted by me
$res3 = $conn->query("SELECT COUNT(*) AS total FROM donations WHERE user_id = $user_id");
$stats['my_donations_posted'] = $res3->fetch_assoc()['total'] ?? 0;

// 4. Donations I claimed
$res4 = $conn->query("SELECT COUNT(*) AS total FROM donations WHERE claimed_by = $user_id");
$stats['my_donation_claims'] = $res4->fetch_assoc()['total'] ?? 0;

// 5. Volunteer requests posted by me
$res5 = $conn->query("SELECT COUNT(*) AS total FROM volunteer_requests WHERE user_id = $user_id");
$stats['my_volunteer_requests'] = $res5->fetch_assoc()['total'] ?? 0;

// 6. Volunteer responses given by me
$res6 = $conn->query("SELECT COUNT(*) AS total FROM volunteer_responses WHERE user_id = $user_id");
$stats['my_volunteer_responses'] = $res6->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Activity Report - DonateX</title>
  <link rel="icon" href="../assets/logo.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f9f9f9;
      font-family: 'Segoe UI', sans-serif;
    }
    .container-box {
      max-width: 1000px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .stat-card {
      border-left: 6px solid #dc3545;
      padding: 15px 20px;
      border-radius: 8px;
      background: #fff3f4;
    }
    .stat-title {
      font-size: 1.1rem;
      color: #dc3545;
      font-weight: bold;
    }
    .stat-value {
      font-size: 2rem;
      font-weight: bold;
      color: #212529;
    }
  </style>
</head>
<body>

<div class="container container-box">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-danger"><i class="bi bi-person-lines-fill"></i> My Activity Report</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="stat-card">
        <div class="stat-title"><i class="bi bi-droplet-half me-2"></i> Blood Requests Raised</div>
        <div class="stat-value"><?= $stats['my_blood_requests'] ?></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="stat-card">
        <div class="stat-title"><i class="bi bi-person-check-fill me-2"></i> Blood Requests Volunteered</div>
        <div class="stat-value"><?= $stats['my_blood_volunteered'] ?></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="stat-card">
        <div class="stat-title"><i class="bi bi-box-fill me-2"></i> Items Donated</div>
        <div class="stat-value"><?= $stats['my_donations_posted'] ?></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="stat-card">
        <div class="stat-title"><i class="bi bi-check-circle-fill me-2"></i> Donations Claimed</div>
        <div class="stat-value"><?= $stats['my_donation_claims'] ?></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="stat-card">
        <div class="stat-title"><i class="bi bi-person-lines-fill me-2"></i> Volunteer Posts Made</div>
        <div class="stat-value"><?= $stats['my_volunteer_requests'] ?></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="stat-card">
        <div class="stat-title"><i class="bi bi-hand-thumbs-up me-2"></i> Volunteering Responses</div>
        <div class="stat-value"><?= $stats['my_volunteer_responses'] ?></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
