<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['request_id'])) {
    echo "Invalid Request.";
    exit();
}

$request_id = intval($_GET['request_id']);

$host = "localhost";
$user = "root";
$password = "";
$dbname = "donatex";
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify this request belongs to the logged-in user
$check_stmt = $conn->prepare("SELECT id FROM blood_requests WHERE id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $request_id, $_SESSION['user_id']);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows === 0) {
    echo "Access denied.";
    exit();
}
$check_stmt->close();

// Get volunteers
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, u.mobile, u.blood_group, u.address, r.responded_at 
    FROM blood_request_responses r 
    JOIN users u ON r.donor_id = u.id 
    WHERE r.request_id = ?
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$volunteers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle thank you
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thank_you_user_id'])) {
    $recipient_id = intval($_POST['thank_you_user_id']);
    $message = "🙏 Thank you for volunteering to help with a blood request!";
    $insert = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $insert->bind_param("is", $recipient_id, $message);
    $insert->execute();
    $success = "Thank You message sent.";
    $insert->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteers for Request #<?php echo $request_id; ?> - DonateX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #fff5f5; font-family: 'Segoe UI', sans-serif; }
        .container-box {
            max-width: 960px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.1);
        }
        .card { border: 1px solid #dee2e6; }
    </style>
</head>
<body>
<div class="container container-box">
    <h3 class="text-danger mb-4">Volunteers for Blood Request #<?php echo $request_id; ?></h3>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (empty($volunteers)): ?>
        <p class="text-muted">No one has volunteered yet.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($volunteers as $v): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title text-danger">🩸 <?php echo htmlspecialchars($v['name']); ?></h5>
                            <p class="mb-1"><strong>Email:</strong> <?php echo $v['email']; ?></p>
                            <p class="mb-1"><strong>Mobile:</strong> <?php echo $v['mobile']; ?></p>
                            <p class="mb-1"><strong>Blood Group:</strong> <?php echo $v['blood_group']; ?></p>
                            <p class="mb-2"><strong>Address:</strong> <?php echo $v['address']; ?></p>
                            <p class="text-muted"><small>Responded at: <?php echo $v['responded_at']; ?></small></p>

                            <form method="POST">
                                <input type="hidden" name="thank_you_user_id" value="<?php echo $v['id']; ?>">
                                <button type="submit" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-envelope-heart"></i> Send Thank You
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="my_requests.php" class="btn btn-outline-secondary mt-3">
        <i class="bi bi-arrow-left"></i> Back to My Requests
    </a>
</div>
</body>
</html>
