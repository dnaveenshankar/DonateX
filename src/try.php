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

// Fetch user info
$stmt = $conn->prepare("SELECT name, roles FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $roles);
$stmt->fetch();
$stmt->close();

$role_list = explode(",", $roles);

// Fetch notifications
$notif_query = $conn->prepare("SELECT id, message, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$notif_query->bind_param("i", $user_id);
$notif_query->execute();
$notif_result = $notif_query->get_result();
$notifications = $notif_result->fetch_all(MYSQLI_ASSOC);
$unread_count = count(array_filter($notifications, fn($n) => !$n['is_read']));

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle notification delete
if (isset($_POST['delete_notif'])) {
    $notif_id = $_POST['delete_notif'];
    $del = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $del->bind_param("ii", $notif_id, $user_id);
    $del->execute();
    header("Location: dashboard.php");
    exit();
}

// Mark as read
if (isset($_POST['read_notif'])) {
    $notif_id = $_POST['read_notif'];
    $update = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $update->bind_param("ii", $notif_id, $user_id);
    $update->execute();
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background: #fff5f5;
            font-family: 'Segoe UI', sans-serif;
        }
        .wrapper {
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dashboard-container {
            width: 100%;
            max-width: 960px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.1);
        }
        .dashboard-title {
            color: #dc3545;
            font-weight: bold;
        }
        .role-badge {
            margin-right: 10px;
        }
        .dashboard-btn {
            margin: 10px;
            border-radius: 0;
        }
        .logo {
            height: 50px;
        }
        footer {
            text-align: center;
            margin-top: 40px;
            color: #888;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="wrapper">
<div class="container dashboard-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <img src="../assets/DonateX.png" class="me-2 logo" alt="DonateX">
            <div>
                <h2 class="dashboard-title mb-0"><?php echo htmlspecialchars($name); ?></h2>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-dark position-relative" onclick="toggleNotif()">
                <i class="bi bi-bell"></i>
                <?php if ($unread_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $unread_count; ?>
                    </span>
                <?php endif; ?>
            </button>
            <form method="POST" onsubmit="return confirm('Are you sure you want to logout?');">
                <button type="submit" name="logout" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>
    </div>

    <div class="mb-3">
        <?php foreach ($role_list as $r): ?>
            <span class="badge bg-<?php
                echo trim($r) === 'Volunteer' ? 'primary' :
                    (trim($r) === 'Donor' ? 'success' :
                    (trim($r) === 'Blood Donor' ? 'danger' :
                    (trim($r) === 'Help Seeker' ? 'warning text-dark' : 'secondary'))); ?> role-badge">
                <?php echo trim($r); ?>
            </span>
        <?php endforeach; ?>
    </div>

    <!-- Notification Popup -->
    <div id="notif-popup" class="border rounded bg-white shadow p-3 mb-4" style="display:none;">
        <h5 class="mb-3">Notifications</h5>
        <?php if (empty($notifications)): ?>
            <p class="text-muted">No notifications yet.</p>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="alert alert-<?php echo $notif['is_read'] ? 'secondary' : 'warning'; ?> d-flex justify-content-between align-items-center">
                    <span><?php echo htmlspecialchars($notif['message']); ?></span>
                    <div class="d-flex gap-2">
                        <?php if (!$notif['is_read']): ?>
                        <form method="POST">
                            <input type="hidden" name="read_notif" value="<?php echo $notif['id']; ?>">
                            <button class="btn btn-sm btn-outline-success"><i class="bi bi-check"></i></button>
                        </form>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="delete_notif" value="<?php echo $notif['id']; ?>">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

   <!-- Dashboard Options -->
<div class="row text-center mt-4">
    <div class="col-md-6 col-lg-4">
        <a href="volunteer.php" class="btn btn-<?php echo in_array('Volunteer', $role_list) ? 'primary' : 'secondary disabled'; ?> dashboard-btn w-100">
            <i class="bi bi-people"></i> Volunteer
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="donor.php" class="btn btn-<?php echo in_array('Donor', $role_list) ? 'success' : 'secondary disabled'; ?> dashboard-btn w-100">
            <i class="bi bi-box"></i> Donor (Food & Things)
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="blood_donor.php" class="btn btn-<?php echo in_array('Blood Donor', $role_list) ? 'danger' : 'secondary disabled'; ?> dashboard-btn w-100">
            <i class="bi bi-droplet-half"></i> Blood Donor
        </a>
    </div>
    <div class="col-md-6 col-lg-4 mt-3">
        <a href="help_seeker.php" class="btn btn-<?php echo in_array('Help Seeker', $role_list) ? 'warning text-dark' : 'secondary disabled'; ?> dashboard-btn w-100">
            <i class="bi bi-person-lines-fill"></i> Help Seeker
        </a>
    </div>
    <div class="col-md-6 col-lg-4 mt-3">
        <a href="edit_profile.php" class="btn btn-dark dashboard-btn w-100">
            <i class="bi bi-gear"></i> Edit Profile
        </a>
    </div>
    <div class="col-md-6 col-lg-4 mt-3">
        <a href="reports.php" class="btn btn-info text-white dashboard-btn w-100">
            <i class="bi bi-bar-chart"></i> Reports
        </a>
    </div>
</div>


    <footer>
        &copy; <?php echo date('Y'); ?> DonateX. All rights reserved.
    </footer>
</div>
</div>

<script>
    function toggleNotif() {
        const popup = document.getElementById('notif-popup');
        popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
    }
</script>
</body>
</html>