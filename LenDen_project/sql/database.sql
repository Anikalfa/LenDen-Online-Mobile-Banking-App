-- Create Database
CREATE DATABASE bkash_project;
USE bkash_project;

-- Ensure users table is created first
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    nid VARCHAR(30) UNIQUE NOT NULL,  -- Extended length for NID
    dob DATE NOT NULL,  -- Date of Birth
    address TEXT NOT NULL,  -- Address
    password VARCHAR(255) NOT NULL,  -- Hashed password
    pin VARCHAR(255) NOT NULL,  -- Hashed PIN for security
    balance DECIMAL(10,2) DEFAULT 0.00
);


CREATE TABLE merchants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    balance DECIMAL(10,2) DEFAULT 0.00  -- Added balance column
);
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT DEFAULT NULL, -- NULL for cases like 'add_money'
    receiver_type ENUM('user', 'merchant', 'bank') DEFAULT NULL,  
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('send_money', 'recharge', 'receive_money', 'cash_out', 'add_money', 'payment', 'pay_bill') NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    reference VARCHAR(255) DEFAULT NULL,  -- Added reference field
    transaction_id VARCHAR(50) UNIQUE NOT NULL,  -- Added transaction_id
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE -- Allows tracking receivers
);

-- Create mobile_recharge table
CREATE TABLE mobile_recharge (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    operator VARCHAR(50) NOT NULL, 
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    merchant_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
);

-- Create bill_payments table
CREATE TABLE bill_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bill_type ENUM('Electricity', 'Water', 'Gas', 'Internet') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create add_money table (UPDATED)
CREATE TABLE add_money (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('bank', 'card') NOT NULL,
    bank_account VARCHAR(50) DEFAULT NULL,  -- Stores bank account number if method is 'bank'
    bank_pin VARCHAR(20) DEFAULT NULL,  -- Stores bank PIN if method is 'bank'
    card_number VARCHAR(20) DEFAULT NULL,  -- Stores card number if method is 'card'
    cvv VARCHAR(4) DEFAULT NULL,  -- Stores card CVV if method is 'card'
    transaction_id VARCHAR(100) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create cash_out table
CREATE TABLE cash_out (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    agent_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    fee DECIMAL(10,2) DEFAULT 0.00,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES merchants(id) ON DELETE CASCADE
);
