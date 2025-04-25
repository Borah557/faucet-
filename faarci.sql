-- Create Database
CREATE DATABASE IF NOT EXISTS faarci;
USE faarci;

-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    email VARCHAR(255) PRIMARY KEY,
    balance DECIMAL(18,8) DEFAULT 0,
    referrals INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optional: Insert a test user for development
-- INSERT INTO users (email, balance, referrals) VALUES ('test@faucetpay.io', 0.0001, 2);
