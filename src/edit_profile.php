<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// Fetch current data
$stmt = $conn->prepare("SELECT name, username, email, mobile, blood_group, address, pincode, dob, roles FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $username, $email, $mobile, $blood_group, $address, $pincode, $dob, $roles);
$stmt->fetch();
$stmt->close();

$role_list = explode(",", $roles);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $blood_group = $_POST['blood_group'];
    $address = trim($_POST['address']);
    $pincode = trim($_POST['pincode']);
    $dob = $_POST['dob'];
    $roles = isset($_POST['roles']) ? implode(",", $_POST['roles']) : "";

    // Optional password update
    $updatePassword = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    if ($updatePassword) {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, mobile=?, blood_group=?, address=?, pincode=?, dob=?, password=?, roles=? WHERE id=?");
        $stmt->bind_param("sssssssssi", $name, $email, $mobile, $blood_group, $address, $pincode, $dob, $updatePassword, $roles, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, mobile=?, blood_group=?, address=?, pincode=?, dob=?, roles=? WHERE id=?");
        $stmt->bind_param("ssssssssi", $name, $email, $mobile, $blood_group, $address, $pincode, $dob, $roles, $user_id);
    }

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
    } else {
        $errors[] = "Update failed. Please try again.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile - DonateX</title>
    <link rel="icon" href="../assets/logo.jpg">
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
    </style>
</head>

<body>

    <div class="container form-container">
        <div class="mb-3 text-end">
            <a href="dashboard.php" class="btn btn-outline-danger">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="text-center mb-4">

            <img src="../assets/DonateX.png" alt="DonateX Logo" class="logo mb-2">

            <h2 class="form-title">Edit Your Profile</h2>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e)
                    echo "<div>$e</div>"; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Name</label>
                    <input type="text" name="name" required class="form-control"
                        value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="col-md-6">
                    <label>Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($username); ?>" disabled>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" required class="form-control"
                        value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="col-md-6">
                    <label>Mobile</label>
                    <input type="text" name="mobile" required class="form-control"
                        value="<?php echo htmlspecialchars($mobile); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Blood Group</label>
                    <select name="blood_group" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php
                        $groups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
                        foreach ($groups as $group) {
                            $selected = $blood_group === $group ? "selected" : "";
                            echo "<option value='$group' $selected>$group</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" required class="form-control" value="<?php echo $dob; ?>">
                </div>
            </div>

            <div class="mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control" required
                    rows="2"><?php echo htmlspecialchars($address); ?></textarea>
            </div>

            <div class="mb-3">
                <label>Pincode</label>
                <input type="text" name="pincode" required class="form-control"
                    value="<?php echo htmlspecialchars($pincode); ?>">
            </div>

            <div class="mb-3">
                <label>Update Password (optional)</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep unchanged">
            </div>

            <label class="mb-2">Update Roles</label><br>
            <?php
            $all_roles = ["Volunteer", "Donor", "Blood Donor", "Help Seeker"];
            foreach ($all_roles as $role) {
                $checked = in_array($role, $role_list) ? "checked" : "";
                echo <<<HTML
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="roles[]" value="$role" $checked>
                <label class="form-check-label">$role</label>
            </div>
            HTML;
            }
            ?>

            <button type="submit" class="btn btn-danger w-100 mt-4">Update Profile</button>
        </form>

    </div>

</body>

</html>