-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 25, 2012 at 03:06 AM
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
  `start_time` varchar(11) NOT NULL,
  `end_time` varchar(11) NOT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
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
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `active` (`active`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_weekly`
--

CREATE TABLE `events_weekly` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `day_of_week` varchar(255) NOT NULL,
  `start_time` varchar(11) NOT NULL,
  `end_time` varchar(11) NOT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `icon` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `day_of_week` (`day_of_week`),
  KEY `active` (`active`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;

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
