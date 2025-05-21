-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 23, 2024 at 10:00 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wedding_db`
--
-- You might want to create the database first if it doesn't exist:
-- CREATE DATABASE IF NOT EXISTS `wedding_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `wedding_db`;

-- --------------------------------------------------------

--
-- Table structure for table `rsvps`
--
-- This table will store the actual RSVP submissions.
--

CREATE TABLE `rsvps` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `attending` ENUM('yes', 'no') NOT NULL,
  `guests` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Number of guests including the person RSVPing',
  `message` TEXT DEFAULT NULL,
  `submission_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Optional: to help identify unique submissions or for troubleshooting',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`) COMMENT 'Optional: if you want to prevent multiple RSVPs from the same email. Consider if guests might share an email.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Notes on `rsvps` table:
-- - `id`: Primary key, auto-increments.
-- - `name`: Full name of the guest.
-- - `email`: Email address.
-- - `attending`: 'yes' or 'no'. ENUM is good for fixed choices.
-- - `guests`: Number of people attending (including the primary guest).
-- - `message`: Optional message from the guest.
-- - `submission_date`: Timestamp of when the RSVP was submitted.
-- - `ip_address`: Storing IP can be useful but also has privacy implications. Ensure you comply with data protection laws (e.g., GDPR if applicable).
-- - `email_unique`: This constraint prevents the same email from being used twice. If you expect multiple people from the same household to RSVP separately using the same email (e.g., a family email), you might want to remove this `UNIQUE KEY`. If you keep it, your backend logic should handle the "email already exists" error gracefully.


-- --------------------------------------------------------

--
-- (OPTIONAL) Table structure for table `site_visits`
--
-- This table can log every time someone visits the page.
-- WARNING: This table can grow very large very quickly on a popular site.
-- Consider if basic web server logs or a dedicated analytics service (like Google Analytics)
-- would be a better solution for tracking general traffic.
--

CREATE TABLE `site_visits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `visit_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visit_timestamp` (`visit_timestamp`),
  KEY `idx_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Notes on `site_visits` table:
-- - `id`: Primary key. BIGINT because it could grow large.
-- - `ip_address`: Visitor's IP address.
-- - `user_agent`: Browser and OS information.
-- - `visit_timestamp`: When they visited.
-- - This table is purely for logging visits. It doesn't link directly to RSVPs unless you add a foreign key,
--   but that would only work if the RSVP happened in the same session, which is not guaranteed.

-- --------------------------------------------------------

--
-- (OPTIONAL) Table structure for table `guest_list`
--
-- If you have a predefined guest list and want to track who has RSVP'd against that list.
-- This is more complex to implement on the frontend (e.g., invite codes or matching emails).
--

/*
CREATE TABLE `guest_list` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invite_code` VARCHAR(20) DEFAULT NULL COMMENT 'A unique code sent to guests',
  `primary_guest_name` VARCHAR(255) NOT NULL,
  `primary_guest_email` VARCHAR(255) DEFAULT NULL,
  `allowed_plus_ones` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `rsvp_id` INT UNSIGNED DEFAULT NULL COMMENT 'Foreign key to the rsvps table, if they have RSVPd',
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invite_code_unique` (`invite_code`),
  KEY `fk_guest_list_rsvp` (`rsvp_id`),
  CONSTRAINT `fk_guest_list_rsvp` FOREIGN KEY (`rsvp_id`) REFERENCES `rsvps` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

--
-- Notes on `guest_list` table:
-- - This is a more advanced setup. You'd pre-populate this table with your invited guests.
-- - `invite_code`: Could be used for guests to "unlock" the RSVP form.
-- - `allowed_plus_ones`: How many additional guests they can bring.
-- - `rsvp_id`: Links to their entry in the `rsvps` table once they respond.
-- - I've commented this out as it adds significant complexity to the backend PHP code.
--   If you want this, you'll need PHP logic to handle invite codes, match guests, etc.

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;