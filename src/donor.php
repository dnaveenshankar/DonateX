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
$success = '';

// Handle new donation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donate'])) {
    $category = $_POST['category'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("INSERT INTO donations (user_id, category, description, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $category, $description, $quantity);
    $stmt->execute();
    $stmt->close();
    $success = "Donation posted successfully!";
}

// Handle claim approval
if (isset($_POST['approve'])) {
    $donation_id = $_POST['donation_id'];
    $stmt = $conn->prepare("UPDATE donations SET status = 'approved' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $donation_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $success = "Claim approved.";
}

// Handle claim rejection
if (isset($_POST['reject'])) {
    $donation_id = $_POST['donation_id'];
    $stmt = $conn->prepare("UPDATE donations SET status = 'available', claimed_by = NULL, claimed_at = NULL WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $donation_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $success = "Claim rejected.";
}

// Fetch donations made by current user
$query = $conn->prepare("SELECT d.*, u.name AS claimer_name, u.email, u.mobile, u.id AS claimer_id FROM donations d
    LEFT JOIN users u ON d.claimed_by = u.id
    WHERE d.user_id = ? ORDER BY d.created_at DESC");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$my_donations = $result->fetch_all(MYSQLI_ASSOC);
$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Donations - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f9f9f9;
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
        <h3 class="text-danger">Donate Items</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select" required>
                <option value="">-- Select --</option>
                <option value="Food">Food</option>
                <option value="Dress">Dress</option>
                <option value="Books">Books</option>
                <option value="Toys">Toys</option>
                <option value="Medicines">Medicines</option>
                <option value="Others">Others</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" required rows="2"></textarea>
        </div>
        <div class="mb-3">
            <label>Quantity / Count</label>
            <input type="text" name="quantity" class="form-control">
        </div>
        <button type="submit" name="donate" class="btn btn-danger w-100">Post Donation</button>
    </form>

    <h5 class="text-danger mt-5 mb-3">My Donations & Claims</h5>

    <?php if (empty($my_donations)): ?>
        <p class="text-muted">No donations yet.</p>
    <?php else: ?>
        <?php foreach ($my_donations as $donation): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="text-danger"><i class="bi bi-box-seam"></i> <?php echo $donation['category']; ?></h6>
                    <p><strong>Description:</strong> <?php echo $donation['description']; ?></p>
                    <p><strong>Quantity:</strong> <?php echo $donation['quantity']; ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-<?php
                            echo match($donation['status']) {
                                'available' => 'secondary',
                                'claimed' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            };
                        ?>"><?php echo ucfirst($donation['status']); ?></span>
                    </p>

                    <?php if ($donation['status'] === 'claimed'): ?>
                        <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-dark">Claimed by:</h6>
                                <p class="mb-1"><strong>Name:</strong> <?php echo $donation['claimer_name']; ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo $donation['email']; ?></p>
                                <p class="mb-1"><strong>Mobile:</strong> <?php echo $donation['mobile']; ?></p>
                            </div>
                            <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#profileModal<?php echo $donation['id']; ?>">
                                <i class="bi bi-person-circle fs-5"></i>
                            </button>
                        </div>

                        <!-- Profile Modal -->
                        <div class="modal fade" id="profileModal<?php echo $donation['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Claimer Profile</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                            $cid = $donation['claimer_id'];
                                            $pstmt = $conn->prepare("SELECT name, email, mobile, address, pincode, dob, blood_group FROM users WHERE id = ?");
                                            $pstmt->bind_param("i", $cid);
                                            $pstmt->execute();
                                            $pstmt->bind_result($name, $email, $mobile, $address, $pincode, $dob, $blood_group);
                                            $pstmt->fetch();
                                            $pstmt->close();
                                        ?>
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                                        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($mobile); ?></p>
                                        <p><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></p>
                                        <p><strong>Pincode:</strong> <?php echo htmlspecialchars($pincode); ?></p>
                                        <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($blood_group); ?></p>
                                        <p><strong>DOB:</strong> <?php echo htmlspecialchars($dob); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" class="d-flex gap-2 mt-2">
                            <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                            <button type="submit" name="approve" class="btn btn-sm btn-success">Approve</button>
                            <button type="submit" name="reject" class="btn btn-sm btn-outline-danger">Reject</button>
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
