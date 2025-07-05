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

// Fetch user details
$stmt = $conn->prepare("SELECT name, blood_group FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $blood_group);
$stmt->fetch();
$stmt->close();

// Handle volunteer response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respond'])) {
    $request_id = $_POST['request_id'];

    // Check if already responded
    $check = $conn->prepare("SELECT id FROM blood_request_responses WHERE request_id = ? AND donor_id = ?");
    $check->bind_param("ii", $request_id, $user_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        // Insert response
        $insert = $conn->prepare("INSERT INTO blood_request_responses (request_id, donor_id) VALUES (?, ?)");
        $insert->bind_param("ii", $request_id, $user_id);
        $insert->execute();
        $insert->close();

        // Optional: Add notification to requester
        $req_user_stmt = $conn->prepare("SELECT user_id FROM blood_requests WHERE id = ?");
        $req_user_stmt->bind_param("i", $request_id);
        $req_user_stmt->execute();
        $req_user_stmt->bind_result($req_user_id);
        $req_user_stmt->fetch();
        $req_user_stmt->close();

        $noti_msg = "$name has volunteered for your blood request.";
        $notify = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notify->bind_param("is", $req_user_id, $noti_msg);
        $notify->execute();
        $notify->close();

        $success = "Your response has been recorded.";
    }
    $check->close();
}

// Fetch blood requests matching user’s blood group and pending status
$query = $conn->prepare("SELECT br.id, u.name AS requester_name, br.location, br.note, br.created_at 
                         FROM blood_requests br
                         JOIN users u ON br.user_id = u.id
                         WHERE br.blood_group = ? AND br.status = 'pending'
                         ORDER BY br.created_at DESC");
$query->bind_param("s", $blood_group);
$query->execute();
$result = $query->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);
$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Donor - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff5f5;
            font-family: 'Segoe UI', sans-serif;
        }
        .container-box {
            max-width: 960px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.1);
        }
        .card {
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

<div class="container container-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-danger">Matching Blood Requests (<?php echo $blood_group; ?>)</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <p class="text-muted">No requests found for your blood group.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($requests as $r): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title text-danger">🩸 Request by: <?php echo htmlspecialchars($r['requester_name']); ?></h5>
                            <p class="card-text"><strong>Location:</strong> <?php echo htmlspecialchars($r['location']); ?></p>
                            <?php if (!empty($r['note'])): ?>
                                <p class="card-text"><strong>Note:</strong> <?php echo htmlspecialchars($r['note']); ?></p>
                            <?php endif; ?>
                            <p class="card-text"><small class="text-muted">Requested at: <?php echo date("d M Y, h:i A", strtotime($r['created_at'])); ?></small></p>

                            <?php
                                $check_res = $conn->prepare("SELECT id FROM blood_request_responses WHERE request_id = ? AND donor_id = ?");
                                $check_res->bind_param("ii", $r['id'], $user_id);
                                $check_res->execute();
                                $check_res->store_result();
                                $already_responded = $check_res->num_rows > 0;
                                $check_res->close();
                            ?>

                            <?php if ($already_responded): ?>
                                <button class="btn btn-outline-success mt-2" disabled><i class="bi bi-check-circle"></i> Volunteered</button>
                            <?php else: ?>
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" name="respond" class="btn btn-danger">I Can Volunteer</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
