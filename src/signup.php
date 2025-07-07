<?php
session_start();

// Debugging: Show errors during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

$errors = [];
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $name        = trim($_POST['name'] ?? '');
    $username    = trim($_POST['username'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $mobile      = trim($_POST['mobile'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $pincode     = trim($_POST['pincode'] ?? '');
    $dob         = trim($_POST['dob'] ?? '');
    $password    = $_POST['password'] ?? '';
    $roles       = isset($_POST['roles']) ? implode(',', $_POST['roles']) : '';

    // Basic validations
    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        $errors[] = "Please fill in all required fields.";
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username already taken. Please choose another.";
    }
    $stmt->close();

    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users 
            (name, username, email, mobile, blood_group, address, pincode, dob, password, roles) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", 
            $name, $username, $email, $mobile, $blood_group, $address, $pincode, $dob, $hashed_password, $roles);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Add welcome notification
            $welcome_msg = "Welcome to DonateX, $name!";
            $notif = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
            $notif->bind_param("is", $user_id, $welcome_msg);
            $notif->execute();
            $notif->close();

            // Log user in
            $_SESSION['user_id'] = $user_id;

            // Redirect
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Error while registering. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .signup-box {
            max-width: 650px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container signup-box">
    <h2 class="text-danger mb-4 text-center">Create Your DonateX Account</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $err): ?>
                <div><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Name *</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Username *</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Blood Group</label>
            <select name="blood_group" class="form-select">
                <option value="">-- Select --</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Pincode</label>
            <input type="text" name="pincode" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Roles</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="roles[]" value="Volunteer"> Volunteer
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="roles[]" value="Donor"> Donor
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="roles[]" value="Blood Donor"> Blood Donor
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="roles[]" value="Help Seeker"> Help Seeker
            </div>
        </div>

        <button type="submit" class="btn btn-danger w-100">Sign Up</button>
    </form>
</div>

</body>
</html>