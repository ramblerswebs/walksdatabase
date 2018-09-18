--
-- Database: `rwalks`
--
CREATE DATABASE IF NOT EXISTS `rwalks` DEFAULT CHARACTER SET utf8;
USE `rwalks`;

--
-- Table structure for table `sessions`
--
CREATE TABLE `sessions` (
  `sessionsId` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `userAgentString` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `logInDateTime` datetime NOT NULL,
  `logOutDateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`sessionsId`)
) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `startPoints`
--
CREATE TABLE `startPoints` (
  `startPointId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `startPoint` varchar(255) NOT NULL,
  `gridRef` varchar(11) NOT NULL,
  `explorer` smallint(5) unsigned DEFAULT NULL,
  `explorerNew` smallint(5) unsigned DEFAULT NULL,
  `landRanger` smallint(5) unsigned DEFAULT NULL,
  `postCode` varchar(9) DEFAULT NULL,
  PRIMARY KEY (`startPointId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `user`
--
CREATE TABLE `user` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `userName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstName` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastName` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1',
  `adminLevel` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `walksProg`
--
CREATE TABLE `walksProg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `walkDate` date NOT NULL DEFAULT '0000-00-00',
  `startTime` time NOT NULL DEFAULT '00:00:00',
  `startTimePm` time DEFAULT NULL,
  `walkTitle` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `walkGrade` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'M',
  `terrain` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
  `distance` float unsigned NOT NULL DEFAULT '0',
  `distancePm` float DEFAULT NULL,
  `startPointId` smallint(5) unsigned DEFAULT '0',
  `startPoint` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `gridRef` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `explorer` smallint(5) unsigned DEFAULT NULL,
  `explorerNew` smallint(5) unsigned DEFAULT NULL,
  `explorer2` smallint(5) unsigned DEFAULT NULL,
  `explorer3` smallint(5) unsigned DEFAULT NULL,
  `landRanger` smallint(5) unsigned DEFAULT NULL,
  `landRanger2` smallint(5) unsigned DEFAULT NULL,
  `postCode` varchar(9) COLLATE utf8_unicode_ci DEFAULT '',
  `keyLocations` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `keyLocationsPm` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `leader` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `leaderLandline` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `leaderMobile` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mobileOpt` tinyint(3) unsigned DEFAULT '0',
  `leader2` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `leader2Tel` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refreshmentStopType` tinyint(3) unsigned DEFAULT '0',
  `refreshmentStopDetails` varchar(55) COLLATE utf8_unicode_ci DEFAULT '',
  `refreshmentStop2Details` varchar(55) COLLATE utf8_unicode_ci DEFAULT '',
  `repeatType` tinyint(3) unsigned DEFAULT '0',
  `repeatDate` date DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT '0',
  `repeatId` int(10) unsigned DEFAULT '0',
  `userId` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
