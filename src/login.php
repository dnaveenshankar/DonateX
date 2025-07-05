<?php
session_start();

require_once 'db.php'; 
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $db_username, $db_password);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $db_username;
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Invalid password!";
        }
    } else {
        $errors[] = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - DonateX</title>
    <link rel="icon" type="image/png" href="../assets/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fff5f5;
            font-family: 'Segoe UI', sans-serif;
        }
        .form-container {
            max-width: 600px;
            margin: 60px auto;
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
        .signup-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container form-container">
    <div class="text-center mb-4">
        <img src="../assets/DonateX.png" alt="DonateX Logo" class="logo mb-2">
        <h2 class="form-title">Login to DonateX</h2>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" required class="form-control">
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" required class="form-control">
        </div>

        <button type="submit" class="btn btn-danger w-100">Login</button>
    </form>

    <div class="signup-link mt-3">
        Don't have an account? <a href="signup.php" class="text-danger fw-bold">Register here</a>
    </div>

</div>

</body>
</html>
