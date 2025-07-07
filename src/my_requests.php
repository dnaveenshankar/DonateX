<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Delete request
if (isset($_POST['delete_request'])) {
    $req_id = $_POST['delete_request'];
    $stmt = $conn->prepare("DELETE FROM blood_requests WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $req_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Update status
if (isset($_POST['update_status'])) {
    $req_id = $_POST['request_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE blood_requests SET status = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $status, $req_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch requests
$stmt = $conn->prepare("SELECT id, blood_group, location, note, status, created_at FROM blood_requests WHERE user_id = ? ORDER BY created_at DESC");
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
    <title>My Blood Requests - DonateX</title>
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
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.1);
        }
    </style>
</head>
<body>
<div class="container container-box">
    <h3 class="text-danger mb-4">My Blood Requests</h3>

    <?php if (empty($requests)): ?>
        <p class="text-muted">No blood requests found.</p>
    <?php else: ?>
        <?php foreach ($requests as $r): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title text-danger">🩸 Blood Group: <?php echo $r['blood_group']; ?></h5>
                    <p class="card-text mb-1"><strong>Location:</strong> <?php echo $r['location']; ?></p>
                    <p class="card-text mb-1"><strong>Note:</strong> <?php echo $r['note'] ?: 'None'; ?></p>
                    <p class="card-text mb-1"><strong>Status:</strong> <?php echo ucfirst($r['status']); ?></p>
                    <p class="card-text text-muted"><small>Raised on: <?php echo $r['created_at']; ?></small></p>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <!-- View Volunteers -->
                        <a href="view_volunteers.php?request_id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View Volunteers
                        </a>

                        <!-- Update Status -->
                        <form method="POST" class="d-flex gap-2 align-items-center">
                            <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="pending" <?php if ($r['status'] === 'pending') echo 'selected'; ?>>Pending</option>
                                <option value="fulfilled" <?php if ($r['status'] === 'fulfilled') echo 'selected'; ?>>Fulfilled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-sm btn-outline-success">Update</button>
                        </form>

                        <!-- Delete -->
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this request?');">
                            <button type="submit" name="delete_request" value="<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="help_seeker.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Help Seeker
        </a>
    </div>
</div>
</body>
</html>
