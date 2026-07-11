<?php
include 'db_connect.php';
include 'session.php';
redirectIfNotLoggedIn();
if (!isAdmin()) header("Location: index.php");

if (isset($_POST['add'])) {
    $room_type = $conn->real_escape_string($_POST['room_type']);
    $room_number = $conn->real_escape_string($_POST['room_number']);
    $price = $conn->real_escape_string($_POST['price']);
    $conn->query("INSERT INTO rooms (room_type, room_number, price) VALUES ('$room_type', '$room_number', '$price')");
    header("Location: manage_rooms.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM rooms WHERE room_id=$id");
    header("Location: manage_rooms.php");
    exit();
}

$rooms = $conn->query("SELECT * FROM rooms ORDER BY room_number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Rooms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Manage Rooms</h2>
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="room_type" placeholder="Room Type" class="form-control" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="room_number" placeholder="Room Number" class="form-control" required>
        </div>
        <div class="col-md-3">
            <input type="number" name="price" placeholder="Price" class="form-control" required>
        </div>
        <div class="col-md-3">
            <button name="add" class="btn btn-success">Add Room</button>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Room Type</th>
                <th>Room Number</th>
                <th>Price (₱)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $rooms->fetch_assoc()): ?>
            <tr>
                <td><?= $row['room_id'] ?></td>
                <td><?= $row['room_type'] ?></td>
                <td><?= $row['room_number'] ?></td>
                <td><?= number_format($row['price'], 2) ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td>
                    <a href="?delete=<?= $row['room_id'] ?>" onclick="return confirm('Delete this room?')" class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</body>
</html>
