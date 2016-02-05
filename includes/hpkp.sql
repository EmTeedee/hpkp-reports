-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2016 at 10:09 AM
-- Server version: 5.6.21
-- PHP Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `hpkp`
--

-- --------------------------------------------------------

--
-- Table structure for table `email_alerts`
--

CREATE TABLE IF NOT EXISTS `email_alerts` (
  `hostname` varchar(255) NOT NULL,
  `last_alert` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `reportId` int(10) unsigned NOT NULL,
  `received` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reporter-ip` varchar(39) NOT NULL,
  `date-time` datetime DEFAULT NULL,
  `hostname` varchar(255) NOT NULL,
  `port` int(11) DEFAULT NULL,
  `effective-expiration-date` datetime DEFAULT NULL,
  `include-subdomains` tinyint(1) DEFAULT NULL,
  `noted-hostname` varchar(255) DEFAULT NULL,
  `served-certificate-chain` text,
  `validated-certificate-chain` text,
  `known-pins` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='hpkp failure reports';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `email_alerts`
--
ALTER TABLE `email_alerts`
 ADD PRIMARY KEY (`hostname`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
 ADD PRIMARY KEY (`reportId`), ADD KEY (`reporter-ip`), ADD KEY `hostname` (`hostname`), ADD KEY `noted-hostname` (`noted-hostname`), ADD FULLTEXT KEY `known-pins` (`known-pins`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
MODIFY `reportId` int(10) unsigned NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
