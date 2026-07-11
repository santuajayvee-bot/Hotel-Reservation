<?php
include 'db_connect.php';
include 'session.php';
redirectIfNotLoggedIn();

if (isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$guestName = htmlspecialchars($_SESSION['name']);

$stmt = $conn->prepare("
    SELECT r.*, rooms.room_type, rooms.room_number, rooms.photo, rooms.price
    FROM reservations r
    JOIN rooms ON r.room_id = rooms.room_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$reservations = [];
$activeCount = 0;
$canceledCount = 0;
$totalPenalty = 0;

while ($row = $result->fetch_assoc()) {
    $reservations[] = $row;
    if ($row['status'] === 'active') {
        $activeCount++;
    }
    if ($row['status'] === 'canceled') {
        $canceledCount++;
    }
    $totalPenalty += (float) $row['penalty'];
}

function reservationImage($photo) {
    return (!empty($photo) && file_exists($photo)) ? $photo : 'images/labas.jpg';
}

function formatDateLabel($date) {
    return date("M d, Y", strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Reservations | Mariposa Inn</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        .booking-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 8px 13px;
            border-radius: 8px;
            color: #fff;
            background: var(--teal);
            font-size: 0.92rem;
            font-weight: 800;
            text-decoration: none;
            transition: 0.2s ease;
            white-space: nowrap;
        }

        .booking-link:hover {
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
            width: min(1180px, calc(100% - 36px));
            margin: 0 auto;
            padding: 44px 0 70px;
        }

        .hero-panel {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(280px, 0.65fr);
            gap: 22px;
            align-items: stretch;
            margin-bottom: 24px;
        }

        .hero-copy,
        .summary-card {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(255, 250, 242, 0.88);
            box-shadow: 0 18px 40px rgba(68, 52, 27, 0.11);
        }

        .hero-copy {
            padding: clamp(24px, 4vw, 38px);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            color: var(--gold);
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .hero-copy h1 {
            max-width: 760px;
            margin: 0;
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 5vw, 4.2rem);
            line-height: 1;
            letter-spacing: 0;
        }

        .hero-copy p {
            max-width: 660px;
            margin: 18px 0 0;
            color: var(--muted);
            line-height: 1.75;
        }

        .summary-card {
            padding: 22px;
        }

        .summary-card h2 {
            margin: 0 0 18px;
            font-size: 1rem;
            font-weight: 800;
        }

        .summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 13px 0;
            border-bottom: 1px solid var(--line);
            color: var(--muted);
            font-weight: 700;
        }

        .summary-row:last-child {
            border-bottom: 0;
        }

        .summary-row strong {
            color: var(--text);
            font-size: 1.08rem;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin: 28px 0 18px;
        }

        .toolbar h2 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.75rem, 3vw, 2.55rem);
            letter-spacing: 0;
        }

        .book-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 15px;
            border: 0;
            border-radius: 8px;
            color: #fff;
            background: var(--teal);
            font-weight: 800;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .book-btn:hover {
            color: #fff;
            filter: brightness(1.06);
            transform: translateY(-1px);
        }

        .reservations-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .reservation-card {
            display: grid;
            grid-template-columns: 190px minmax(0, 1fr);
            min-height: 230px;
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: var(--surface-strong);
            box-shadow: 0 16px 34px rgba(68, 52, 27, 0.1);
        }

        .reservation-image {
            position: relative;
            min-height: 100%;
            background: #ddd;
        }

        .reservation-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .status-pill {
            position: absolute;
            left: 12px;
            top: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 30px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 800;
        }

        .status-active {
            color: #063a34;
            background: #98ded5;
        }

        .status-canceled {
            color: #6f1e2c;
            background: #ffd5dc;
        }

        .reservation-body {
            display: flex;
            flex-direction: column;
            padding: 18px;
        }

        .reservation-body h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .transaction {
            margin-top: 5px;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 700;
            word-break: break-word;
        }

        .reservation-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin: 16px 0;
        }

        .meta-box {
            padding: 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fbf6ed;
        }

        .meta-box span {
            display: block;
            color: var(--muted);
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .meta-box strong {
            display: block;
            margin-top: 4px;
            font-size: 0.92rem;
        }

        .reservation-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid var(--line);
        }

        .penalty {
            color: var(--muted);
            font-size: 0.86rem;
            font-weight: 700;
        }

        .cancel-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 38px;
            padding: 8px 12px;
            border-radius: 8px;
            color: #fff;
            background: var(--rose);
            font-size: 0.86rem;
            font-weight: 800;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .cancel-btn:hover {
            color: #fff;
            filter: brightness(1.06);
            transform: translateY(-1px);
        }

        .empty-state {
            display: grid;
            place-items: center;
            min-height: 300px;
            border: 1px dashed var(--line);
            border-radius: 8px;
            background: rgba(255, 250, 242, 0.78);
            text-align: center;
            padding: 34px;
        }

        .empty-state i {
            color: var(--gold);
            font-size: 2.4rem;
        }

        .empty-state h3 {
            margin: 14px 0 8px;
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
        }

        .empty-state p {
            max-width: 520px;
            margin: 0 auto 18px;
            color: var(--muted);
        }

        @media (max-width: 1040px) {
            .hero-panel,
            .reservations-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .site-nav,
            .site-nav nav,
            .site-nav ul,
            .nav-actions,
            .toolbar,
            .reservation-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .site-nav nav,
            .site-nav ul,
            .nav-actions,
            .booking-link,
            .book-btn,
            .logout-link,
            .nav-link {
                width: 100%;
            }

            .nav-link,
            .booking-link,
            .logout-link,
            .book-btn {
                justify-content: center;
            }

            .reservation-card {
                grid-template-columns: 1fr;
            }

            .reservation-image {
                min-height: 220px;
            }

            .reservation-meta {
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
                <li><a href="available_rooms.php" class="nav-link">Available Rooms</a></li>
                <li><a href="my_reservations.php" class="nav-link active">My Reservations</a></li>
            </ul>
            <div class="nav-actions">
                <a href="available_rooms.php" class="booking-link"><i class="bi bi-calendar-plus"></i> Book Now</a>
                <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </nav>
    </header>

    <main class="page-shell">
        <section class="hero-panel">
            <div class="hero-copy">
                <div class="eyebrow"><i class="bi bi-calendar2-check"></i> Reservation Center</div>
                <h1>Your stays, organized clearly.</h1>
                <p>Hello, <?= $guestName; ?>. Review your bookings, check your stay dates, and manage active reservations from one neat guest dashboard.</p>
            </div>

            <aside class="summary-card">
                <h2>Booking Summary</h2>
                <div class="summary-row">
                    <span>Total reservations</span>
                    <strong><?= count($reservations); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Active bookings</span>
                    <strong><?= $activeCount; ?></strong>
                </div>
                <div class="summary-row">
                    <span>Canceled bookings</span>
                    <strong><?= $canceledCount; ?></strong>
                </div>
                <div class="summary-row">
                    <span>Total penalties</span>
                    <strong>PHP <?= number_format($totalPenalty, 2); ?></strong>
                </div>
            </aside>
        </section>

        <div class="toolbar">
            <h2>Reservations</h2>
            <a href="available_rooms.php" class="book-btn"><i class="bi bi-plus-circle"></i> Book Another Room</a>
        </div>

        <?php if (count($reservations) > 0): ?>
            <section class="reservations-grid">
                <?php foreach ($reservations as $row): ?>
                    <?php $image = reservationImage($row['photo']); ?>
                    <article class="reservation-card">
                        <div class="reservation-image">
                            <img src="<?= htmlspecialchars($image); ?>" alt="<?= htmlspecialchars($row['room_type']); ?> room">
                            <span class="status-pill status-<?= htmlspecialchars($row['status']); ?>">
                                <i class="bi <?= $row['status'] === 'active' ? 'bi-check2-circle' : 'bi-x-circle'; ?>"></i>
                                <?= ucfirst(htmlspecialchars($row['status'])); ?>
                            </span>
                        </div>

                        <div class="reservation-body">
                            <h3><?= htmlspecialchars($row['room_type']); ?> Room <?= htmlspecialchars($row['room_number']); ?></h3>
                            <div class="transaction"><?= htmlspecialchars($row['transaction_id']); ?></div>

                            <div class="reservation-meta">
                                <div class="meta-box">
                                    <span>Check In</span>
                                    <strong><?= htmlspecialchars(formatDateLabel($row['check_in'])); ?></strong>
                                </div>
                                <div class="meta-box">
                                    <span>Check Out</span>
                                    <strong><?= htmlspecialchars(formatDateLabel($row['check_out'])); ?></strong>
                                </div>
                            </div>

                            <div class="reservation-footer">
                                <div class="penalty">Penalty: PHP <?= number_format((float) $row['penalty'], 2); ?></div>
                                <?php if ($row['status'] === 'active'): ?>
                                    <a href="cancel_reservation.php?id=<?= (int) $row['reservation_id']; ?>" class="cancel-btn" onclick="return confirm('Cancel this reservation?')">
                                        <i class="bi bi-x-lg"></i> Cancel
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted fw-bold">No action needed</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <section class="empty-state">
                <div>
                    <i class="bi bi-calendar-heart"></i>
                    <h3>No reservations yet</h3>
                    <p>Your confirmed stays will appear here. Start by choosing an available room that fits your trip.</p>
                    <a href="available_rooms.php" class="book-btn"><i class="bi bi-door-open"></i> Browse Available Rooms</a>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
