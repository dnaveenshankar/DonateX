<?php
session_start();

require_once 'db.php'; 
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $blood_group = $_POST['blood_group'];
    $address = trim($_POST['address']);
    $pincode = trim($_POST['pincode']);
    $dob = $_POST['dob'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $roles = isset($_POST['roles']) ? implode(",", $_POST['roles']) : "";

    // Check if username already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $errors[] = "Username already exists!";
    } else {
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, mobile, blood_group, address, pincode, dob, password, roles) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $name, $username, $email, $mobile, $blood_group, $address, $pincode, $dob, $password, $roles);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Insert welcome message
            $message = "🎉 Welcome to DonateX, $name! Thank you for joining us in making a difference.";
            $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notif->bind_param("is", $user_id, $message);
            $notif->execute();

            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Error during registration.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - DonateX</title>
    <link rel="icon" type="image/png" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fff5f5;
            font-family: 'Segoe UI', sans-serif;
        }
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.1);
        }
        .form-title {
            color: #dc3545;
            font-weight: bold;
        }
        .logo {
            height: 50px;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container form-container">
    <div class="text-center mb-4">
        <img src="../assets/DonateX.png" alt="DonateX Logo" class="logo mb-2">
        <h2 class="form-title">Create Your DonateX Account</h2>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Name</label>
                <input type="text" name="name" required class="form-control">
            </div>
            <div class="col-md-3">
                <label>Username</label>
                <input type="text" name="username" required class="form-control">
            </div>
            <div class="col-md-3">
                <label>Password</label>
                <input type="password" name="password" required class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" required class="form-control">
            </div>
            <div class="col-md-6">
                <label>Mobile</label>
                <input type="text" name="mobile" required class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Blood Group</label>
                <select name="blood_group" required class="form-select">
                    <option value="">-- Select --</option>
                    <option value="A+">A+</option>
                    <option value="A-">A−</option>
                    <option value="B+">B+</option>
                    <option value="B-">B−</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB−</option>
                    <option value="O+">O+</option>
                    <option value="O-">O−</option>
                </select>
            </div>
            <div class="col-md-6">
                <label>Date of Birth</label>
                <input type="date" name="dob" required class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control" rows="2" required></textarea>
        </div>

        <div class="mb-3">
            <label>Pincode</label>
            <input type="text" name="pincode" required class="form-control">
        </div>

        <label class="mb-2">What do you want to be?</label><br>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" value="Volunteer">
            <label class="form-check-label">Volunteer</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" value="Donor">
            <label class="form-check-label">Donor (Food and Things)</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" value="Blood Donor">
            <label class="form-check-label">Blood Donor</label>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="roles[]" value="Help Seeker">
            <label class="form-check-label">Help Seeker</label>
        </div>

        <button type="submit" class="btn btn-danger w-100">Register</button>
    </form>

    <div class="login-link mt-3">
        Already have an account? <a href="login.php" class="text-danger fw-bold">Login here</a>
    </div>
</div>

</body>
</html>
