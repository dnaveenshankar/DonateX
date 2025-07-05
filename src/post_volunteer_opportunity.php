<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "donatex";
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $date_needed = $_POST['date_needed'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO volunteer_requests (user_id, title, category, location, date_needed, description, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isssss", $user_id, $title, $category, $location, $date_needed, $description);
    $stmt->execute();
    $stmt->close();

    $success = "Volunteer opportunity posted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Volunteer Opportunity - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #fffaf5;
            font-family: 'Segoe UI', sans-serif;
        }

        .container-box {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(255, 99, 71, 0.1);
        }
    </style>
</head>
<body>
<div class="container container-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-danger">Post a Volunteer Opportunity</h3>
        <a href="my_volunteer_requests.php" class="btn btn-outline-primary">
            <i class="bi bi-clock-history"></i> My Volunteer Requests
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Opportunity Title</label>
            <input type="text" name="title" class="form-control" required placeholder="e.g. Teach Village Kids">
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select" required>
                <option value="">-- Select Category --</option>
                <option value="Teaching">Teaching</option>
                <option value="Road Cleaning">Road Cleaning</option>
                <option value="Awareness Program">Awareness Program</option>
                <option value="Medical Camp">Medical Camp</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" required placeholder="e.g. Government School, Tiruppur">
        </div>
        <div class="mb-3">
            <label class="form-label">Date Needed</label>
            <input type="date" name="date_needed" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Describe the opportunity in detail..." required></textarea>
        </div>
        <button type="submit" class="btn btn-danger w-100">Post Opportunity</button>
    </form>

    <div class="mt-4">
        <a href="help_seeker.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Help Seeker</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
