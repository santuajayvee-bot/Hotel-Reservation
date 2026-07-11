<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <style>
    .navbar {
      background-color: #111 !important;
      font-family: 'Open Sans', sans-serif;
    }

    .navbar-brand {
      font-family: 'Playfair Display', serif;
      font-size: 1.6rem;
      color: #ffd700 !important;
    }

    .nav-link {
      color: #fff !important;
      font-weight: 600;
      margin-right: 1rem;
      transition: color 0.3s;
    }

    .nav-link:hover {
      color: #ffd700 !important;
    }

    .btn-logout {
      background-color: #ffd700;
      border: none;
      color: #000;
      font-weight: 600;
      padding: 0.45rem 1rem;
      border-radius: 5px;
      transition: background 0.3s;
    }

    .btn-logout:hover {
      background-color: #e6c200;
      color: #000;
    }
  </style>

  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Mariposa Inn</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? ' active' : '' ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'available_rooms.php' ? ' active' : '' ?>" href="available_rooms.php">Available Rooms</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'my_reservations.php' ? ' active' : '' ?>" href="my_reservations.php">My Reservations</a>
        </li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="btn btn-logout" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
