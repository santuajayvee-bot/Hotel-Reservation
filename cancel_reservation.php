<?php
include 'db_connect.php';
include 'session.php';
include 'functions.php';
redirectIfNotLoggedIn();
if (isAdmin()) header("Location: dashboard.php");

if (!isset($_GET['id'])) header("Location: my_reservations.php");
$res_id = intval($_GET['id']);

$res = $conn->query("
    SELECT r.*, rooms.price 
    FROM reservations r
    JOIN rooms ON r.room_id = rooms.room_id
    WHERE reservation_id=$res_id
")->fetch_assoc();

$penalty = calculatePenalty(date("Y-m-d"), $res['check_in'], $res['price']);

$conn->query("UPDATE reservations SET status='canceled', penalty=$penalty WHERE reservation_id=$res_id");
$conn->query("UPDATE rooms SET status='available' WHERE room_id=" . $res['room_id']);

header("Location: my_reservations.php");
exit();
?>
