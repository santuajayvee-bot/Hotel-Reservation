<?php
include 'db_connect.php';
include 'session.php';
redirectIfNotLoggedIn();

if (isAdmin()) {
  header("Location: dashboard.php");
  exit();
}

$guestName = htmlspecialchars($_SESSION['name']);
$userId = (int) $_SESSION['user_id'];

$roomStats = $conn->query("
  SELECT
    COUNT(*) AS total_rooms,
    SUM(status = 'available') AS available_rooms,
    COUNT(DISTINCT room_type) AS room_categories,
    MIN(CASE WHEN status = 'available' THEN price END) AS starting_rate
  FROM rooms
")->fetch_assoc();

$stmt = $conn->prepare("SELECT COUNT(*) AS active_reservations FROM reservations WHERE user_id = ? AND status = 'active'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$activeReservations = $stmt->get_result()->fetch_assoc()['active_reservations'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mariposa Inn | Guest Home</title>

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

    html {
      scroll-behavior: smooth;
    }

    body {
      margin: 0;
      background: var(--page);
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

    .hero {
      position: relative;
      min-height: calc(100vh - 74px);
      display: flex;
      align-items: center;
      overflow: hidden;
      background: #efe7d8;
    }

    .hero-video {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: 0;
    }

    .hero::after {
      content: "";
      position: absolute;
      inset: 0;
      z-index: 1;
      background: linear-gradient(90deg, rgba(246, 241, 232, 0.96) 0%, rgba(246, 241, 232, 0.84) 48%, rgba(246, 241, 232, 0.28) 100%);
    }

    .hero-inner {
      position: relative;
      z-index: 2;
      width: min(1180px, calc(100% - 36px));
      margin: 0 auto;
      padding: 64px 0;
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

    .hero h1 {
      max-width: 780px;
      margin: 0;
      font-family: 'Playfair Display', serif;
      font-size: clamp(2.8rem, 7vw, 6rem);
      line-height: 0.94;
      letter-spacing: 0;
    }

    .hero p {
      max-width: 620px;
      margin: 22px 0 0;
      color: var(--muted);
      font-size: 1.02rem;
      line-height: 1.8;
    }

    .hero-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 28px;
    }

    .primary-btn,
    .secondary-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-height: 46px;
      padding: 11px 16px;
      border-radius: 8px;
      font-weight: 800;
      text-decoration: none;
      transition: 0.2s ease;
    }

    .primary-btn {
      color: #fff;
      background: var(--teal);
    }

    .primary-btn:hover {
      color: #fff;
      filter: brightness(1.06);
      transform: translateY(-1px);
    }

    .secondary-btn {
      border: 1px solid var(--line);
      color: var(--ink);
      background: rgba(255, 250, 242, 0.82);
    }

    .secondary-btn:hover {
      color: var(--ink);
      border-color: rgba(184, 137, 45, 0.42);
      background: var(--surface);
    }

    .hero-stats {
      display: grid;
      grid-template-columns: repeat(4, minmax(140px, 1fr));
      gap: 12px;
      width: min(850px, 100%);
      margin-top: 36px;
    }

    .stat-tile {
      min-height: 92px;
      padding: 16px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 250, 242, 0.82);
      box-shadow: 0 14px 30px rgba(68, 52, 27, 0.08);
    }

    .stat-tile span {
      display: block;
      color: var(--muted);
      font-size: 0.78rem;
      font-weight: 800;
      text-transform: uppercase;
    }

    .stat-tile strong {
      display: block;
      margin-top: 8px;
      font-size: 1.72rem;
      line-height: 1;
    }

    .content-wrap {
      width: min(1180px, calc(100% - 36px));
      margin: 0 auto;
      padding: 60px 0 72px;
    }

    .section-heading {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 22px;
      margin-bottom: 22px;
    }

    .section-heading h2 {
      margin: 0;
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.9rem, 4vw, 3rem);
      letter-spacing: 0;
    }

    .section-heading p {
      max-width: 620px;
      margin: 9px 0 0;
      color: var(--muted);
      line-height: 1.7;
    }

    .experience-grid {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 18px;
      margin-bottom: 44px;
    }

    .feature-photo {
      min-height: 430px;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid var(--line);
      background: url('images/home.jpg') center/cover;
      box-shadow: 0 18px 40px rgba(68, 52, 27, 0.12);
    }

    .feature-list {
      display: grid;
      gap: 14px;
    }

    .feature-card,
    .step-card {
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--surface-strong);
      box-shadow: 0 14px 32px rgba(68, 52, 27, 0.08);
    }

    .feature-card {
      display: grid;
      grid-template-columns: 52px minmax(0, 1fr);
      gap: 14px;
      align-items: start;
      padding: 18px;
    }

    .feature-icon {
      width: 52px;
      height: 52px;
      display: grid;
      place-items: center;
      border-radius: 8px;
      color: var(--teal);
      background: rgba(33, 124, 115, 0.1);
      font-size: 1.4rem;
    }

    .feature-card h3,
    .step-card h3 {
      margin: 0;
      font-size: 1.08rem;
      font-weight: 800;
    }

    .feature-card p,
    .step-card p {
      margin: 8px 0 0;
      color: var(--muted);
      line-height: 1.65;
    }

    .steps-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 18px;
    }

    .step-card {
      padding: 20px;
    }

    .step-number {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      margin-bottom: 18px;
      border-radius: 8px;
      color: #fff;
      background: var(--gold);
      font-weight: 900;
    }

    .footer {
      border-top: 1px solid var(--line);
      background: var(--surface);
      color: var(--muted);
      padding: 22px 18px;
      text-align: center;
      font-size: 0.92rem;
      font-weight: 600;
    }

    @media (max-width: 980px) {
      .hero-stats,
      .steps-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .experience-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 760px) {
      .site-nav,
      .site-nav nav,
      .site-nav ul,
      .nav-actions,
      .section-heading {
        align-items: stretch;
        flex-direction: column;
      }

      .site-nav nav,
      .site-nav ul,
      .nav-actions,
      .nav-link,
      .booking-link,
      .logout-link,
      .primary-btn,
      .secondary-btn {
        width: 100%;
      }

      .nav-link,
      .booking-link,
      .logout-link,
      .primary-btn,
      .secondary-btn {
        justify-content: center;
      }

      .hero {
        min-height: auto;
      }

      .hero-stats,
      .steps-grid {
        grid-template-columns: 1fr;
      }

      .feature-photo {
        min-height: 280px;
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
        <li><a href="index.php" class="nav-link active">Home</a></li>
        <li><a href="facilities.php" class="nav-link">Facilities</a></li>
        <li><a href="available_rooms.php" class="nav-link">Available Rooms</a></li>
        <li><a href="my_reservations.php" class="nav-link">My Reservations</a></li>
      </ul>
      <div class="nav-actions">
        <a href="available_rooms.php" class="booking-link"><i class="bi bi-calendar-plus"></i> Book Now</a>
        <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </div>
    </nav>
  </header>

  <section class="hero">
    <video class="hero-video" autoplay muted loop playsinline poster="images/labas.jpg">
      <source src="images/vid.mp4" type="video/mp4">
    </video>
    <div class="hero-inner">
      <div class="eyebrow"><i class="bi bi-stars"></i> Welcome, <?= $guestName; ?></div>
      <h1>A lighter, calmer way to book your stay.</h1>
      <p>Plan your Mariposa Inn visit with a clean guest dashboard, easy room browsing, and organized reservation tracking.</p>

      <div class="hero-actions">
        <a href="available_rooms.php" class="primary-btn"><i class="bi bi-door-open"></i> Browse Rooms</a>
        <a href="my_reservations.php" class="secondary-btn"><i class="bi bi-calendar2-check"></i> View Reservations</a>
      </div>

      <div class="hero-stats">
        <div class="stat-tile">
          <span>Available Rooms</span>
          <strong><?= (int) $roomStats['available_rooms']; ?></strong>
        </div>
        <div class="stat-tile">
          <span>Room Types</span>
          <strong><?= (int) $roomStats['room_categories']; ?></strong>
        </div>
        <div class="stat-tile">
          <span>Rates From</span>
          <strong><?= $roomStats['starting_rate'] === null ? 'N/A' : 'PHP ' . number_format((float) $roomStats['starting_rate'], 0); ?></strong>
        </div>
        <div class="stat-tile">
          <span>Your Active Stays</span>
          <strong><?= (int) $activeReservations; ?></strong>
        </div>
      </div>
    </div>
  </section>

  <main class="content-wrap">
    <section>
      <div class="section-heading">
        <div>
          <h2>Designed Around Your Stay</h2>
          <p>Everything a guest needs is now easier to scan, compare, and manage.</p>
        </div>
        <a href="facilities.php" class="secondary-btn"><i class="bi bi-grid"></i> Explore Facilities</a>
      </div>

      <div class="experience-grid">
        <div class="feature-photo" aria-label="Mariposa Inn guest space"></div>
        <div class="feature-list">
          <article class="feature-card">
            <div class="feature-icon"><i class="bi bi-house-heart"></i></div>
            <div>
              <h3>Comfortable Rooms</h3>
              <p>Compare room categories, prices, and availability with a cleaner booking experience.</p>
            </div>
          </article>
          <article class="feature-card">
            <div class="feature-icon"><i class="bi bi-calendar-check"></i></div>
            <div>
              <h3>Simple Reservations</h3>
              <p>Keep track of active and canceled bookings without digging through a crowded table.</p>
            </div>
          </article>
          <article class="feature-card">
            <div class="feature-icon"><i class="bi bi-cup-hot"></i></div>
            <div>
              <h3>Guest Facilities</h3>
              <p>Preview the on-site amenities that make the stay more complete and convenient.</p>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section>
      <div class="section-heading">
        <div>
          <h2>How Booking Works</h2>
          <p>A straightforward flow for choosing, confirming, and managing your room.</p>
        </div>
      </div>

      <div class="steps-grid">
        <article class="step-card">
          <span class="step-number">1</span>
          <h3>Choose a Room</h3>
          <p>Open the available rooms page and pick from Deluxe, Suite, or Family options.</p>
        </article>
        <article class="step-card">
          <span class="step-number">2</span>
          <h3>Select Dates</h3>
          <p>Confirm your check-in and check-out dates from the reservation form.</p>
        </article>
        <article class="step-card">
          <span class="step-number">3</span>
          <h3>Track Your Stay</h3>
          <p>Review booking details and cancellations through your reservations dashboard.</p>
        </article>
      </div>
    </section>
  </main>

  <footer class="footer">
    &copy; 2026 Mariposa Inn. Guest reservation portfolio system.
  </footer>
</body>
</html>
