-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 28, 2012 at 12:08 PM
-- Server version: 5.5.9
-- PHP Version: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `cscms`
--

-- --------------------------------------------------------

--
-- Table structure for table `cscms_sessions`
--

CREATE TABLE `cscms_sessions` (
  `uid` int(11) NOT NULL,
  `sid` varchar(32) NOT NULL DEFAULT '',
  `hostname` varchar(128) DEFAULT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `useragent` varchar(255) NOT NULL,
  `mode` enum('active','kill','ban','update') NOT NULL DEFAULT 'active',
  `store` longblob,
  PRIMARY KEY (`sid`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cscms_sessions`
--

INSERT INTO `cscms_sessions` VALUES(1, '06a6700675503b26b940ce26ba155dcb', '127.0.0.1', 1348830501, 'Mozilla Firefox Bishes', 'active', 0x4e3b);
INSERT INTO `cscms_sessions` VALUES(1, '30f4642b71cd07cc22b79f4123352917', '127.0.0.1', 1348798101, 'Mozilla Firefox Bishes', 'active', 0x4e3b);
INSERT INTO `cscms_sessions` VALUES(1, '593cf799f92a85d187e82f1941da3cef', '127.0.0.1', 1348819701, 'Mozilla Firefox Bishes', 'active', 0x4e3b);
INSERT INTO `cscms_sessions` VALUES(1, '6ec30600919b28379d436db189f526cb', '127.0.0.1', 1348805301, 'Mozilla Firefox Bishes', 'active', 0x4e3b);
INSERT INTO `cscms_sessions` VALUES(1, '7a2eb173ea985b4ed52227d902733616', '127.0.0.1', 1348823301, 'Mozilla Firefox Bishes', 'active', 0x4e3b);
INSERT INTO `cscms_sessions` VALUES(1, '85e2e78d454d94cacaf7eb757587b50d', '127.0.0.1', 1348826901, 'Mozilla Firefox Bishes', 'active', 0x4e3b);
INSERT INTO `cscms_sessions` VALUES(1, 'c2f319b2731b7571a0720f812749696d', '127.0.0.1', 1348812501, 'Mozilla Firefox Bishes', 'active', 0x4e3b);
INSERT INTO `cscms_sessions` VALUES(1, 'cdffc5a3b8f930658f02e68c4ba2efa1', '127.0.0.1', 1348808901, 'Mozilla Firefox Bishes', 'active', 0x4e3b);
INSERT INTO `cscms_sessions` VALUES(1, 'd41370b4d5a2ee60c8d31a7b3ef29a3d', '127.0.0.1', 1348816101, 'Mozilla Firefox Bishes', 'active', 0x4e3b);
INSERT INTO `cscms_sessions` VALUES(1, 'e711929ddcdac96a64761c334fc4ba5a', '127.0.0.1', 1348830293, 'Mozilla Firefox Bishes', 'active', 0x4e3b);
INSERT INTO `cscms_sessions` VALUES(1, 'f22f4d12697afa9ebe0df334e115b503', '127.0.0.1', 1348801701, 'Mozilla Firefox Bishes', 'active', 0x4e3b);