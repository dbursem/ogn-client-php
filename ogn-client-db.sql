-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 18, 2015 at 04:02 AM
-- Server version: 5.5.46-0ubuntu0.14.04.2
-- PHP Version: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `glidernet_logs`
--

-- --------------------------------------------------------

--
-- Table structure for table `airplanes`
--

CREATE TABLE IF NOT EXISTS `airplanes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration` varchar(10) NOT NULL,
  `flarm_id` varchar(9) NOT NULL,
  `callsign` varchar(10) NOT NULL,
  `type` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
-- --------------------------------------------------------
ALTER TABLE `airplanes`
  CHANGE COLUMN `flarm_id` `aprs_callsign` VARCHAR(20) NOT NULL,
    ADD COLUMN `device_id` VARCHAR(6) NOT NULL,
    ADD COLUMN `device_type` INT(1) NOT NULL
  AFTER `type`;
--
-- Table structure for table `ogn_logs`
--

CREATE TABLE IF NOT EXISTS `ogn_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `flarm_id` varchar(9) NOT NULL,
  `log_time` datetime NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `latitude` decimal(8,6) NOT NULL,
  `altitude` int(11) NOT NULL,
  `receiver` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

ALTER TABLE `ogn_logs`
  ADD COLUMN `course` INT(4) NOT NULL,
  ADD COLUMN `speed` INT(4) NOT NULL,
  ADD COLUMN `device_type` INT(1) NOT NULL,
  ADD COLUMN `aircraft_category` INT(1) NOT NULL,
  ADD COLUMN `notrack` INT(1) NOT NULL,
  ADD COLUMN `stealth` INT(1) NOT NULL,
  ADD COLUMN `device_id` VARCHAR(6) NOT NULL,
  ADD COLUMN `climbrate` INT(4) NOT NULL,
  ADD COLUMN `rotation` FLOAT NOT NULL,
  ADD COLUMN `signaltonoise` FLOAT NOT NULL,
  ADD COLUMN `biterrors` INT(2) NOT NULL,
  ADD COLUMN `freqency_offset` FLOAT NOT NULL,
  ADD COLUMN `raw` VARCHAR(200) NOT NULL
  AFTER `receiver`,
  CHANGE COLUMN `flarm_id` `aprs_callsign` VARCHAR(20) NOT NULL;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
