<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Debugging: Show errors during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch logged-in user info
$user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_stmt->bind_result($name);
$user_stmt->fetch();
$user_stmt->close();

// Handle Blood Request Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['raise_request'])) {
    $req_blood_group = $_POST['req_blood_group'];
    $location = $_POST['location'];
    $note = $_POST['note'];

    // Store blood request
    $insert = $conn->prepare("INSERT INTO blood_requests (user_id, blood_group, location, note) VALUES (?, ?, ?, ?)");
    $insert->bind_param("isss", $user_id, $req_blood_group, $location, $note);
    $insert->execute();

    $insert->close();

    // Notify blood donors
    $notif_query = $conn->prepare("SELECT id FROM users WHERE FIND_IN_SET('Blood Donor', roles) AND blood_group = ?");
    $notif_query->bind_param("s", $req_blood_group);
    $notif_query->execute();
    $result = $notif_query->get_result();

    $message = "Blood request for group $req_blood_group raised by $name. Respond if you can save a life.";
    while ($row = $result->fetch_assoc()) {
        $uid = $row['id'];
        $notify = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notify->bind_param("is", $uid, $message);
        $notify->execute();
    }
    $notif_query->close();
    $success = "Blood request stored and donors notified successfully.";
}

// Fetch all blood donors
$filter_group = $_GET['filter'] ?? '';
if ($filter_group) {
    $stmt = $conn->prepare("SELECT name, email, mobile, blood_group, address FROM users WHERE FIND_IN_SET('Blood Donor', roles) AND blood_group = ?");
    $stmt->bind_param("s", $filter_group);
} else {
    $stmt = $conn->prepare("SELECT name, email, mobile, blood_group, address FROM users WHERE FIND_IN_SET('Blood Donor', roles)");
}
$stmt->execute();
$result = $stmt->get_result();
$donors = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Blood Donors - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background: #fff5f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .dashboard-container {
            max-width: 960px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.1);
        }

        .card {
            border: 1px solid #dee2e6;
        }

        .btn-square {
            border-radius: 0;
        }
    </style>
</head>

<body>
    <div class="container dashboard-container">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h3 class="text-danger">Search Blood Donors</h3>
            <div class="d-flex gap-2">
                <a href="my_requests.php" class="btn btn-outline-dark">
                    <i class="bi bi-clock-history"></i> My Requests
                </a>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#requestModal">
                    <i class="bi bi-plus-circle"></i> Raise Blood Request
                </button>
            </div>
        </div>


        <?php if (!empty($success)): ?>
            <div class="alert alert-success"> <?php echo $success; ?> </div>
        <?php endif; ?>

        <form class="row g-2 mb-4" method="GET">
            <div class="col-md-4">
                <select name="filter" class="form-select">
                    <option value="">-- Filter by Blood Group --</option>
                    <?php foreach (["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"] as $group): ?>
                        <option value="<?php echo $group; ?>" <?php echo $filter_group == $group ? 'selected' : ''; ?>>
                            <?php echo $group; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100">Filter</button>
            </div>
        </form>

        <div class="row">
            <?php if (empty($donors)): ?>
                <p class="text-muted">No blood donors found.</p>
            <?php else: ?>
                <?php foreach ($donors as $d): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title text-danger">🩸 <?php echo htmlspecialchars($d['name']); ?></h5>
                                <p class="card-text mb-1"><strong>Blood:</strong> <?php echo $d['blood_group']; ?></p>
                                <p class="card-text mb-1"><strong>Mobile:</strong> <?php echo $d['mobile']; ?></p>
                                <p class="card-text mb-1"><strong>Email:</strong> <?php echo $d['email']; ?></p>
                                <p class="card-text"><strong>Address:</strong> <?php echo $d['address']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="mt-3">
            <a href="help_seeker.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Help Seeker
            </a>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="requestModalLabel">Raise Blood Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Required Blood Group</label>
                            <select name="req_blood_group" class="form-select" required>
                                <option value="">-- Select --</option>
                                <?php foreach (["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"] as $group): ?>
                                    <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Location / Hospital</label>
                            <input type="text" name="location" required class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Note (optional)</label>
                            <textarea name="note" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="raise_request" class="btn btn-danger w-100">Notify Donors</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>