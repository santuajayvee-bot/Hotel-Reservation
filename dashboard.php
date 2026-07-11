<?php
include 'db_connect.php';
include 'session.php';
redirectIfNotLoggedIn();
$current_page = basename($_SERVER['PHP_SELF']);
if (isClient() && $current_page != 'index.php') {
    header("Location: index.php");
    exit();
}

$adminName = htmlspecialchars($_SESSION['name']);

$roomStats = $conn->query("
    SELECT
        COUNT(*) AS total_rooms,
        SUM(status = 'available') AS available_rooms,
        SUM(status = 'reserved') AS reserved_rooms
    FROM rooms
")->fetch_assoc();

$reservationStats = $conn->query("
    SELECT
        COUNT(*) AS total_reservations,
        SUM(status = 'active') AS active_reservations,
        SUM(status = 'canceled') AS canceled_reservations,
        COALESCE(SUM(penalty), 0) AS penalties
    FROM reservations
")->fetch_assoc();

$recentReservations = $conn->query("
    SELECT r.transaction_id, r.check_in, r.check_out, r.status,
           u.name AS customer, rooms.room_type, rooms.room_number
    FROM reservations r
    JOIN users u ON r.user_id = u.user_id
    JOIN rooms ON r.room_id = rooms.room_id
    ORDER BY r.created_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1115;
            --panel: #181b21;
            --panel-2: #20242c;
            --line: rgba(255, 255, 255, 0.1);
            --text: #f7f3ea;
            --muted: #aeb6c3;
            --gold: #f2c14e;
            --teal: #35b7a6;
            --blue: #5aa7ff;
            --rose: #e85d75;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                linear-gradient(135deg, rgba(15, 17, 21, 0.94), rgba(15, 17, 21, 0.86)),
                url('images/labas.jpg') center/cover fixed;
            color: var(--text);
            font-family: 'Poppins', Arial, sans-serif;
        }

        .admin-shell {
            min-height: 100vh;
            padding: 28px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin: 0 auto 24px;
            max-width: 1180px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(242, 193, 78, 0.42);
            border-radius: 8px;
            background: rgba(242, 193, 78, 0.12);
            color: var(--gold);
            font-size: 24px;
        }

        .brand h1 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            letter-spacing: 0;
        }

        .brand p {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(232, 93, 117, 0.45);
            border-radius: 8px;
            padding: 10px 14px;
            color: #fff;
            background: rgba(232, 93, 117, 0.13);
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .logout-btn:hover {
            background: var(--rose);
            color: #fff;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(300px, 0.8fr);
            gap: 20px;
            max-width: 1180px;
            margin: 0 auto;
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(24, 27, 33, 0.9);
            box-shadow: 0 18px 44px rgba(0, 0, 0, 0.32);
        }

        .hero-panel {
            padding: clamp(22px, 4vw, 34px);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            color: var(--gold);
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .hero-panel h2 {
            margin: 0;
            max-width: 760px;
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 4vw, 3.5rem);
            line-height: 1;
            letter-spacing: 0;
        }

        .hero-panel p {
            margin: 16px 0 26px;
            max-width: 680px;
            color: var(--muted);
            line-height: 1.7;
        }

        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 46px;
            border: 0;
            border-radius: 8px;
            padding: 11px 16px;
            color: #07100f;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, filter 0.2s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
            color: #07100f;
        }

        .action-rooms {
            background: var(--teal);
        }

        .action-reservations {
            background: var(--gold);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .stat-card {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
            background: rgba(32, 36, 44, 0.86);
        }

        .stat-card span {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 0.84rem;
        }

        .stat-card strong {
            display: block;
            margin-top: 10px;
            font-size: clamp(1.7rem, 3vw, 2.35rem);
            line-height: 1;
        }

        .side-panel {
            padding: 22px;
        }

        .side-panel h3,
        .activity-panel h3 {
            margin: 0 0 16px;
            font-size: 1.02rem;
            font-weight: 700;
        }

        .availability-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            padding: 13px 0;
            border-bottom: 1px solid var(--line);
            color: var(--muted);
        }

        .availability-row:last-child {
            border-bottom: 0;
        }

        .availability-row strong {
            color: var(--text);
        }

        .activity-panel {
            grid-column: 1 / -1;
            padding: 22px;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text);
            --bs-table-border-color: var(--line);
            margin: 0;
        }

        .table thead th {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            min-width: 82px;
            justify-content: center;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .status-active {
            background: rgba(53, 183, 166, 0.16);
            color: #69dfcf;
        }

        .status-canceled {
            background: rgba(232, 93, 117, 0.16);
            color: #ff8ca0;
        }

        .empty-state {
            margin: 0;
            padding: 18px;
            border: 1px dashed var(--line);
            border-radius: 8px;
            color: var(--muted);
            background: rgba(255, 255, 255, 0.03);
        }

        @media (max-width: 980px) {
            .dashboard-grid,
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .hero-panel,
            .side-panel {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 640px) {
            .admin-shell {
                padding: 18px;
            }

            .topbar,
            .quick-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .dashboard-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .action-btn,
            .logout-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="admin-shell">
        <header class="topbar">
            <div class="brand">
                <div class="brand-mark"><i class="bi bi-buildings"></i></div>
                <div>
                    <h1>Mariposa Inn</h1>
                    <p>Hotel reservation administration</p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </header>

        <section class="dashboard-grid">
            <div class="panel hero-panel">
                <div class="eyebrow"><i class="bi bi-stars"></i> Admin Dashboard</div>
                <h2>Welcome back, <?= $adminName; ?></h2>
                <p>Monitor room availability, reservation activity, and day-to-day hotel operations from one clean workspace.</p>

                <div class="quick-actions">
                    <a href="manage_rooms.php" class="action-btn action-rooms">
                        <i class="bi bi-door-open"></i>
                        <span>Manage Rooms</span>
                    </a>
                    <a href="manage_reservation.php" class="action-btn action-reservations">
                        <i class="bi bi-calendar-check"></i>
                        <span>Manage Reservations</span>
                    </a>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <span><i class="bi bi-door-closed"></i> Total Rooms</span>
                        <strong><?= (int) $roomStats['total_rooms']; ?></strong>
                    </div>
                    <div class="stat-card">
                        <span><i class="bi bi-check2-circle"></i> Available</span>
                        <strong><?= (int) $roomStats['available_rooms']; ?></strong>
                    </div>
                    <div class="stat-card">
                        <span><i class="bi bi-bookmark-check"></i> Reserved</span>
                        <strong><?= (int) $roomStats['reserved_rooms']; ?></strong>
                    </div>
                    <div class="stat-card">
                        <span><i class="bi bi-receipt"></i> Active Bookings</span>
                        <strong><?= (int) $reservationStats['active_reservations']; ?></strong>
                    </div>
                </div>
            </div>

            <aside class="panel side-panel">
                <h3>Operations Summary</h3>
                <div class="availability-row">
                    <span>Total reservations</span>
                    <strong><?= (int) $reservationStats['total_reservations']; ?></strong>
                </div>
                <div class="availability-row">
                    <span>Canceled reservations</span>
                    <strong><?= (int) $reservationStats['canceled_reservations']; ?></strong>
                </div>
                <div class="availability-row">
                    <span>Penalty balance</span>
                    <strong>PHP <?= number_format((float) $reservationStats['penalties'], 2); ?></strong>
                </div>
                <div class="availability-row">
                    <span>Room readiness</span>
                    <strong><?= (int) $roomStats['available_rooms']; ?>/<?= (int) $roomStats['total_rooms']; ?></strong>
                </div>
            </aside>

            <section class="panel activity-panel">
                <h3>Recent Reservations</h3>
                <?php if ($recentReservations && $recentReservations->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Transaction</th>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Dates</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recentReservations->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['transaction_id']); ?></td>
                                        <td><?= htmlspecialchars($row['customer']); ?></td>
                                        <td><?= htmlspecialchars($row['room_type'] . ' - ' . $row['room_number']); ?></td>
                                        <td><?= htmlspecialchars($row['check_in'] . ' to ' . $row['check_out']); ?></td>
                                        <td>
                                            <span class="status-pill status-<?= htmlspecialchars($row['status']); ?>">
                                                <?= ucfirst(htmlspecialchars($row['status'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="empty-state">No reservations yet. New bookings will appear here once guests reserve a room.</p>
                <?php endif; ?>
            </section>
        </section>
    </main>
</body>
</html>
