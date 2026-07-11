<?php
include 'session.php';
redirectIfNotLoggedIn();

if (isAdmin()) {
  header("Location: dashboard.php");
  exit();
}

$facilities = [
  [
    'name' => 'Lobby & Reception',
    'tagline' => 'A calm arrival experience with attentive front-desk support.',
    'image' => 'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg',
    'icon' => 'bi-buildings',
    'highlights' => ['Concierge support', 'Comfortable seating', 'Smooth check-in'],
    'description' => 'Settle in quickly with a welcoming reception area designed for relaxed arrivals, guest support, and easy coordination before heading to your room.'
  ],
  [
    'name' => 'Restaurant & Bar',
    'tagline' => 'Comfortable dining spaces for meals, coffee, and evening drinks.',
    'image' => 'https://images.pexels.com/photos/262047/pexels-photo-262047.jpeg',
    'icon' => 'bi-cup-hot',
    'highlights' => ['All-day dining', 'Relaxed seating', 'House beverages'],
    'description' => 'Enjoy well-prepared meals and drinks in an inviting dining area made for casual breakfasts, quiet dinners, and small gatherings.'
  ],
  [
    'name' => 'Fitness Center',
    'tagline' => 'Simple, practical workout space for keeping your routine on track.',
    'image' => 'https://images.pexels.com/photos/1954524/pexels-photo-1954524.jpeg',
    'icon' => 'bi-heart-pulse',
    'highlights' => ['Cardio equipment', 'Strength area', 'Daily access'],
    'description' => 'Stay active during your visit with essential fitness equipment and a clean space for cardio, stretching, and strength training.'
  ],
  [
    'name' => 'Swimming Pool',
    'tagline' => 'A refreshing pool area for quiet downtime and relaxed afternoons.',
    'image' => 'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg',
    'icon' => 'bi-water',
    'highlights' => ['Outdoor setting', 'Lounge area', 'Guest access'],
    'description' => 'Take a swim, unwind poolside, or enjoy a slower afternoon in a refreshing open-air leisure space.'
  ],
  [
    'name' => 'Spa & Wellness',
    'tagline' => 'A quiet wellness space for rest, recovery, and reset moments.',
    'image' => 'https://images.pexels.com/photos/275768/pexels-photo-275768.jpeg',
    'icon' => 'bi-flower1',
    'highlights' => ['Relaxation treatments', 'Private rooms', 'Wellness care'],
    'description' => 'Recharge with a soothing wellness experience prepared for guests who want a slower, more restorative stay.'
  ],
  [
    'name' => 'Meeting Rooms',
    'tagline' => 'Private spaces for small events, planning sessions, and business needs.',
    'image' => 'https://images.pexels.com/photos/1181395/pexels-photo-1181395.jpeg',
    'icon' => 'bi-people',
    'highlights' => ['Private rooms', 'Flexible setup', 'Business-ready'],
    'description' => 'Host focused meetings or small events in practical rooms arranged for presentations, discussions, and guest coordination.'
  ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Facilities | Mariposa Inn</title>

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
      --surface-2: #f1e8d9;
      --line: rgba(39, 43, 38, 0.12);
      --text: #1e211f;
      --muted: #6d746f;
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

    .site-nav nav {
      display: flex;
      align-items: center;
      gap: 18px;
    }

    .site-nav ul {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 4px;
      margin: 0;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.5);
      list-style: none;
    }

    .nav-link {
      display: inline-flex;
      align-items: center;
      min-height: 40px;
      padding: 8px 12px;
      border-radius: 8px;
      color: var(--muted);
      font-size: 0.92rem;
      font-weight: 600;
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
      font-weight: 700;
      text-decoration: none;
      transition: 0.2s ease;
    }

    .logout-link:hover {
      color: #fff;
      background: var(--rose);
    }

    .hero {
      min-height: 430px;
      display: flex;
      align-items: flex-end;
      background:
        linear-gradient(90deg, rgba(246, 241, 232, 0.96) 0%, rgba(246, 241, 232, 0.82) 52%, rgba(246, 241, 232, 0.22) 100%),
        url('images/home.jpg') center/cover;
    }

    .hero-inner {
      width: min(1180px, calc(100% - 36px));
      margin: 0 auto;
      padding: 74px 0 58px;
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
      max-width: 790px;
      margin: 0;
      font-family: 'Playfair Display', serif;
      font-size: clamp(2.45rem, 6vw, 5rem);
      line-height: 0.96;
      letter-spacing: 0;
    }

    .hero p {
      max-width: 620px;
      margin: 20px 0 0;
      color: var(--muted);
      line-height: 1.75;
    }

    .hero-points {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 28px;
    }

    .point-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      min-height: 40px;
      padding: 8px 12px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 250, 242, 0.82);
      box-shadow: 0 14px 30px rgba(68, 52, 27, 0.08);
      color: var(--text);
      font-size: 0.9rem;
      font-weight: 700;
    }

    .facilities-wrap {
      width: min(1180px, calc(100% - 36px));
      margin: 0 auto;
      padding: 46px 0 74px;
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

    .facility-count {
      flex: 0 0 auto;
      color: var(--muted);
      font-weight: 800;
    }

    .facility-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 18px;
    }

    .facility-card {
      min-height: 100%;
      border: 1px solid var(--line);
      border-radius: 8px;
      overflow: hidden;
      background: var(--surface);
      box-shadow: 0 18px 36px rgba(68, 52, 27, 0.1);
      transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .facility-card:hover {
      transform: translateY(-4px);
      border-color: rgba(242, 193, 78, 0.45);
      box-shadow: 0 22px 46px rgba(68, 52, 27, 0.16);
    }

    .facility-media {
      position: relative;
      aspect-ratio: 16 / 10;
      overflow: hidden;
      background: #e2d7c8;
    }

    .facility-media img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.3s ease;
    }

    .facility-card:hover .facility-media img {
      transform: scale(1.04);
    }

    .facility-icon {
      position: absolute;
      left: 14px;
      bottom: 14px;
      width: 46px;
      height: 46px;
      display: grid;
      place-items: center;
      border: 1px solid rgba(242, 193, 78, 0.5);
      border-radius: 8px;
      background: rgba(255, 250, 242, 0.84);
      color: var(--gold);
      font-size: 1.25rem;
      backdrop-filter: blur(10px);
    }

    .facility-body {
      padding: 18px;
    }

    .facility-body h3 {
      margin: 0;
      font-size: 1.08rem;
      font-weight: 800;
    }

    .facility-body p {
      min-height: 70px;
      margin: 11px 0 16px;
      color: var(--muted);
      font-size: 0.92rem;
      line-height: 1.6;
    }

    .highlight-list {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      padding: 0;
      margin: 0 0 18px;
      list-style: none;
    }

    .highlight-list li {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 8px;
      border: 1px solid var(--line);
      border-radius: 8px;
      color: var(--text);
      background: rgba(184, 137, 45, 0.08);
      font-size: 0.78rem;
      font-weight: 700;
    }

    .view-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      min-height: 42px;
      border: 0;
      border-radius: 8px;
      color: var(--ink);
      background: var(--gold);
      font-weight: 800;
      transition: 0.2s ease;
    }

    .view-btn:hover {
      filter: brightness(1.05);
      transform: translateY(-1px);
    }

    .modal-content {
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--surface);
      color: var(--text);
      overflow: hidden;
    }

    .modal-header,
    .modal-footer {
      border-color: var(--line);
    }

    .modal-image {
      width: 100%;
      aspect-ratio: 16 / 9;
      object-fit: cover;
      border: 1px solid var(--line);
      border-radius: 8px;
    }

    .modal-description {
      margin: 18px 0;
      color: var(--muted);
      line-height: 1.7;
    }

    .modal-highlight-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 10px;
    }

    .modal-highlight {
      padding: 12px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(184, 137, 45, 0.08);
      color: var(--text);
      font-weight: 700;
    }

    .btn-outline-light {
      border-radius: 8px;
      font-weight: 700;
    }

    @media (max-width: 1040px) {
      .facility-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 780px) {
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
      .nav-actions {
        width: 100%;
      }

      .site-nav ul {
        gap: 8px;
      }

      .nav-link,
      .booking-link,
      .logout-link {
        justify-content: center;
        width: 100%;
      }

      .facility-grid,
      .modal-highlight-grid {
        grid-template-columns: 1fr;
      }

      .facility-body p {
        min-height: 0;
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
        <li><a href="facilities.php" class="nav-link active">Facilities</a></li>
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
    <div class="hero-inner">
      <div class="eyebrow"><i class="bi bi-stars"></i> Guest Facilities</div>
      <h1>Comfortable spaces for every part of your stay.</h1>
      <p>From arrival to downtime, Mariposa Inn brings together practical amenities and relaxed spaces for guests who want a smoother stay.</p>

      <div class="hero-points">
        <span class="point-chip"><i class="bi bi-clock"></i> Daily guest access</span>
        <span class="point-chip"><i class="bi bi-shield-check"></i> Well-maintained spaces</span>
        <span class="point-chip"><i class="bi bi-geo-alt"></i> Convenient on-site amenities</span>
      </div>
    </div>
  </section>

  <main class="facilities-wrap">
    <div class="section-heading">
      <div>
        <h2>Explore Our Facilities</h2>
        <p>Browse the spaces available during your stay, from leisure amenities to practical business and wellness areas.</p>
      </div>
      <span class="facility-count"><?= count($facilities); ?> facilities</span>
    </div>

    <section class="facility-grid">
      <?php foreach ($facilities as $index => $facility): ?>
        <?php $modalId = 'facilityModal' . $index; ?>
        <article class="facility-card">
          <div class="facility-media">
            <img src="<?= htmlspecialchars($facility['image']); ?>" alt="<?= htmlspecialchars($facility['name']); ?>">
            <span class="facility-icon"><i class="bi <?= htmlspecialchars($facility['icon']); ?>"></i></span>
          </div>
          <div class="facility-body">
            <h3><?= htmlspecialchars($facility['name']); ?></h3>
            <p><?= htmlspecialchars($facility['tagline']); ?></p>

            <ul class="highlight-list">
              <?php foreach ($facility['highlights'] as $highlight): ?>
                <li><i class="bi bi-check-lg"></i><?= htmlspecialchars($highlight); ?></li>
              <?php endforeach; ?>
            </ul>

            <button class="view-btn" type="button" data-bs-toggle="modal" data-bs-target="#<?= $modalId; ?>">
              <i class="bi bi-eye"></i> View Details
            </button>
          </div>
        </article>

        <div class="modal fade" id="<?= $modalId; ?>" tabindex="-1" aria-labelledby="<?= $modalId; ?>Label" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <div>
                  <h5 class="modal-title" id="<?= $modalId; ?>Label"><?= htmlspecialchars($facility['name']); ?></h5>
                  <span class="text-muted">Mariposa Inn facility</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <img src="<?= htmlspecialchars($facility['image']); ?>" class="modal-image" alt="<?= htmlspecialchars($facility['name']); ?>">
                <p class="modal-description"><?= htmlspecialchars($facility['description']); ?></p>

                <div class="modal-highlight-grid">
                  <?php foreach ($facility['highlights'] as $highlight): ?>
                    <div class="modal-highlight"><i class="bi bi-check-lg me-2"></i><?= htmlspecialchars($highlight); ?></div>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </section>
  </main>

</body>
</html>
