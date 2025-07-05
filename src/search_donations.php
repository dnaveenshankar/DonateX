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
$search = $_GET['search'] ?? '';

// Handle claim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_donation'])) {
    $donation_id = $_POST['donation_id'];

    $check = $conn->prepare("SELECT claimed_by FROM donations WHERE id = ? AND status != 'available'");
    $check->bind_param("i", $donation_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        $update = $conn->prepare("UPDATE donations SET claimed_by = ?, status = 'claimed', claimed_at = NOW() WHERE id = ?");
        $update->bind_param("ii", $user_id, $donation_id);
        $update->execute();
        $update->close();
        $success = "Claim submitted successfully. Please wait for approval.";
    } else {
        $error = "This donation has already been claimed.";
    }
    $check->close();
}

// Search logic
if ($search) {
    $stmt = $conn->prepare("SELECT d.*, u.name AS donor_name, u.id AS user_id FROM donations d JOIN users u ON d.user_id = u.id WHERE 
        (d.category LIKE CONCAT('%', ?, '%') OR d.description LIKE CONCAT('%', ?, '%') OR u.name LIKE CONCAT('%', ?, '%')) 
        ORDER BY d.created_at DESC");
    $stmt->bind_param("sss", $search, $search, $search);
} else {
    $stmt = $conn->prepare("SELECT d.*, u.name AS donor_name, u.id AS user_id FROM donations d JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC");
}
$stmt->execute();
$result = $stmt->get_result();
$donations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch my claims
$my_claims_stmt = $conn->prepare("SELECT d.*, u.name AS donor_name FROM donations d JOIN users u ON d.user_id = u.id WHERE d.claimed_by = ?");
$my_claims_stmt->bind_param("i", $user_id);
$my_claims_stmt->execute();
$claimed_result = $my_claims_stmt->get_result();
$my_claims = $claimed_result->fetch_all(MYSQLI_ASSOC);
$my_claims_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Donations - DonateX</title>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-danger">Search Donations</h3>
        <div>
            <button class="btn btn-outline-dark me-2" data-bs-toggle="modal" data-bs-target="#myClaimsModal">
                <i class="bi bi-eye"></i> My Claims
            </button>
            <a href="help_seeker.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form class="input-group mb-4" method="GET">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Search by category, donor name, or description...">
        <button class="btn btn-dark">Search</button>
    </form>

    <div class="row">
        <?php if (empty($donations)): ?>
            <p class="text-muted">No donations found.</p>
        <?php else: ?>
            <?php foreach ($donations as $d): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border border-danger-subtle">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($d['category']); ?> Donation</h5>
                            <p class="card-text mb-1 d-flex justify-content-between align-items-center">
                                <strong>Donor:</strong> <?php echo htmlspecialchars($d['donor_name']); ?>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#donorModal<?php echo $d['user_id']; ?>">
                                    <i class="bi bi-person-circle"></i>
                                </button>
                            </p>
                            <p class="card-text mb-1"><strong>Description:</strong> <?php echo htmlspecialchars($d['description']); ?></p>
                            <p class="card-text mb-1"><strong>Quantity:</strong> <?php echo htmlspecialchars($d['quantity']); ?></p>
                            <p class="card-text text-muted"><small>Posted on: <?php echo date("d M Y, h:i A", strtotime($d['created_at'])); ?></small></p>

                            <?php if ($d['status'] === 'available'): ?>
                                <form method="POST">
                                    <input type="hidden" name="donation_id" value="<?php echo $d['id']; ?>">
                                    <button type="submit" name="claim_donation" class="btn btn-danger btn-sm mt-2">Claim</button>
                                </form>
                            <?php elseif ($d['claimed_by'] == $user_id): ?>
                                <span class="badge bg-warning mt-2">You Claimed (Pending Approval)</span>
                            <?php else: ?>
                                <span class="badge bg-secondary mt-2">Claimed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- My Claims Modal -->
<div class="modal fade" id="myClaimsModal" tabindex="-1" aria-labelledby="myClaimsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">My Claimed Donations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($my_claims)): ?>
                    <p class="text-muted">You haven’t claimed any donations yet.</p>
                <?php else: ?>
                    <?php foreach ($my_claims as $c): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $c['category']; ?></h5>
                                <p class="mb-1"><strong>Donor:</strong> <?php echo htmlspecialchars($c['donor_name']); ?></p>
                                <p class="mb-1"><strong>Description:</strong> <?php echo htmlspecialchars($c['description']); ?></p>
                                <p class="mb-1"><strong>Status:</strong>
                                    <?php
                                    if ($c['status'] === 'claimed') echo '<span class="badge bg-warning">Pending</span>';
                                    elseif ($c['status'] === 'approved') echo '<span class="badge bg-success">Approved</span>';
                                    elseif ($c['status'] === 'rejected') echo '<span class="badge bg-danger">Rejected</span>';
                                    else echo '<span class="badge bg-secondary">Available</span>';
                                    ?>
                                </p>
                                <small class="text-muted">Claimed at: <?php echo $c['claimed_at'] ? date("d M Y, h:i A", strtotime($c['claimed_at'])) : '—'; ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Donor Profile Modals -->
<?php
$donor_ids = [];
foreach ($donations as $d) {
    if (!in_array($d['user_id'], $donor_ids)) {
        $donor_ids[] = $d['user_id'];

        $donor_stmt = $conn->prepare("SELECT name, email, mobile, address, pincode, blood_group, dob FROM users WHERE id = ?");
        $donor_stmt->bind_param("i", $d['user_id']);
        $donor_stmt->execute();
        $donor_stmt->bind_result($name, $email, $mobile, $address, $pincode, $blood_group, $dob);
        $donor_stmt->fetch();
        $donor_stmt->close();
?>
<div class="modal fade" id="donorModal<?php echo $d['user_id']; ?>" tabindex="-1" aria-labelledby="donorModalLabel<?php echo $d['user_id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Donor Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($mobile); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></p>
                <p><strong>Pincode:</strong> <?php echo htmlspecialchars($pincode); ?></p>
                <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($blood_group); ?></p>
                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($dob); ?></p>
            </div>
        </div>
    </div>
</div>
<?php
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
