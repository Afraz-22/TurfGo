-- TurfGo Database Schema
-- Import this file into a MySQL database named `turfgo`

CREATE DATABASE IF NOT EXISTS turfgo;
USE turfgo;

-- ==========================
-- Users table (Admin, Manager, Player)
-- ==========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(150),
    role ENUM('admin', 'manager', 'player') NOT NULL DEFAULT 'player',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- Turfs table
-- ==========================
CREATE TABLE turfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) NOT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- Bookings table
-- ==========================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    turf_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (turf_id) REFERENCES turfs(id) ON DELETE CASCADE
);

-- ==========================
-- Payments table
-- ==========================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('cash', 'card', 'mobile_banking') NOT NULL DEFAULT 'cash',
    status ENUM('paid', 'unpaid', 'refunded') NOT NULL DEFAULT 'unpaid',
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- ==========================
-- Seed data
-- ==========================

-- Default admin account (password: admin123)
INSERT INTO users (name, email, password, phone, address, role, status) VALUES
('Admin Owner', 'admin@turfgo.com', '$2b$10$8b5bZWHjCyIgIIDN0Ltfpu.9fwmIId2j4T5IQRzh7/x9dQ.nsvTSS', '01700000000', 'Mirpur, Dhaka', 'admin', 'active');

-- Sample manager (password: manager123)
INSERT INTO users (name, email, password, phone, address, role, status) VALUES
('Jahid Hasan', 'jahid@turfgo.com', '$2b$10$zlR2QKL/ffcJz8KLeYc61eHpi/jtnu4bNv.9FBa6LeUS2CLda13ZW', '01711111111', 'Banani, Dhaka', 'manager', 'active');

-- Sample turfs
INSERT INTO turfs (name, location, price_per_hour, status) VALUES
('Green Field', 'Mirpur, Dhaka', 1200.00, 'active'),
('Victory Turf', 'Banani, Dhaka', 1000.00, 'active'),
('Play Arena', 'Uttara, Dhaka', 1500.00, 'active'),
('Goal Point', 'Dhanmondi, Dhaka', 800.00, 'inactive');
