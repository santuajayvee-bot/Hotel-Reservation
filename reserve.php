<?php
include 'db_connect.php';
include 'session.php';
include 'functions.php';
redirectIfNotLoggedIn();

if (isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_GET['room_id'])) {
    header("Location: index.php");
    exit();
}

$room_id = (int) $_GET['room_id'];
$room = $conn->query("SELECT * FROM rooms WHERE room_id = $room_id")->fetch_assoc();

if (!$room) {
    header("Location: available_rooms.php");
    exit();
}

$today = date("Y-m-d");
$error = "";

function reserveRoomImage($photo) {
    return (!empty($photo) && file_exists($photo)) ? $photo : 'images/labas.jpg';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $check_in = $_POST['check_in'] ?? "";
    $check_out = $_POST['check_out'] ?? "";

    if (!$check_in || !$check_out) {
        $error = "Please select both check-in and check-out dates.";
    } elseif ($check_in < $today) {
        $error = "Check-in date cannot be earlier than today.";
    } elseif ($check_out <= $check_in) {
        $error = "Check-out date must be after the check-in date.";
    } elseif ($room['status'] !== 'available') {
        $error = "This room is no longer available. Please choose another room.";
    } else {
        $count = $conn->query("SELECT COUNT(*) as total FROM reservations")->fetch_assoc()['total'] + 1;
        $transaction_id = generateTransactionID($_SESSION['name'], $check_in, $room['room_type'], $count);
        $user_id = (int) $_SESSION['user_id'];

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO reservations (transaction_id, user_id, room_id, check_in, check_out) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("siiss", $transaction_id, $user_id, $room_id, $check_in, $check_out);
            $stmt->execute();

            $update = $conn->prepare("UPDATE rooms SET status = 'reserved' WHERE room_id = ?");
            $update->bind_param("i", $room_id);
            $update->execute();

            $conn->commit();
            header("Location: my_reservations.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Unable to complete your reservation. Please try again.";
        }
    }
}

$image = reserveRoomImage($room['photo']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reserve <?= htmlspecialchars($room['room_type']); ?> Room | Mariposa Inn</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --page: #f6f1e8;
            --surface: #fffaf2;
            --surface-strong: #ffffff;
            --text: #1e211f;
            --muted: #6d746f;
            --line: rgba(39, 43, 38, 0.12);
            --gold: #b8892d;
            --teal: #217c73;
            --rose: #b94a5a;
            --ink: #1e211f;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                linear-gradient(180deg, rgba(246, 241, 232, 0.94), rgba(246, 241, 232, 1)),
                url('images/labas.jpg') center/cover fixed;
            color: var(--text);
            font-family: 'Poppins', Arial, sans-serif;
        }

        .site-nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            min-height: 82px;
            padding: 12px clamp(18px, 4vw, 48px);
            border-bottom: 1px solid var(--line);
            background: rgba(255, 250, 242, 0.94);
            box-shadow: 0 12px 30px rgba(68, 52, 27, 0.08);
            backdrop-filter: blur(18px);
        }

        .nav-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
            text-decoration: none;
        }

        .brand-mark {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(184, 137, 45, 0.26);
            border-radius: 8px;
            color: var(--gold);
            background: linear-gradient(135deg, #fffaf2, #efe3ce);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.7);
        }

        .logo {
            display: block;
            color: var(--gold);
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.28rem, 2vw, 1.65rem);
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0;
            white-space: nowrap;
        }

        .brand-subtitle {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .site-nav nav,
        .site-nav ul {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .site-nav nav {
            gap: 18px;
        }

        .site-nav ul {
            padding: 4px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.5);
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            min-height: 40px;
            padding: 8px 12px;
            border-radius: 8px;
            color: var(--muted);
            font-size: 0.92rem;
            font-weight: 700;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--ink);
            background: rgba(184, 137, 45, 0.12);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .booking-link,
        .submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            border: 0;
            border-radius: 8px;
            color: #fff;
            background: var(--teal);
            font-weight: 800;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .booking-link {
            padding: 8px 13px;
            font-size: 0.92rem;
            white-space: nowrap;
        }

        .submit-btn {
            width: 100%;
            padding: 11px 16px;
        }

        .booking-link:hover,
        .submit-btn:hover {
            color: #fff;
            filter: brightness(1.06);
            transform: translateY(-1px);
        }

        .logout-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 40px;
            padding: 8px 13px;
            border: 1px solid rgba(185, 74, 90, 0.28);
            border-radius: 8px;
            color: var(--rose);
            background: rgba(185, 74, 90, 0.08);
            font-size: 0.92rem;
            font-weight: 800;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .logout-link:hover {
            color: #fff;
            background: var(--rose);
        }

        .page-shell {
            width: min(1120px, calc(100% - 36px));
            margin: 0 auto;
            padding: 44px 0 70px;
        }

        .booking-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
            gap: 22px;
            align-items: stretch;
        }

        .room-preview,
        .booking-panel {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: var(--surface-strong);
            box-shadow: 0 18px 40px rgba(68, 52, 27, 0.11);
        }

        .room-image {
            position: relative;
            min-height: 390px;
            background: #e2d7c8;
        }

        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .room-badge {
            position: absolute;
            left: 16px;
            top: 16px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 32px;
            padding: 7px 11px;
            border: 1px solid rgba(184, 137, 45, 0.32);
            border-radius: 999px;
            color: #5f4214;
            background: rgba(255, 250, 242, 0.92);
            font-size: 0.78rem;
            font-weight: 800;
            box-shadow: 0 8px 18px rgba(68, 52, 27, 0.12);
            backdrop-filter: blur(10px);
        }

        .room-info {
            padding: 22px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            color: var(--gold);
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.1rem, 4vw, 3.4rem);
            line-height: 1;
            letter-spacing: 0;
        }

        .room-description {
            margin: 14px 0 0;
            color: var(--muted);
            line-height: 1.7;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        .detail-box {
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fbf6ed;
        }

        .detail-box span {
            display: block;
            color: var(--muted);
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .detail-box strong {
            display: block;
            margin-top: 5px;
        }

        .booking-panel {
            padding: clamp(22px, 4vw, 32px);
        }

        .booking-panel h2 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 900;
        }

        .booking-panel p {
            margin: 8px 0 22px;
            color: var(--muted);
            line-height: 1.65;
        }

        .form-label {
            color: var(--ink);
            font-weight: 800;
        }

        .form-control {
            min-height: 48px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fffdf8;
            color: var(--text);
            font-weight: 700;
        }

        .form-control:focus {
            border-color: rgba(33, 124, 115, 0.52);
            box-shadow: 0 0 0 0.22rem rgba(33, 124, 115, 0.12);
        }

        .form-text {
            color: var(--muted);
            font-weight: 600;
        }

        .alert {
            border-radius: 8px;
            font-weight: 700;
        }

        .secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 42px;
            padding: 10px 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--ink);
            background: var(--surface);
            font-weight: 800;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .secondary-btn:hover {
            color: var(--ink);
            border-color: rgba(184, 137, 45, 0.42);
            background: #fffdf8;
        }

        .policy-note {
            margin-top: 18px;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(184, 137, 45, 0.08);
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.6;
        }

        @media (max-width: 980px) {
            .booking-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .site-nav,
            .site-nav nav,
            .site-nav ul,
            .nav-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .site-nav nav,
            .site-nav ul,
            .nav-actions,
            .nav-link,
            .booking-link,
            .logout-link {
                width: 100%;
            }

            .nav-link,
            .booking-link,
            .logout-link {
                justify-content: center;
            }

            .room-image {
                min-height: 260px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="site-nav">
        <a href="index.php" class="nav-brand" aria-label="Mariposa Inn home">
            <span class="brand-mark"><i class="bi bi-buildings"></i></span>
            <span>
                <span class="logo">Mariposa Inn</span>
                <span class="brand-subtitle">Guest Reservation</span>
            </span>
        </a>
        <nav>
            <ul>
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="facilities.php" class="nav-link">Facilities</a></li>
                <li><a href="available_rooms.php" class="nav-link active">Available Rooms</a></li>
                <li><a href="my_reservations.php" class="nav-link">My Reservations</a></li>
            </ul>
            <div class="nav-actions">
                <a href="available_rooms.php" class="booking-link"><i class="bi bi-calendar-plus"></i> Book Now</a>
                <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </nav>
    </header>

    <main class="page-shell">
        <section class="booking-layout">
            <article class="room-preview">
                <div class="room-image">
                    <img src="<?= htmlspecialchars($image); ?>" alt="<?= htmlspecialchars($room['room_type'] . ' room ' . $room['room_number']); ?>">
                    <span class="room-badge"><i class="bi bi-check2-circle"></i><?= ucfirst(htmlspecialchars($room['status'])); ?></span>
                </div>
                <div class="room-info">
                    <div class="eyebrow"><i class="bi bi-door-open"></i> Room Selection</div>
                    <h1><?= htmlspecialchars($room['room_type']); ?> Room <?= htmlspecialchars($room['room_number']); ?></h1>
                    <p class="room-description">Confirm your stay dates for this room. Once submitted, the room will be marked as reserved and your booking will appear in your reservations dashboard.</p>

                    <div class="detail-grid">
                        <div class="detail-box">
                            <span>Room Type</span>
                            <strong><?= htmlspecialchars($room['room_type']); ?></strong>
                        </div>
                        <div class="detail-box">
                            <span>Room No.</span>
                            <strong><?= htmlspecialchars($room['room_number']); ?></strong>
                        </div>
                        <div class="detail-box">
                            <span>Rate</span>
                            <strong>PHP <?= number_format((float) $room['price'], 2); ?></strong>
                        </div>
                    </div>
                </div>
            </article>

            <aside class="booking-panel">
                <div class="eyebrow"><i class="bi bi-calendar2-check"></i> Booking Details</div>
                <h2>Choose your stay dates</h2>
                <p>Select a check-in and check-out date to complete your reservation.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label" for="check_in">Check-in date</label>
                        <input type="date" id="check_in" name="check_in" class="form-control" min="<?= $today; ?>" value="<?= htmlspecialchars($_POST['check_in'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="check_out">Check-out date</label>
                        <input type="date" id="check_out" name="check_out" class="form-control" min="<?= $today; ?>" value="<?= htmlspecialchars($_POST['check_out'] ?? ''); ?>" required>
                        <div class="form-text">Check-out should be at least one day after check-in.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="submit-btn"><i class="bi bi-check2-circle"></i> Confirm Reservation</button>
                        <a href="available_rooms.php" class="secondary-btn"><i class="bi bi-arrow-left"></i> Back to Rooms</a>
                    </div>
                </form>

                <div class="policy-note">
                    <strong>Cancellation note:</strong> penalties may apply depending on how close the cancellation date is to your check-in date.
                </div>
            </aside>
        </section>
    </main>
</body>
</html>
