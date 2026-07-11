<?php
function generateTransactionID($customerName, $checkInDate, $roomType, $count) {
    $initials = strtoupper(substr($customerName, 0, 2));
    $monthDay = strtoupper(date("M", strtotime($checkInDate))) . date("d", strtotime($checkInDate));
    $year = date("y", strtotime($checkInDate));
    $roomCode = strtoupper(substr($roomType, 0, 3));
    $serial = str_pad($count, 5, '0', STR_PAD_LEFT);

    return $initials . $monthDay . $year . '-' . $roomCode . $serial;
}

function calculatePenalty($cancelDate, $checkInDate, $price) {
    $diff = (strtotime($checkInDate) - strtotime($cancelDate)) / (60 * 60 * 24);
    if ($diff < 2) return $price * 0.20;
    elseif ($diff < 4) return $price * 0.15;
    elseif ($diff >= 5) return $price * 0.10;
    else return 0;
}
?>
