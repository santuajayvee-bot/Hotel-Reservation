<?php
include 'db_connect.php';
include 'session.php';
redirectIfNotLoggedIn();
if (!isAdmin()) header("Location: index.php");

$reservations = $conn->query("
    SELECT r.*, u.name as customer, rooms.room_number, rooms.room_type 
    FROM reservations r 
    JOIN users u ON r.user_id = u.user_id
    JOIN rooms ON r.room_id = rooms.room_id
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Manage Reservations</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Transaction ID</th>
                <th>Customer</th>
                <th>Room</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Penalty</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $reservations->fetch_assoc()): ?>
            <tr>
                <td><?= $row['reservation_id'] ?></td>
                <td><?= $row['transaction_id'] ?></td>
                <td><?= $row['customer'] ?></td>
                <td><?= $row['room_type'] ?> - <?= $row['room_number'] ?></td>
                <td><?= $row['check_in'] ?></td>
                <td><?= $row['check_out'] ?></td>
                <td>₱<?= number_format($row['penalty'], 2) ?></td>
                <td><?= ucfirst($row['status']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</body>
</html>
