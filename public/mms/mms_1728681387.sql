-- phpMyAdmin SQL Dump
-- version 5.1.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 19, 2024 at 05:11 PM
-- Server version: 8.0.36-28
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbv4unux5qa8qo`
--

-- --------------------------------------------------------

--
-- Table structure for table `cg_chat_boxes`
--

CREATE TABLE `cg_chat_boxes` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `from` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sending_server_id` bigint UNSIGNED DEFAULT NULL,
  `reply_by_customer` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cg_chat_boxes`
--

INSERT INTO `cg_chat_boxes` (`id`, `uid`, `user_id`, `from`, `to`, `notification`, `created_at`, `updated_at`, `sending_server_id`, `reply_by_customer`) VALUES
(1, '662c14cb07839', 1, '14013610746', '254790508982', NULL, '2024-04-26 21:55:39', '2024-04-26 21:55:39', 1, 0),
(2, '662d5bbbc675d', 1, '14013610746', '15029109264', 0, '2024-04-27 21:10:35', '2024-05-21 19:23:29', 1, 1),
(3, '6666ce59a65ac', 1, '12044001022', '254790508982', NULL, '2024-06-10 10:58:49', '2024-06-10 10:58:49', 2, 0),
(4, '6666d690dae4e', 1, '13216779686', '13216779686', 0, '2024-06-10 11:33:52', '2024-06-10 11:43:12', 1, 1),
(5, '666c6384e6d4f', 1, '13216779686', '17864325446', 0, '2024-06-14 16:36:36', '2024-06-20 00:54:15', 1, 1),
(6, '66880f0c284db', 1, '13216779686', '45528', 0, '2024-07-05 16:19:40', '2024-07-19 19:48:31', 1, 1),
(7, '66abcff6b824d', 1, '14075812918', '15029109264', 0, '2024-08-01 19:12:06', '2024-08-01 19:12:24', 3, 1),
(8, '66abd11db59c4', 1, '18665302257', '14075812918', 0, '2024-08-01 19:17:01', '2024-08-12 04:57:00', 3, 1),
(9, '66b4d0c71bc29', 1, '12513895546', '14075812918', 0, '2024-08-08 15:05:59', '2024-08-08 15:06:06', 2, 1),
(10, '66b53764a865b', 1, '12513895546', '13862155021', 0, '2024-08-08 22:23:48', '2024-08-12 04:57:04', 2, 1),
(11, '66b625d2683fe', 1, '18665302257', '13867489189', 0, '2024-08-09 15:21:06', '2024-08-12 04:56:56', 3, 1),
(12, '66b63818665d6', 1, '18665302257', '15619720893', 0, '2024-08-09 16:39:04', '2024-08-12 04:56:54', 3, 1),
(13, '66b63f50bb143', 1, '18665302257', '17279678599', 0, '2024-08-09 17:09:52', '2024-08-12 04:57:17', 3, 1),
(14, '66b6408c1857c', 1, '18665302257', '13344750986', 0, '2024-08-09 17:15:08', '2024-08-12 04:05:50', 3, 1),
(15, '66b6c250e5279', 1, '18665302257', '18632268881', 0, '2024-08-10 02:28:48', '2024-08-12 04:05:45', 3, 1),
(16, '66ba1fb23466b', 1, '18665302257', '15086851386', 1, '2024-08-12 15:44:02', '2024-08-12 15:44:02', 3, 1),
(17, '66ba23fcaa4a9', 1, '18665302257', '14328531979', 1, '2024-08-12 16:02:20', '2024-08-12 16:02:20', 3, 1),
(18, '66ba378196f4b', 1, '18665302257', '17723019812', 1, '2024-08-12 17:25:37', '2024-08-12 17:25:37', 3, 1),
(19, '66bb8140cd472', 1, '18665302257', '15613133976', 1, '2024-08-13 16:52:32', '2024-08-13 16:52:32', 3, 1),
(20, '66be251b1f812', 1, '12513895546', '13217755589', 1, '2024-08-15 16:56:11', '2024-08-15 16:56:11', 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cg_chat_boxes`
--
ALTER TABLE `cg_chat_boxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cg_chat_boxes_user_id_foreign` (`user_id`),
  ADD KEY `cg_chat_boxes_sending_server_id_foreign` (`sending_server_id`),
  ADD KEY `cg_chat_boxes_reply_by_customer_index` (`reply_by_customer`),
  ADD KEY `cg_chat_boxes_updated_at_index` (`updated_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cg_chat_boxes`
--
ALTER TABLE `cg_chat_boxes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cg_chat_boxes`
--
ALTER TABLE `cg_chat_boxes`
  ADD CONSTRAINT `cg_chat_boxes_sending_server_id_foreign` FOREIGN KEY (`sending_server_id`) REFERENCES `cg_sending_servers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cg_chat_boxes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `cg_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
