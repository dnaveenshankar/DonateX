<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];
$success = "";

// Mark as fulfilled
if (isset($_POST['mark_fulfilled'])) {
    $req_id = $_POST['request_id'];
    $stmt = $conn->prepare("UPDATE volunteer_requests SET status = 'fulfilled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $req_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $success = "Request marked as fulfilled.";
}

// Delete request
if (isset($_POST['delete_request'])) {
    $req_id_del = $_POST['delete_request'];
    $stmt = $conn->prepare("DELETE FROM volunteer_requests WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $req_id_del, $user_id);
    $stmt->execute();
    $stmt->close();
    $success = "Request deleted successfully.";
}

// Send Thank You
if (isset($_POST['send_thank_you'])) {
    $to_user = $_POST['volunteer_id'];
    $thank_msg = "🙏 Thank you for volunteering in one of my posted opportunities!";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $to_user, $thank_msg);
    $stmt->execute();
    $stmt->close();
    $success = "Thank you note sent!";
}

// Fetch volunteer requests by user
$stmt = $conn->prepare("SELECT * FROM volunteer_requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Volunteer Requests - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .container-box {
            max-width: 960px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
<div class="container container-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-danger">My Volunteer Requests</h3>
        <a href="post_volunteer_opportunity.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Post
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <p class="text-muted">You haven't posted any volunteer requests yet.</p>
    <?php else: ?>
        <?php foreach ($requests as $r): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title text-danger"><?php echo htmlspecialchars($r['title']); ?></h5>
                    <p class="mb-1"><strong>Category:</strong> <?php echo $r['category']; ?></p>
                    <p class="mb-1"><strong>Location:</strong> <?php echo $r['location']; ?></p>
                    <p class="mb-1"><strong>Date Needed:</strong> <?php echo $r['date_needed']; ?></p>
                    <p class="mb-1"><strong>Status:</strong> <?php echo ucfirst($r['status']); ?></p>
                    <p class="mb-2"><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($r['description'])); ?></p>

                    <!-- Mark Fulfilled -->
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                        <button type="submit" name="mark_fulfilled" class="btn btn-success btn-sm">
                            <i class="bi bi-check-circle"></i> Mark Fulfilled
                        </button>
                    </form>

                    <!-- Delete -->
                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this?');">
                        <button type="submit" name="delete_request" value="<?php echo $r['id']; ?>" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>

                    <hr>

                    <h6 class="text-dark">Volunteers:</h6>
                    <?php
                        $vol_query = $conn->prepare("SELECT u.id, u.name, u.mobile, u.email FROM volunteer_responses vr JOIN users u ON vr.user_id = u.id WHERE vr.volunteer_request_id = ?");
                        $vol_query->bind_param("i", $r['id']);
                        $vol_query->execute();
                        $vol_result = $vol_query->get_result();
                        if ($vol_result->num_rows === 0) {
                            echo "<p class='text-muted'>No one has volunteered yet.</p>";
                        } else {
                            while ($vol = $vol_result->fetch_assoc()):
                    ?>
                        <div class="border p-2 rounded mb-2">
                            <p class="mb-1"><strong><?php echo htmlspecialchars($vol['name']); ?></strong></p>
                            <p class="mb-1">📞 <?php echo $vol['mobile']; ?> | ✉️ <?php echo $vol['email']; ?></p>
                            <form method="POST">
                                <input type="hidden" name="volunteer_id" value="<?php echo $vol['id']; ?>">
                                <button type="submit" name="send_thank_you" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-heart"></i> Send Thank You
                                </button>
                            </form>
                        </div>
                    <?php
                            endwhile;
                        }
                        $vol_query->close();
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
