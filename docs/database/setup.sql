-- ---------------------------------------------------------------------------
-- Paydar Group website — dedicated MySQL database and user.
--
-- This project must NOT share a database or user with Paydar Fund
-- (which uses `paydargroup_db`). Run this ONCE as a MySQL admin user:
--
--     sudo mysql < docs/database/setup.sql
--
-- Replace CHANGE_ME with the value of DB_PASSWORD from your .env first.
-- The script is idempotent (IF NOT EXISTS) and never drops anything.
-- ---------------------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `paydar_group`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'paydar_group'@'localhost' IDENTIFIED BY 'CHANGE_ME';

GRANT ALL PRIVILEGES ON `paydar_group`.* TO 'paydar_group'@'localhost';

FLUSH PRIVILEGES;
