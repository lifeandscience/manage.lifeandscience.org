-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 23, 2013 at 12:11 PM
-- Server version: 5.5.33
-- PHP Version: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

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
  `display_date` text NOT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `sun_start_time` time DEFAULT NULL,
  `sun_end_time` time DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL,
  `fb_link` text NOT NULL,
  `description` text NOT NULL,
  `special_note` text NOT NULL,
  `custom_1` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
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
  KEY `tags` (`tags`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=141 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_weekly`
--

CREATE TABLE `events_weekly` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=56 ;

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

-- --------------------------------------------------------

--
-- Table structure for table `special_notes`
--

CREATE TABLE `special_notes` (
  `date` date NOT NULL,
  `notes` text NOT NULL,
  UNIQUE KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) NOT NULL,
  `sort_id` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sort_id` (`sort_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `tag`, `sort_id`) VALUES
(1, 'Featured', 1),
(2, 'Camps & Classes', 2),
(3, 'The Lab', 3),
(4, 'Members Only', 4),
(5, 'Adult Events', 5);
