<?php
include 'db_connect.php';
include 'session.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (isset($_SESSION['role'])) {
    if (isAdmin() && $current_page != 'dashboard.php') {
        header("Location: dashboard.php");
        exit();
    }
    if (isClient() && $current_page != 'index.php') {
        header("Location: index.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = md5($_POST['password']);
    $query = $conn->query("SELECT * FROM users WHERE email='$email' AND password='$password'");

    if ($query->num_rows == 1) {
        $user = $query->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        if (isAdmin()) {
            header("Location: dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "Invalid login credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        /* Hotel Background Image */
        body {
			background: url('images/labas.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Glassmorphism Card */
        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px 30px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            color: #fff;
        }

        .login-card h2 {
            font-family: 'Playfair Display', serif;
            color: #ffd700;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-label {
            color: #f1f1f1;
        }

        .btn-primary {
            background-color: #ffd700;
            border: none;
            color: #121212;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #e6c200;
            color: #000;
        }

        a {
            color: #ffd700;
        }

        a:hover {
            color: #f1c40f;
            text-decoration: underline;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.8);
            border: none;
            color: #fff;
        }
		
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Welcome to Mariposa Inn</h2>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <p class="mt-3 text-center">Don’t have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
