-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 15, 2016 at 04:07 PM
-- Server version: 5.7.16-0ubuntu0.16.04.1
-- PHP Version: 7.0.8-0ubuntu0.16.04.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_freak`
--

-- --------------------------------------------------------

--
-- Table structure for table `bugs`
--

CREATE TABLE `bugs` (
  `id` int(11) NOT NULL,
  `environment` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `line` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `message` varchar(255) NOT NULL,
  `trace` text NOT NULL,
  `counter` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `confirmed_changes`
--

CREATE TABLE `confirmed_changes` (
  `id` int(11) NOT NULL,
  `saver_user_id` smallint(5) UNSIGNED NOT NULL,
  `current_user_id` smallint(5) UNSIGNED NOT NULL,
  `resource_id` int(11) NOT NULL,
  `element_id` int(11) NOT NULL,
  `session_id` char(40) NOT NULL,
  `value` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_spent` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` tinyint(5) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`) VALUES
(5, 'admininistration'),
(1, 'affiliation'),
(2, 'blog'),
(4, 'data'),
(3, 'support');

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `id` tinyint(5) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `priority` tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`id`, `name`, `priority`) VALUES
(1, 'junior', 1),
(4, 'master', 4);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `isError` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `code`, `message`, `isError`) VALUES
(1, 'NOT_ALLOWED', 'Page you originally requested requires login / is not allowed for your account!', 1),
(2, 'NOT_FOUND', 'Page you originally requested was not found!', 1),
(3, 'LOGIN_FAILED', 'Login failed!', 1),
(4, 'LOGOUT_OK', 'Logout successful!', 0),
(5, 'LOGIN_SUCCESS', 'Login successful!', 0),
(8, 'DELETE_SUCCESSFUL', 'Entry was deleted successfully!', 0),
(9, 'ADD_SUCCESSFUL', 'Entry was added successfully!', 0),
(10, 'EDIT_SUCCESSFUL', 'Entry was edited successfully!', 0),
(11, 'SAVE_PENDING', 'Changes were saved and wait for approval!', 0),
(12, 'SAVE_FAILED', 'Changes could not be saved!', 1);

-- --------------------------------------------------------

--
-- Table structure for table `panels`
--


CREATE TABLE `panels` (
  `id` int(11) NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `panels`
--

INSERT INTO `panels` (`id`, `is_public`, `parent_id`, `url`) VALUES
(1, 1, 0, 'login'),
(2, 0, 0, 'index'),
(3, 0, 0, 'logout'),
(4, 0, 0, 'users'),
(5, 0, 0, 'panels'),
(10, 0, 5, 'panel/add'),
(11, 0, 5, 'panel/edit'),
(12, 0, 5, 'panel/delete'),
(16, 0, 5, 'resource/add'),
(17, 0, 5, 'resource/edit'),
(18, 0, 5, 'resource/delete'),
(45, 0, 0, 'bugs'),
(46, 0, 45, 'bugs/test'),
(47, 0, 45, 'bugs/delete'),
(48, 0, 45, 'bugs/info'),
(52, 0, 0, 'messages'),
(53, 0, 52, 'message/add'),
(54, 0, 52, 'message/edit'),
(55, 0, 52, 'message/delete'),
(63, 0, 4, 'users/synchronization'),
(65, 1, 0, 'authorize'),
(66, 1, 65, 'authorize/all'),
(68, 0, 0, 'rebrand-trigger'),
(69, 0, 0, 'htaccess-scanner'),
(70, 0, 0, 'menu');

-- --------------------------------------------------------

--
-- Table structure for table `panels_resources`
--

CREATE TABLE `panels_resources` (
  `id` int(11) NOT NULL,
  `panel_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `name`) VALUES
(1, 'Login'),
(2, 'Home'),
(3, 'Logout'),
(4, 'Users'),
(5, 'Panels'),
(7, 'add user'),
(8, 'edit user'),
(9, 'delete user'),
(10, 'add panel'),
(11, 'edit panel'),
(12, 'delete panel'),
(16, 'add resource'),
(17, 'edit resource'),
(18, 'delete resource'),
(45, 'Bugs'),
(46, 'test bug'),
(47, 'delete bug'),
(48, 'Bug Details'),
(52, 'Messages'),
(53, 'add message'),
(54, 'edit message'),
(55, 'delete message'),
(62, 'delete panel'),
(63, 'synchronization'),
(65, 'Authorization'),
(66, 'global authorization'),
(68, 'Rebrand Trigger'),
(69, 'Htaccess Scanner'),
(70, 'Menu');

-- --------------------------------------------------------

--
-- Table structure for table `rights`
--

CREATE TABLE `rights` (
  `id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `department_id` tinyint(3) UNSIGNED NOT NULL,
  `level_id` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rights`
--

INSERT INTO `rights` (`id`, `resource_id`, `department_id`, `level_id`) VALUES
(172, 2, 1, 1),
(173, 2, 2, 1),
(174, 2, 3, 1),
(175, 2, 4, 1),
(176, 2, 5, 1),
(131, 3, 1, 1),
(129, 3, 2, 1),
(133, 3, 3, 1),
(132, 3, 4, 1),
(130, 3, 5, 1),
(178, 4, 5, 1),
(9, 5, 5, 1),
(24, 7, 5, 1),
(12, 8, 5, 1),
(13, 9, 5, 1),
(14, 10, 5, 1),
(144, 11, 5, 1),
(143, 12, 5, 1),
(147, 16, 5, 1),
(27, 17, 5, 1),
(28, 18, 5, 1),
(113, 45, 5, 1),
(117, 46, 5, 1),
(115, 47, 5, 1),
(150, 48, 5, 1),
(157, 52, 5, 1),
(158, 53, 5, 1),
(159, 54, 5, 1),
(160, 55, 5, 1),
(198, 63, 5, 4);
(209, 68, 5, 1),
(212, 69, 5, 1),
(213, 70, 5, 1)

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` char(32) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`) VALUES
(1, 'lucian@hliscorp.com', 'b02fa0320a61b5f99ee80ea5fce94294', 'Lucian Popescu');

-- --------------------------------------------------------

--
-- Table structure for table `users_departments`
--

CREATE TABLE `users_departments` (
  `id` int(11) NOT NULL,
  `user_id` smallint(5) UNSIGNED NOT NULL,
  `department_id` tinyint(3) UNSIGNED NOT NULL,
  `level_id` tinyint(3) UNSIGNED NOT NULL,
  `group_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_departments`
--

INSERT INTO `users_departments` (`id`, `user_id`, `department_id`, `level_id`, `group_id`) VALUES
(51, 1, 5, 4, 0),
(52, 1, 1, 4, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bugs`
--
ALTER TABLE `bugs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type` (`type`,`file`,`line`,`message`);

--
-- Indexes for table `confirmed_changes`
--
ALTER TABLE `confirmed_changes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `saver_user_id` (`saver_user_id`),
  ADD KEY `current_user_id` (`current_user_id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `panels`
--
ALTER TABLE `panels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `panels_resources`
--
ALTER TABLE `panels_resources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `panel_id` (`panel_id`,`resource_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rights`
--
ALTER TABLE `rights`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resource_id` (`resource_id`,`department_id`,`level_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `level_id` (`level_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users_departments`
--
ALTER TABLE `users_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`department_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `level_id` (`level_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bugs`
--
ALTER TABLE `bugs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `confirmed_changes`
--
ALTER TABLE `confirmed_changes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` tinyint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `levels`
--
ALTER TABLE `levels`
  MODIFY `id` tinyint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `panels`
--
ALTER TABLE `panels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;
--
-- AUTO_INCREMENT for table `panels_resources`
--
ALTER TABLE `panels_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;
--
-- AUTO_INCREMENT for table `rights`
--
ALTER TABLE `rights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `users_departments`
--
ALTER TABLE `users_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `panels`
--
ALTER TABLE `panels`
  ADD CONSTRAINT `panels_ibfk_1` FOREIGN KEY (`id`) REFERENCES `resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `panels_resources`
--
ALTER TABLE `panels_resources`
  ADD CONSTRAINT `panels_resources_ibfk_1` FOREIGN KEY (`panel_id`) REFERENCES `panels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `panels_resources_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rights`
--
ALTER TABLE `rights`
  ADD CONSTRAINT `rights_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rights_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rights_ibfk_3` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users_departments`
--
ALTER TABLE `users_departments`
  ADD CONSTRAINT `users_departments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_departments_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_departments_ibfk_3` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE;


--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `panel_id` int(11) NOT NULL,
  `holder` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `panel_id`, `holder`) VALUES
(1, 4, 'System'),
(2, 67, 'Admins'),
(3, 68, 'Admins'),
(4, 45, 'Tech'),
(5, 5, 'Tech'),
(6, 52, 'Tech'),
(7, 70, 'Tech');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `panel_id` (`panel_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`panel_id`) REFERENCES `panels` (`id`) ON DELETE CASCADE;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;