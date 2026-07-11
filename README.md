# Mariposa Inn Hotel Reservation

A PHP and MySQL hotel reservation system built for a school project and polished as a portfolio piece. The app includes client room browsing, reservation tracking, cancellation penalties, and an admin dashboard for rooms and bookings.

## Features

- Client login and registration
- Light themed guest landing page
- Available rooms catalog with room categories, images, rates, and booking actions
- Facilities page with professional cards and detail modals
- My Reservations dashboard with booking summary and reservation cards
- Admin dashboard with room and reservation stats
- Room management and reservation management pages
- Reservation cancellation with penalty calculation
- Reusable SQL seed file for local setup

## Tech Stack

- PHP
- MySQL / MariaDB
- Bootstrap 5
- Bootstrap Icons
- HTML / CSS

## Local Setup

1. Copy the project folder into your XAMPP `htdocs` directory.
2. Start Apache and MySQL from XAMPP.
3. Open phpMyAdmin or MySQL CLI.
4. Import `database_seed.sql`.
5. Make sure `db_connect.php` matches your local database credentials.
6. Open:

```text
http://localhost/APPDEV/hotel_reservation_jo/login.php
```

## Default Accounts

```text
Admin
Email: admin@example.com
Password: admin123

Client
Email: client@example.com
Password: client123
```

## Database

The included `database_seed.sql` recreates:

- `users`
- `rooms`
- `reservations`

It also adds sample rooms and default admin/client accounts.

## Notes

This project is intended as a portfolio demo. For deployment, update `db_connect.php` with the hosting provider's MySQL credentials and avoid using the default demo passwords in production.
