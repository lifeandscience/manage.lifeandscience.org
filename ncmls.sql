-- phpMyAdmin SQL Dump
-- version 3.3.7deb5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 17, 2014 at 01:17 AM
-- Server version: 5.1.49
-- PHP Version: 5.3.3-7+squeeze1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `manage`
--

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE IF NOT EXISTS `attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(500) NOT NULL,
  `event_id` int(11) NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_special`
--

CREATE TABLE IF NOT EXISTS `events_special` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `date` date NOT NULL,
  `display_date` text NOT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `sun_start_time` time DEFAULT NULL,
  `sun_end_time` time DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL,
  `fb_link` text NOT NULL,
  `tweet` text NOT NULL,
  `description` text NOT NULL,
  `special_note` text NOT NULL,
  `custom_1` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `isLab` int(1) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL,
  `group_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `tags` varchar(255) NOT NULL,
  `big_image` varchar(255) NOT NULL,
  `col1` text NOT NULL,
  `col2` text NOT NULL,
  `registration_code` int(1) DEFAULT NULL,
  `registration_url` varchar(2000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `active` (`active`),
  KEY `end_date` (`end_date`),
  KEY `tags` (`tags`),
  KEY `isLab` (`isLab`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=309 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_weekly`
--

CREATE TABLE IF NOT EXISTS `events_weekly` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `mon` tinyint(1) NOT NULL DEFAULT '0',
  `tue` tinyint(1) NOT NULL DEFAULT '0',
  `wed` tinyint(1) NOT NULL DEFAULT '0',
  `thu` tinyint(1) NOT NULL DEFAULT '0',
  `fri` tinyint(1) NOT NULL DEFAULT '0',
  `sat` tinyint(1) NOT NULL DEFAULT '0',
  `sun` tinyint(1) NOT NULL DEFAULT '0',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `icon` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `added` datetime NOT NULL,
  `registration_code` int(1) DEFAULT NULL,
  `registration_url` varchar(2000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `mon` (`mon`,`tue`,`wed`,`thu`,`fri`,`sat`,`sun`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=57 ;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `property` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `default` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `sort_id` int(5) NOT NULL,
  UNIQUE KEY `property_2` (`property`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `special_notes`
--

CREATE TABLE IF NOT EXISTS `special_notes` (
  `date` date NOT NULL,
  `notes` text NOT NULL,
  `dailyEventsDisabled` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) NOT NULL,
  `sort_id` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sort_id` (`sort_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `tag`, `sort_id`) VALUES
(1, 'Featured Events', 1),
(2, 'Camps & Classes', 2),
(3, 'The Lab', 3),
(4, 'Members Only', 4),
(5, 'Adult Events', 5);
