<?php
include 'db_connect.php';
include 'session.php';
redirectIfNotLoggedIn();

$categories = [
  'Deluxe' => [
    'title' => 'Deluxe Rooms',
    'summary' => 'Elegant rooms for focused stays, quick escapes, and weekend comfort.',
    'accent' => 'teal',
    'amenities' => ['Queen bed', 'City view', 'Work desk']
  ],
  'Suite' => [
    'title' => 'Suite Rooms',
    'summary' => 'Spacious suites with refined details for longer and more relaxed stays.',
    'accent' => 'gold',
    'amenities' => ['Lounge area', 'Premium bath', 'Panoramic view']
  ],
  'Family' => [
    'title' => 'Family Rooms',
    'summary' => 'Larger rooms designed for shared stays without giving up comfort.',
    'accent' => 'rose',
    'amenities' => ['Multiple beds', 'Extra storage', 'Family space']
  ]
];

$roomsByCategory = [];
$totalAvailable = 0;
$lowestPrice = null;

foreach ($categories as $type => $details) {
  $stmt = $conn->prepare("SELECT * FROM rooms WHERE status = 'available' AND room_type = ? ORDER BY price, room_number");
  $stmt->bind_param("s", $type);
  $stmt->execute();
  $result = $stmt->get_result();
  $roomsByCategory[$type] = [];

  while ($room = $result->fetch_assoc()) {
    $roomsByCategory[$type][] = $room;
    $totalAvailable++;
    $price = (float) $room['price'];
    $lowestPrice = ($lowestPrice === null || $price < $lowestPrice) ? $price : $lowestPrice;
  }
}

function roomImage($photo) {
  return (!empty($photo) && file_exists($photo)) ? $photo : 'images/labas.jpg';
}

function roomBlurb($type) {
  $descriptions = [
    'Deluxe' => 'A polished private room with warm finishes, practical amenities, and a calm atmosphere for restful stays.',
    'Suite' => 'A premium room with extra space, elevated styling, and a more refined stay experience.',
    'Family' => 'A comfortable shared room with practical space for groups and families traveling together.'
  ];

  return $descriptions[$type] ?? 'A comfortable room prepared for a convenient stay at Mariposa Inn.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Available Rooms | Mariposa Inn</title>
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
      position: relative;
      min-height: 430px;
      display: flex;
      align-items: flex-end;
      overflow: hidden;
      background:
        linear-gradient(90deg, rgba(246, 241, 232, 0.96) 0%, rgba(246, 241, 232, 0.82) 48%, rgba(246, 241, 232, 0.22) 100%),
        url('images/labas.jpg') center/cover;
    }

    .hero-inner {
      width: min(1180px, calc(100% - 36px));
      margin: 0 auto;
      padding: 72px 0 58px;
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
      max-width: 760px;
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

    .hero-stats {
      display: grid;
      grid-template-columns: repeat(3, minmax(150px, 1fr));
      gap: 12px;
      width: min(660px, 100%);
      margin-top: 28px;
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
      font-size: 0.8rem;
      font-weight: 700;
      text-transform: uppercase;
    }

    .stat-tile strong {
      display: block;
      margin-top: 8px;
      font-size: 1.75rem;
      line-height: 1;
    }

    .catalog {
      width: min(1180px, calc(100% - 36px));
      margin: 0 auto;
      padding: 34px 0 70px;
    }

    .category-nav {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 30px;
    }

    .category-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      min-height: 42px;
      padding: 9px 14px;
      border: 1px solid var(--line);
      border-radius: 8px;
      color: var(--text);
      background: var(--surface);
      font-size: 0.92rem;
      font-weight: 700;
      text-decoration: none;
      transition: 0.2s ease;
    }

    .category-chip:hover {
      color: var(--ink);
      background: var(--gold);
      border-color: var(--gold);
      transform: translateY(-2px);
    }

    .room-section {
      padding-top: 24px;
      margin-top: 18px;
      border-top: 1px solid var(--line);
      scroll-margin-top: 96px;
    }

    .section-heading {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 20px;
      margin-bottom: 18px;
    }

    .section-heading h2 {
      margin: 0;
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.8rem, 3vw, 2.6rem);
      letter-spacing: 0;
    }

    .section-heading p {
      max-width: 580px;
      margin: 8px 0 0;
      color: var(--muted);
      line-height: 1.65;
    }

    .room-count {
      flex: 0 0 auto;
      color: var(--muted);
      font-size: 0.9rem;
      font-weight: 700;
    }

    .rooms-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 18px;
    }

    .room-card {
      display: flex;
      flex-direction: column;
      min-height: 100%;
      border: 1px solid var(--line);
      border-radius: 8px;
      overflow: hidden;
      background: var(--surface);
      box-shadow: 0 18px 36px rgba(68, 52, 27, 0.1);
      transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .room-card:hover {
      transform: translateY(-4px);
      border-color: rgba(242, 193, 78, 0.45);
      box-shadow: 0 22px 46px rgba(68, 52, 27, 0.16);
    }

    .room-media {
      position: relative;
      aspect-ratio: 16 / 10;
      overflow: hidden;
      background: #e2d7c8;
    }

    .room-media img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.3s ease;
    }

    .room-card:hover .room-media img {
      transform: scale(1.04);
    }

    .badge-available {
      position: absolute;
      top: 12px;
      left: 12px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      min-height: 30px;
      padding: 6px 10px;
      border: 1px solid rgba(184, 137, 45, 0.32);
      border-radius: 999px;
      color: #5f4214;
      background: rgba(255, 250, 242, 0.92);
      font-size: 0.78rem;
      font-weight: 800;
      box-shadow: 0 8px 18px rgba(68, 52, 27, 0.12);
      backdrop-filter: blur(10px);
    }

    .room-body {
      display: flex;
      flex: 1;
      flex-direction: column;
      padding: 18px;
    }

    .room-title-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 14px;
    }

    .room-card h3 {
      margin: 0;
      font-size: 1.08rem;
      font-weight: 800;
    }

    .room-number {
      color: var(--muted);
      font-size: 0.82rem;
      font-weight: 700;
      white-space: nowrap;
    }

    .room-copy {
      margin: 12px 0 14px;
      color: var(--muted);
      font-size: 0.92rem;
      line-height: 1.6;
    }

    .amenity-list {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      padding: 0;
      margin: 0 0 18px;
      list-style: none;
    }

    .amenity-list li {
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

    .room-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-top: auto;
      padding-top: 16px;
      border-top: 1px solid var(--line);
    }

    .price-label {
      display: block;
      color: var(--muted);
      font-size: 0.76rem;
      font-weight: 700;
      text-transform: uppercase;
    }

    .price {
      display: block;
      margin-top: 4px;
      color: var(--gold);
      font-size: 1.2rem;
      font-weight: 800;
      white-space: nowrap;
    }

    .room-actions {
      display: flex;
      gap: 8px;
    }

    .icon-btn,
    .reserve-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 40px;
      border-radius: 8px;
      font-weight: 800;
      text-decoration: none;
      transition: 0.2s ease;
    }

    .icon-btn {
      width: 42px;
      border: 1px solid var(--line);
      color: var(--text);
      background: var(--surface-2);
    }

    .icon-btn:hover {
      color: var(--gold);
      border-color: rgba(242, 193, 78, 0.5);
    }

    .reserve-btn {
      padding: 9px 13px;
      border: 0;
      color: var(--ink);
      background: var(--gold);
    }

    .reserve-btn:hover {
      color: var(--ink);
      filter: brightness(1.05);
      transform: translateY(-1px);
    }

    .empty-panel {
      padding: 22px;
      border: 1px dashed var(--line);
      border-radius: 8px;
      color: var(--muted);
      background: rgba(255, 255, 255, 0.03);
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

    .modal-room-image {
      width: 100%;
      aspect-ratio: 16 / 9;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid var(--line);
    }

    .detail-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 12px;
      margin: 18px 0;
    }

    .detail-box {
      padding: 12px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(184, 137, 45, 0.08);
    }

    .detail-box span {
      display: block;
      color: var(--muted);
      font-size: 0.76rem;
      font-weight: 800;
      text-transform: uppercase;
    }

    .detail-box strong {
      display: block;
      margin-top: 5px;
    }

    @media (max-width: 1040px) {
      .rooms-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 780px) {
      .site-nav,
      .site-nav nav,
      .site-nav ul,
      .nav-actions,
      .section-heading,
      .room-footer {
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

      .hero-stats,
      .detail-grid {
        grid-template-columns: 1fr;
      }

      .rooms-grid {
        grid-template-columns: 1fr;
      }

      .room-actions {
        width: 100%;
      }

      .reserve-btn {
        flex: 1;
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

  <section class="hero">
    <div class="hero-inner">
      <div class="eyebrow"><i class="bi bi-calendar2-check"></i> Rooms Ready For Booking</div>
      <h1>Choose a stay that feels easy from the first click.</h1>
      <p>Browse available rooms by category, compare rates, and reserve the option that fits your trip.</p>

      <div class="hero-stats">
        <div class="stat-tile">
          <span>Available Rooms</span>
          <strong><?= $totalAvailable; ?></strong>
        </div>
        <div class="stat-tile">
          <span>Room Categories</span>
          <strong><?= count($categories); ?></strong>
        </div>
        <div class="stat-tile">
          <span>Rates From</span>
          <strong><?= $lowestPrice === null ? 'N/A' : 'PHP ' . number_format($lowestPrice, 0); ?></strong>
        </div>
      </div>
    </div>
  </section>

  <main class="catalog">
    <div class="category-nav" aria-label="Room categories">
      <?php foreach ($categories as $type => $details): ?>
        <a href="#<?= strtolower($type); ?>" class="category-chip">
          <i class="bi bi-door-open"></i>
          <?= htmlspecialchars($details['title']); ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php foreach ($categories as $type => $details): ?>
      <?php $rooms = $roomsByCategory[$type]; ?>
      <section class="room-section" id="<?= strtolower($type); ?>">
        <div class="section-heading">
          <div>
            <h2><?= htmlspecialchars($details['title']); ?></h2>
            <p><?= htmlspecialchars($details['summary']); ?></p>
          </div>
          <span class="room-count"><?= count($rooms); ?> available</span>
        </div>

        <?php if (count($rooms) > 0): ?>
          <div class="rooms-grid">
            <?php foreach ($rooms as $row): ?>
              <?php
                $image = roomImage($row['photo']);
                $modalId = 'roomModal' . (int) $row['room_id'];
              ?>
              <article class="room-card">
                <div class="room-media">
                  <img src="<?= htmlspecialchars($image); ?>" alt="<?= htmlspecialchars($row['room_type'] . ' room ' . $row['room_number']); ?>">
                  <span class="badge-available"><i class="bi bi-check2-circle"></i> Available</span>
                </div>
                <div class="room-body">
                  <div class="room-title-row">
                    <h3><?= htmlspecialchars($row['room_type']); ?> Room</h3>
                    <span class="room-number"><?= htmlspecialchars($row['room_number']); ?></span>
                  </div>

                  <p class="room-copy"><?= htmlspecialchars(roomBlurb($row['room_type'])); ?></p>

                  <ul class="amenity-list">
                    <?php foreach ($details['amenities'] as $amenity): ?>
                      <li><i class="bi bi-check-lg"></i><?= htmlspecialchars($amenity); ?></li>
                    <?php endforeach; ?>
                  </ul>

                  <div class="room-footer">
                    <div>
                      <span class="price-label">Per night</span>
                      <strong class="price">PHP <?= number_format((float) $row['price'], 2); ?></strong>
                    </div>
                    <div class="room-actions">
                      <button class="icon-btn" type="button" data-bs-toggle="modal" data-bs-target="#<?= $modalId; ?>" aria-label="View room details">
                        <i class="bi bi-eye"></i>
                      </button>
                      <a href="reserve.php?room_id=<?= (int) $row['room_id']; ?>" class="reserve-btn">
                        <i class="bi bi-calendar-plus me-2"></i>Reserve
                      </a>
                    </div>
                  </div>
                </div>
              </article>

              <div class="modal fade" id="<?= $modalId; ?>" tabindex="-1" aria-labelledby="<?= $modalId; ?>Label" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <div>
                        <h5 class="modal-title" id="<?= $modalId; ?>Label"><?= htmlspecialchars($row['room_type']); ?> Room <?= htmlspecialchars($row['room_number']); ?></h5>
                          <span class="text-muted">Mariposa Inn</span>
                      </div>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <img src="<?= htmlspecialchars($image); ?>" class="modal-room-image" alt="<?= htmlspecialchars($row['room_type'] . ' room preview'); ?>">

                      <div class="detail-grid">
                        <div class="detail-box">
                          <span>Room Type</span>
                          <strong><?= htmlspecialchars($row['room_type']); ?></strong>
                        </div>
                        <div class="detail-box">
                          <span>Room No.</span>
                          <strong><?= htmlspecialchars($row['room_number']); ?></strong>
                        </div>
                        <div class="detail-box">
                          <span>Rate</span>
                          <strong>PHP <?= number_format((float) $row['price'], 2); ?></strong>
                        </div>
                      </div>

                      <p class="mb-0 text-muted"><?= htmlspecialchars(roomBlurb($row['room_type'])); ?></p>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Close</button>
                      <a href="reserve.php?room_id=<?= (int) $row['room_id']; ?>" class="reserve-btn">
                        <i class="bi bi-calendar-plus me-2"></i>Reserve Room
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-panel">No <?= strtolower(htmlspecialchars($type)); ?> rooms are available right now.</div>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>
  </main>

</body>
</html>
