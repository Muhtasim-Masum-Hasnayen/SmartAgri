-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 01, 2025 at 09:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `farming_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `description`, `created_at`) VALUES
(1, 'Wheat', 500.00, 'wheat.jpg', 'High-quality wheat for sale.', '2025-01-01 19:27:59'),
(2, 'Corn', 300.00, 'corn.jpg', 'Freshly harvested corn.', '2025-01-01 19:27:59'),
(3, 'Rice', 450.00, 'rice.jpg', 'Organic rice available.', '2025-01-01 19:27:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `role` enum('Farmer','Admin','Supplier','Customer','Investor','Labour') DEFAULT 'Farmer',
  `start_date` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone_number`, `role`, `start_date`, `updated_at`) VALUES
(1, 'hasib', 'hasib@gamil.com', '$2y$10$.gGuVTwycYa6b18yZigG9eTszka36uDw8J88zKTwptLrSvH/ovveO', '01754270430', 'Farmer', '2024-12-26 00:00:00', '2024-12-26 20:40:18'),
(2, 'hasib', 'treport67@gmail.com', '$2y$10$K1dHdugiYF2QdRWFYgP.ZeRSx5Q2ym4OUuhNezBjn.mvFtA7HdWPq', '123', 'Farmer', '2024-12-26 00:00:00', '2024-12-26 21:16:01'),
(4, 'masum', 'masum@gmail.com', '$2y$10$nbCQUxj..JXUe8MqgkR8Qu24LJgDRF57kCuf6XGqLOZ4grNdHsrBG', '1234', 'Farmer', '2024-12-27 00:00:00', '2024-12-27 17:38:34'),
(5, 'hasib', 'hasibur303@gmail.com', '$2y$10$aDKu87FLIehuv6WKcN3AAOANALH35GAkp8AAa2IFAAyRDqgIa8xnm', '12345', 'Farmer', '2024-12-27 00:00:00', '2024-12-27 18:04:35'),
(7, 'maruf', 'maruf@gmail.com', '$2y$10$DZSwkLG10vzg.xgVx1etDuMk54oVmRn56OFarBJisTHvCLJBedWRO', '4444', 'Customer', '2025-01-01 00:00:00', '2025-01-01 19:12:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
