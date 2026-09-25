-- ============================================================
-- Dairy Management System - Database Setup
-- Run this file in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS dairy_management;
USE dairy_management;

-- ---------------------------------------------------------------
-- Farmers Table
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS farmers (
    farmer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address TEXT NOT NULL,
    village VARCHAR(100),
    bank_account VARCHAR(20),
    ifsc_code VARCHAR(15),
    join_date DATE NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- Employees Table
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    role ENUM('Manager','Supervisor','Lab Technician','Driver','Helper','Accountant') NOT NULL,
    salary DECIMAL(10,2) NOT NULL,
    join_date DATE NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- Dairy Products Table
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    price_per_unit DECIMAL(10,2) NOT NULL,
    stock_quantity DECIMAL(10,2) DEFAULT 0,
    description TEXT,
    status ENUM('available','unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- Daily Production Data Table
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS daily_production (
    production_id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    product_id INT NOT NULL,
    production_date DATE NOT NULL,
    morning_qty DECIMAL(10,2) DEFAULT 0,
    evening_qty DECIMAL(10,2) DEFAULT 0,
    total_qty DECIMAL(10,2) GENERATED ALWAYS AS (morning_qty + evening_qty) STORED,
    fat_percentage DECIMAL(5,2),
    snf_percentage DECIMAL(5,2),
    rate_per_unit DECIMAL(10,2) NOT NULL,
    amount DECIMAL(10,2) GENERATED ALWAYS AS ((morning_qty + evening_qty) * rate_per_unit) STORED,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------
-- Bills Table
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bills (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    bill_month VARCHAR(7) NOT NULL,
    total_quantity DECIMAL(10,2),
    total_amount DECIMAL(10,2),
    deductions DECIMAL(10,2) DEFAULT 0,
    net_payable DECIMAL(10,2),
    status ENUM('pending','paid') DEFAULT 'pending',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------
-- Sample Data
-- ---------------------------------------------------------------
INSERT INTO farmers (name, phone, address, village, bank_account, ifsc_code, join_date) VALUES
('Ramesh Kumar', '9876543210', '12, MG Road, Mysuru', 'Mandya', '1234567890', 'SBI0001234', '2023-01-10'),
('Suresh Patel', '9812345678', '45, Gandhi Nagar, Hassan', 'Hassan', '0987654321', 'HDFC0005678', '2023-03-15'),
('Kavitha Devi', '9845001122', 'Plot 7, Dairy Colony, Tumkur', 'Tumkur', '1122334455', 'AXIS0009012', '2023-06-01');

INSERT INTO employees (name, phone, email, address, role, salary, join_date) VALUES
('Anand Sharma', '9900112233', 'anand@dairy.com', 'Bengaluru', 'Manager', 45000.00, '2022-05-01'),
('Priya Nair', '9988776655', 'priya@dairy.com', 'Mysuru', 'Lab Technician', 28000.00, '2022-08-15'),
('Vijay Singh', '9871234560', 'vijay@dairy.com', 'Tumkur', 'Driver', 22000.00, '2023-01-01');

INSERT INTO products (product_name, unit, price_per_unit, stock_quantity, description) VALUES
('Full Cream Milk', 'Litre', 55.00, 500, 'Fresh full cream cow milk'),
('Toned Milk', 'Litre', 45.00, 300, 'Low fat toned milk'),
('Butter', 'Kg', 480.00, 50, 'Fresh white butter'),
('Ghee', 'Kg', 580.00, 30, 'Pure cow ghee'),
('Paneer', 'Kg', 350.00, 20, 'Fresh homemade paneer'),
('Curd', 'Kg', 60.00, 80, 'Thick set curd');

INSERT INTO daily_production (farmer_id, product_id, production_date, morning_qty, evening_qty, fat_percentage, snf_percentage, rate_per_unit) VALUES
(1, 1, CURDATE(), 15.5, 12.0, 3.8, 8.5, 55.00),
(2, 1, CURDATE(), 10.0, 9.5, 4.0, 8.7, 55.00),
(3, 2, CURDATE(), 8.0, 7.5, 3.2, 8.2, 45.00);