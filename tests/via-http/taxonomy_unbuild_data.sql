-- phpMyAdmin SQL Dump
-- version 3.3.10
-- http://www.phpmyadmin.net
--
-- Host: 192.168.50.1
-- Generation Time: Aug 24, 2016 at 06:29 PM
-- Server version: 1.0.110
-- PHP Version: 5.6.25

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `github_rundiz_nested-set`
--

-- --------------------------------------------------------

--
-- Table structure for table `test_taxonomy`
--

CREATE TABLE IF NOT EXISTS `test_taxonomy` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) NOT NULL DEFAULT '0' COMMENT 'refer to this table column id. this column value must be integer. if it is root then this value must be 0, it can not be NULL.',
  `name` varchar(255) DEFAULT NULL COMMENT 'taxonomy name',
  `position` int(9) NOT NULL DEFAULT '0' COMMENT 'position when sort/order tags item.',
  `level` int(10) NOT NULL DEFAULT '1' COMMENT 'deep level of taxonomy hierarchy. begins at 1 (no sub items).',
  `left` int(10) NOT NULL DEFAULT '0' COMMENT 'for nested set model calculation. this will be able to select filtered parent id and all of its children.',
  `right` int(10) NOT NULL DEFAULT '0' COMMENT 'for nested set model calculation. this will be able to select filtered parent id and all of its children.',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='contain taxonomy data such as category.' AUTO_INCREMENT=21 ;

--
-- Dumping data for table `test_taxonomy`
--

INSERT INTO `test_taxonomy` (`id`, `parent_id`, `name`, `position`, `level`, `left`, `right`) VALUES
(1, 0, 'Root 1', 1, 0, 0, 0),
(2, 0, 'Root 2', 2, 0, 0, 0),
(3, 0, 'Root 3', 3, 0, 0, 0),
(4, 2, '2.1', 1, 0, 0, 0),
(5, 2, '2.2', 2, 0, 0, 0),
(6, 2, '2.3', 3, 0, 0, 0),
(7, 2, '2.4', 4, 0, 0, 0),
(8, 2, '2.5', 5, 0, 0, 0),
(9, 4, '2.1.1', 1, 0, 0, 0),
(10, 4, '2.1.2', 2, 0, 0, 0),
(11, 4, '2.1.3', 3, 0, 0, 0),
(12, 9, '2.1.1.1', 1, 0, 0, 0),
(13, 9, '2.1.1.2', 2, 0, 0, 0),
(14, 9, '2.1.1.3', 3, 0, 0, 0),
(15, 3, '3.1', 1, 0, 0, 0),
(16, 3, '3.2', 2, 0, 0, 0),
(17, 3, '3.3', 3, 0, 0, 0),
(18, 16, '3.2.1', 1, 0, 0, 0),
(19, 16, '3.2.2', 2, 0, 0, 0),
(20, 16, '3.3.3', 3, 0, 0, 0);
