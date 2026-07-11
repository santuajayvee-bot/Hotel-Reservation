<?php
include 'db_connect.php';
include 'session.php';
include 'functions.php';
redirectIfNotLoggedIn();

if (isAdmin()) header("Location: dashboard.php");

if (!isset($_GET['room_id'])) header("Location: index.php");

$room_id = intval($_GET['room_id']);
$room = $conn->query("SELECT * FROM rooms WHERE room_id=$room_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $count = $conn->query("SELECT COUNT(*) as total FROM reservations")->fetch_assoc()['total'] + 1;

    $transaction_id = generateTransactionID($_SESSION['name'], $check_in, $room['room_type'], $count);
    $user_id = $_SESSION['user_id'];

    $conn->query("INSERT INTO reservations (transaction_id, user_id, room_id, check_in, check_out) VALUES ('$transaction_id', $user_id, $room_id, '$check_in', '$check_out')");
    $conn->query("UPDATE rooms SET status='reserved' WHERE room_id=$room_id");

    header("Location: my_reservations.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reserve Room</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Dark Theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dark-theme.css">

    <style>
        body {
            background-color: #121212;
            color: #fff;
        }
        .form-control {
            background-color: #222;
            border-color: #555;
            color: #fff;
        }
        .form-control:focus {
            border-color: #ffd700;
            background-color: #222;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.25);
        }
        label {
            font-weight: 600;
        }
        .btn-primary {
            background-color: #ffd700;
            border: none;
            color: #000;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #e6c200;
        }
        .btn-secondary {
            color: #fff;
            border-color: #ccc;
        }
        .btn-secondary:hover {
            background-color: #333;
            border-color: #999;
        }
    </style>
</head>
<body>
    <div class="container mt-5" style="max-width: 600px;">
        <h2 class="text-warning mb-4">Reserve <?= $room['room_type']; ?> - <?= $room['room_number']; ?></h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Check-In Date:</label>
                <input type="date" name="check_in" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Check-Out Date:</label>
                <input type="date" name="check_out" class="form-control" required>
            </div>
            <div class="d-flex justify-content-between">
                <button class="btn btn-primary">Confirm Reservation</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
