DROP DATABASE IF EXISTS hotel_reservation_db;
CREATE DATABASE hotel_reservation_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE hotel_reservation_db;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'client') NOT NULL DEFAULT 'client',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rooms (
  room_id INT AUTO_INCREMENT PRIMARY KEY,
  room_type VARCHAR(50) NOT NULL,
  room_number VARCHAR(20) NOT NULL UNIQUE,
  price DECIMAL(10,2) NOT NULL,
  status ENUM('available', 'reserved') NOT NULL DEFAULT 'available',
  photo VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reservations (
  reservation_id INT AUTO_INCREMENT PRIMARY KEY,
  transaction_id VARCHAR(60) NOT NULL UNIQUE,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  penalty DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status ENUM('active', 'canceled') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reservations_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_reservations_room
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (name, email, password, role) VALUES
  ('Admin', 'admin@example.com', '0192023a7bbd73250516f069df18b500', 'admin'),
  ('Client', 'client@example.com', '3677b23baa08f74c28aba07f0cb6554e', 'client');

INSERT INTO rooms (room_type, room_number, price, status, photo) VALUES
  ('Deluxe', 'D101', 3500.00, 'available', 'images/deluxe.jpg'),
  ('Deluxe', 'D102', 3500.00, 'available', 'images/deluxe2.jpg'),
  ('Deluxe', 'D103', 3800.00, 'available', 'images/deluxe3.jpg'),
  ('Deluxe', 'D104', 4000.00, 'available', 'images/deluxe4.jpg'),
  ('Deluxe', 'D105', 4200.00, 'available', 'images/deluxe5.jpg'),
  ('Suite', 'S201', 5500.00, 'available', 'images/s1.jpg'),
  ('Suite', 'S202', 5800.00, 'available', 'images/s2.jpg'),
  ('Suite', 'S203', 6000.00, 'available', 'images/s3.jpg'),
  ('Suite', 'S204', 6200.00, 'available', 'images/s4.jpg'),
  ('Suite', 'S205', 6500.00, 'available', 'images/s5.jpg'),
  ('Family', 'F301', 4500.00, 'available', 'images/family1.jpg'),
  ('Family', 'F302', 4700.00, 'available', 'images/f2.jpg'),
  ('Family', 'F303', 5000.00, 'available', 'images/f3.jpg'),
  ('Family', 'F304', 5200.00, 'available', 'images/f4.jpg'),
  ('Family', 'F305', 5500.00, 'available', 'images/f5.jpg');
