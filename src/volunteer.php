<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];
$success = "";

// Handle volunteer response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['opportunity_id'])) {
    $opportunity_id = $_POST['opportunity_id'];

    // Check if already volunteered
    $check = $conn->prepare("SELECT id FROM volunteer_responses WHERE volunteer_request_id = ? AND user_id = ?");
    $check->bind_param("ii", $opportunity_id, $user_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO volunteer_responses (volunteer_request_id, user_id) VALUES (?, ?)");
        $insert->bind_param("ii", $opportunity_id, $user_id);
        $insert->execute();
        $insert->close();
        $success = "You've successfully volunteered!";
    } else {
        $success = "You have already volunteered for this opportunity.";
    }
    $check->close();
}

// Fetch all opportunities excluding user’s own
$stmt = $conn->prepare("SELECT vr.id, vr.title, vr.category, vr.location, vr.date_needed, vr.description, vr.status, vr.created_at, u.name 
                        FROM volunteer_requests vr 
                        JOIN users u ON vr.user_id = u.id
                        WHERE vr.user_id != ? 
                        ORDER BY vr.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$opportunities = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteer Opportunities - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
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
    </style>
</head>
<body>

<div class="container container-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-danger">Volunteer Opportunities</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (empty($opportunities)): ?>
        <p class="text-muted">No volunteer opportunities available at the moment.</p>
    <?php else: ?>
        <?php foreach ($opportunities as $o): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title text-danger"><?php echo htmlspecialchars($o['title']); ?></h5>
                    <p class="card-text mb-1"><strong>Posted by:</strong> <?php echo htmlspecialchars($o['name']); ?></p>
                    <p class="card-text mb-1"><strong>Category:</strong> <?php echo htmlspecialchars($o['category']); ?></p>
                    <p class="card-text mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($o['location']); ?></p>
                    <p class="card-text mb-1"><strong>Date Needed:</strong> <?php echo $o['date_needed']; ?></p>
                    <p class="card-text mb-2"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($o['description'])); ?></p>
                    <p class="text-muted mb-2"><small>Posted on: <?php echo date('d M Y, h:i A', strtotime($o['created_at'])); ?></small></p>

                    <?php
                        $check_resp = $conn->prepare("SELECT id FROM volunteer_responses WHERE volunteer_request_id = ? AND user_id = ?");
                        $check_resp->bind_param("ii", $o['id'], $user_id);
                        $check_resp->execute();
                        $check_resp->store_result();
                        $already_volunteered = $check_resp->num_rows > 0;
                        $check_resp->close();
                    ?>

                    <?php if ($already_volunteered): ?>
                        <button class="btn btn-outline-success" disabled>
                            <i class="bi bi-check-circle"></i> Volunteered
                        </button>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="opportunity_id" value="<?php echo $o['id']; ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-person-check"></i> I'm Open to Volunteer
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
