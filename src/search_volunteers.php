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

$search = $_GET['search'] ?? '';

// Fetch volunteers
if (!empty($search)) {
    $keyword = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT name, email, mobile, address FROM users WHERE FIND_IN_SET('Volunteer', roles) AND (
        name LIKE ? OR email LIKE ? OR mobile LIKE ? OR address LIKE ?
    )");
    $stmt->bind_param("ssss", $keyword, $keyword, $keyword, $keyword);
} else {
    $stmt = $conn->prepare("SELECT name, email, mobile, address FROM users WHERE FIND_IN_SET('Volunteer', roles)");
}

$stmt->execute();
$result = $stmt->get_result();
$volunteers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Volunteers - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #fdfdfd;
            font-family: 'Segoe UI', sans-serif;
        }
        .container-box {
            max-width: 960px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .card {
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
<div class="container container-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-danger">Search Volunteers</h3>
        <a href="help_seeker.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Help Seeker</a>
    </div>

    <form method="GET" class="input-group mb-4">
        <input type="text" name="search" class="form-control" placeholder="Search by name, location, email, or mobile" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-dark"><i class="bi bi-search"></i> Search</button>
    </form>

    <div class="row">
        <?php if (empty($volunteers)): ?>
            <p class="text-muted">No volunteers found.</p>
        <?php else: ?>
            <?php foreach ($volunteers as $v): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title text-danger"><i class="bi bi-person-heart"></i> <?php echo htmlspecialchars($v['name']); ?></h5>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($v['email']); ?></p>
                            <p class="mb-1"><strong>Mobile:</strong> <?php echo htmlspecialchars($v['mobile']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($v['address']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
