-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 11, 2012 at 01:19 AM
-- Server version: 5.5.9
-- PHP Version: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ncmls`
--

-- --------------------------------------------------------

--
-- Table structure for table `events_special`
--

CREATE TABLE `events_special` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `date` date NOT NULL,
  `time` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `fb_link` text NOT NULL,
  `description` text NOT NULL,
  `special_note` text NOT NULL,
  `members_only` tinyint(1) NOT NULL,
  `cost_members` varchar(255) NOT NULL,
  `cost_public` varchar(255) NOT NULL,
  `custom_1` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `active` (`active`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `events_special`
--

INSERT INTO `events_special` VALUES(1, 'Christmas', '2012-12-25', '10:15', '2118Screen_Shot_2012-06-10_at_11.44.01_PM.png', 'http://facebook.com/blah', 'Christmas 2012~', 'This is the special note message - testing', 0, '25', '15', 'All Ages', 1, '2012-06-10 23:44:42');
INSERT INTO `events_special` VALUES(2, 'WWDC', '2012-06-11', '13:00', '3695Screen_Shot_2012-06-10_at_11.43.42_PM.png', 'http://facebook.com/blah3', 'This is the first keynote event.', 'This text goes under the event photo.', 0, 'FREE', '', 'All Ages', 1, '2012-06-10 23:48:05');
INSERT INTO `events_special` VALUES(8, 'From Chrome', '2012-06-12', '11:15', '', '', 'Desc', '', 1, '12', '', 'wadfadf', 1, '2012-06-10 23:57:05');
INSERT INTO `events_special` VALUES(9, 'Chrome - public event', '2012-06-19', '12:30', '294Screen_Shot_2012-06-10_at_11.43.51_PM.png', '', 'afadsfasdf', '', 0, '12', '11', '', 1, '2012-06-10 23:57:31');
INSERT INTO `events_special` VALUES(10, 'From Safari', '2012-06-15', '12:45', '4788Screen_Shot_2012-06-10_at_11.43.42_PM.png', '', 'This is a desc', '', 1, '122', '', 'adfadf', 1, '2012-06-10 23:59:03');
INSERT INTO `events_special` VALUES(11, 'Safari - public', '2012-06-19', '11:15', '', '', 'asfadf', '', 0, '11', '11', '', 1, '2012-06-10 23:59:16');
INSERT INTO `events_special` VALUES(13, 'Ice Cream Social', '2012-06-15', '11:15', '', 'http://facebook.com/blah', 'Not really. I wish.', 'Yum Ice cream', 0, '25', '12', '18+', 1, '2012-06-11 01:07:03');

-- --------------------------------------------------------

--
-- Table structure for table `events_weekly`
--

CREATE TABLE `events_weekly` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `day_of_week` varchar(255) NOT NULL,
  `time` time NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `day_of_week` (`day_of_week`),
  KEY `active` (`active`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `events_weekly`
--

INSERT INTO `events_weekly` VALUES(8, 'Event 1', 'Thursday', '10:30:00', 'adfasdf', '', 1, '2012-05-29 01:08:50');
INSERT INTO `events_weekly` VALUES(9, 'Event 23', 'Tuesday', '10:15:00', '', '', 1, '2012-05-29 01:09:58');
INSERT INTO `events_weekly` VALUES(10, 'Event 3', 'Wednesday', '11:00:00', 'adsfasdf', '', 1, '2012-05-29 01:10:07');
INSERT INTO `events_weekly` VALUES(11, 'Late night', 'Wednesday', '15:30:00', 'adfasdf', '', 1, '2012-05-29 01:10:49');
INSERT INTO `events_weekly` VALUES(13, 'COFFEE', 'Wednesday', '11:45:00', 'rocks.', '4717AlbumArt.jpg', 1, '2012-05-29 01:16:36');
INSERT INTO `events_weekly` VALUES(15, 'Freeride Friday', 'Friday', '15:30:00', 'Biking @ Cisco!', '', 1, '2012-06-11 01:06:32');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `property` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `default` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `sort_id` int(5) NOT NULL,
  UNIQUE KEY `property_2` (`property`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` VALUES('end_time', '17:00', '17:00', 'End Time', 'This is the end time for events in the time picker.', 30);
INSERT INTO `settings` VALUES('start_time', '10:00', '10:00', 'Start Time', 'This is the start time for events in the time picker.', 10);
INSERT INTO `settings` VALUES('time_interval', '15', '15', 'Time Interval (min)', 'The number of minutes between each time interval in the time picker.', 40);
