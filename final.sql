-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 12, 2024 at 12:58 AM
-- Server version: 8.0.39
-- PHP Version: 8.2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `final`
--

-- --------------------------------------------------------

--
-- Table structure for table `chars`
--

CREATE TABLE `chars` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `race` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `hp` int NOT NULL,
  `ac` int NOT NULL,
  `is_alive` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chars`
--

INSERT INTO `chars` (`id`, `name`, `race`, `class`, `hp`, `ac`, `is_alive`) VALUES
(2, 'Shadowheart', 'Half-Elf', 'Cleric', 20, 19, 1),
(3, 'Gale', 'Human', 'Wizard', 14, 16, 0),
(12, 'Lae`zel', 'Githyanki', 'Fighter', 17, 18, 1),
(13, 'Astarion', 'Elf', 'Rogue', 16, 17, 1),
(14, 'Minthara', 'Drow', 'Paladin', 20, 19, 1),
(15, 'Wyll', 'Human', 'Warlock', 16, 16, 0);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int NOT NULL,
  `character_id` int NOT NULL,
  `item_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `item_type` enum('Weapon','Armor','Food','Other') COLLATE utf8mb4_general_ci NOT NULL,
  `damage` varchar(256) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `armor_class` int DEFAULT NULL,
  `camp_supply` int DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `equipped` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `character_id`, `item_name`, `item_type`, `damage`, `armor_class`, `camp_supply`, `quantity`, `description`, `equipped`) VALUES
(4, 2, 'Apple', 'Food', NULL, NULL, 3, 1, 'Red Apple', 0),
(6, 2, 'Flail', 'Weapon', '1d8', NULL, NULL, 1, 'Flail', 1),
(11, 12, 'Silver Sword', 'Weapon', '2d6', NULL, NULL, 1, 'Big sword', 1),
(12, 2, 'Chainmail', 'Armor', NULL, 17, NULL, 1, 'Chainmail Armor', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'mark', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-11-12 05:17:20'),
(2, 'joseph', '$2y$10$6fA4l/dKJqAz5wOAh0GKueymshMVUQw6wIenKk98JXdzFpdBLXiiC', '2024-11-12 05:24:09'),
(7, 'xander', '$2y$10$Odm/zOJt9WiWDvoVY2Jb8ebMu4RNXrlY11oegEuPbYRRAxg9KCYFe', '2024-12-08 20:20:32'),
(8, 'david', '$2y$10$VcKjzBhsXt7vKRn0l4rdIuVVDBHLJEDqbYhXePrDM4TBVWDdKn7YS', '2024-12-12 00:28:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chars`
--
ALTER TABLE `chars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `character_id` (`character_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chars`
--
ALTER TABLE `chars`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `chars` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
