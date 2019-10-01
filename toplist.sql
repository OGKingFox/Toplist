-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 01, 2019 at 05:20 PM
-- Server version: 10.3.18-MariaDB
-- PHP Version: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `runenexus_main`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
    `id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `user_id` bigint(20) NOT NULL,
    `news_body` longtext DEFAULT NULL,
    `date_posted` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
    `id` int(11) NOT NULL,
    `server_id` int(11) NOT NULL,
    `user_id` varchar(255) NOT NULL,
    `username` varchar(255) NOT NULL,
    `comment` text NOT NULL,
    `date_posted` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
     `id` int(11) NOT NULL,
     `title` varchar(255) NOT NULL,
     `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `item_list`
--

CREATE TABLE `item_list` (
     `id` int(11) NOT NULL,
     `name` varchar(255) NOT NULL,
     `reqs` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
     `id` int(11) NOT NULL,
     `server_id` varchar(255) NOT NULL,
     `user_id` varchar(255) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
    `id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `price` float NOT NULL,
    `base_price` float NOT NULL,
    `length` int(11) NOT NULL DEFAULT 0,
    `features` text DEFAULT NULL,
    `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
    `server_id` int(11) NOT NULL,
    `count` int(11) NOT NULL,
    `player_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
    `id` int(11) NOT NULL,
    `ip_address` varchar(255) DEFAULT NULL,
    `location` varchar(255) DEFAULT NULL,
    `referrer` varchar(255) DEFAULT NULL,
    `date_added` int(11) DEFAULT -1
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
   `id` int(11) NOT NULL,
   `user_id` bigint(20) DEFAULT -1,
   `username` varchar(255) DEFAULT 'Anonymous',
   `server_id` int(11) NOT NULL,
   `reason` text NOT NULL,
   `date_submitted` bigint(20) NOT NULL DEFAULT -1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `screenshots`
--

CREATE TABLE `screenshots` (
   `server_id` int(11) NOT NULL,
   `owner_id` bigint(20) DEFAULT NULL,
   `images` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

CREATE TABLE `servers` (
    `id` int(11) NOT NULL,
    `owner_id` bigint(20) NOT NULL,
    `owner_tag` varchar(255) NOT NULL,
    `game` varchar(255) DEFAULT NULL,
    `title` varchar(255) NOT NULL,
    `revision` varchar(3) DEFAULT NULL,
    `server_ip` varchar(255) DEFAULT NULL,
    `server_port` int(11) NOT NULL DEFAULT -1,
    `is_online` tinyint(1) NOT NULL DEFAULT 0,
    `votes` int(11) NOT NULL DEFAULT 0,
    `date_created` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `servers_info`
--

CREATE TABLE `servers_info` (
    `server_id` int(11) NOT NULL,
    `website` varchar(255) DEFAULT NULL,
    `callback` varchar(255) DEFAULT NULL,
    `discord_id` varchar(255) DEFAULT NULL,
    `banner_url` varchar(255) DEFAULT NULL,
    `meta_info` varchar(255) DEFAULT NULL,
    `meta_tags` text DEFAULT NULL,
    `info` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
    `id` int(11) NOT NULL,
    `user_id` bigint(20) DEFAULT NULL,
    `discriminator` varchar(255) NOT NULL DEFAULT '-1',
    `username` varchar(255) NOT NULL,
    `role` varchar(255) DEFAULT 'Member',
    `premium_level` int(11) NOT NULL DEFAULT 0,
    `premium_expires` bigint(20) DEFAULT 0,
    `theme_id` varchar(255) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    `avatar` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users_data`
--

CREATE TABLE `users_data` (
    `user_id` bigint(20) NOT NULL,
    `api_key` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
    `id` int(11) NOT NULL,
    `server_id` varchar(255) NOT NULL,
    `ip_address` varchar(255) DEFAULT NULL,
    `incentive` varchar(255) DEFAULT NULL,
    `voted_on` bigint(20) NOT NULL DEFAULT -1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `item_list`
--
ALTER TABLE `item_list`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
    ADD PRIMARY KEY (`server_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `screenshots`
--
ALTER TABLE `screenshots`
    ADD PRIMARY KEY (`server_id`),
    ADD UNIQUE KEY `server_id` (`server_id`);

--
-- Indexes for table `servers`
--
ALTER TABLE `servers`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servers_info`
--
ALTER TABLE `servers_info`
    ADD PRIMARY KEY (`server_id`),
    ADD UNIQUE KEY `server_id` (`server_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users_data`
--
ALTER TABLE `users_data`
    ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
    ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servers`
--
ALTER TABLE `servers`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
