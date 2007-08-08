-- MySQL dump 10.11
--
-- Host: localhost    Database: phprojekt-mvc
-- ------------------------------------------------------
-- Server version	5.0.38-Ubuntu_0ubuntu1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `DatabaseManager`
--

DROP TABLE IF EXISTS `DatabaseManager`;
CREATE TABLE `DatabaseManager` (
  `id` mediumint(9) NOT NULL auto_increment,
  `tableName` varchar(50) default NULL,
  `tableField` varchar(60) default NULL,
  `formTab` int(11) default NULL,
  `formLabel` varchar(255) default NULL,
  `formTooltip` varchar(255) default NULL,
  `formType` varchar(50) default NULL,
  `formPosition` int(11) default NULL,
  `formColumns` int(11) default NULL,
  `formRegexp` varchar(255) default NULL,
  `formRange` text,
  `defaultValue` varchar(255) default NULL,
  `listPosition` int(11) default NULL,
  `listAlign` varchar(20) default NULL,
  `listUseFilter` tinyint(4) default NULL,
  `altPosition` int(11) default NULL,
  `status` varchar(20) default NULL,
  `isInteger` tinyint(4) default NULL,
  `isRequired` tinyint(4) default NULL,
  `isUnique` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 ;

--
-- Dumping data for table `DatabaseManager`
--

LOCK TABLES `DatabaseManager` WRITE;
/*!40000 ALTER TABLE `DatabaseManager` DISABLE KEYS */;
INSERT INTO `DatabaseManager` VALUES (1,'project','title',1,'title','title','text',1,1,'',NULL,'',1,'left',1,1,'',0,1,0),(2,'project','notes',1,'notes','notes','textarea',2,2,'',NULL,'',3,'left',1,2,'1',0,1,0),(3,'project','priority',1,'priority','priority','text',3,1,NULL,NULL,'5',2,NULL,NULL,NULL,NULL,1,NULL,NULL);
/*!40000 ALTER TABLE `DatabaseManager` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `History`
--

DROP TABLE IF EXISTS `History`;
CREATE TABLE `History` (
  `id` mediumint(9) NOT NULL auto_increment,
  `userId` mediumint(9) NOT NULL,
  `dataobjectId` mediumint(9) NOT NULL,
  `module` varchar(50) NOT NULL,
  `field` varchar(255) NOT NULL,
  `oldValue` varchar(100) default NULL,
  `newValue` varchar(250) default NULL,
  `action` varchar(50) NOT NULL,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`),
  KEY `History_userId` (`userId`)
);

--
-- Table structure for table `ModuleInstance`
--

DROP TABLE IF EXISTS `ModuleInstance`;
CREATE TABLE `ModuleInstance` (
  `id` mediumint(9) NOT NULL auto_increment,
  `projectId` mediumint(9) default NULL,
  `module` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB AUTO_INCREMENT=3;

--
-- Dumping data for table `ModuleInstance`
--

LOCK TABLES `ModuleInstance` WRITE;
/*!40000 ALTER TABLE `ModuleInstance` DISABLE KEYS */;
INSERT INTO `ModuleInstance` VALUES (1,1,'Task','Developer Tasks'),(2,1,'Tasks','Project Tasks');
/*!40000 ALTER TABLE `ModuleInstance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `Project`;
CREATE TABLE `Project` (
  `id` mediumint(9) NOT NULL auto_increment,
  `parent` mediumint(9) default NULL,
  `path` varchar(25) NOT NULL default '\\',
  `title` varchar(250) NOT NULL,
  `notes` text default NULL,
  `ownerId` mediumint(9) default NULL,
  `startDate` date default NULL,
  `endDate` date default NULL,
  `priority` int(11) default NULL,
  `currentStatus` varchar(50) NOT NULL default 'working',
  `completePercent` float default '0',
  `hourlyWageRate` float default NULL,
  `budget` float default NULL,
  PRIMARY KEY  (`id`),
  KEY `ownerId` (`ownerId`)
) ENGINE=InnoDB AUTO_INCREMENT=2;

--
-- Dumping data for table `project`
--

LOCK TABLES `Project` WRITE;
/*!40000 ALTER TABLE `Project` DISABLE KEYS */;
INSERT INTO `Project` VALUES (1,NULL,'/','Test Project','Test note',NULL,NULL,NULL,NULL,'ordered',0,NULL,NULL);
/*!40000 ALTER TABLE `Project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ProjectUserRoleRelation`
--

DROP TABLE IF EXISTS `ProjectUserRoleRelation`;
CREATE TABLE `ProjectUserRoleRelation` (
  `projectId` mediumint(9) NOT NULL,
  `userId` mediumint(9) NOT NULL,
  `roleId` mediumint(9) NOT NULL,
  KEY `projectId` (`projectId`),
  KEY `roleId` (`roleId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB ;

--
-- Dumping data for table `ProjectUserRoleRelation`
--

LOCK TABLES `ProjectUserRoleRelation` WRITE;
/*!40000 ALTER TABLE `ProjectUserRoleRelation` DISABLE KEYS */;
/*!40000 ALTER TABLE `ProjectUserRoleRelation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `Role`;
CREATE TABLE `Role` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL,
  `module` varchar(250) NOT NULL,
  `permission` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3;

--
-- Dumping data for table `role`
--

LOCK TABLES `Role` WRITE;
/*!40000 ALTER TABLE `Role` DISABLE KEYS */;
INSERT INTO `Role` VALUES (1,'Developer','Task','read'),(2,'Senior Developer','Task','write');
/*!40000 ALTER TABLE `Role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RoleUserRelation`
--

DROP TABLE IF EXISTS `RoleUserRelation`;
CREATE TABLE `RoleUserRelation` (
  `id` int(11) NOT NULL auto_increment,
  `roleId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3;

--
-- Dumping data for table `RoleUserRelation`
--

LOCK TABLES `RoleUserRelation` WRITE;
/*!40000 ALTER TABLE `RoleUserRelation` DISABLE KEYS */;
INSERT INTO `RoleUserRelation` VALUES (1,1,1),(2,2,1);
/*!40000 ALTER TABLE `RoleUserRelation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
  `id` mediumint(9) NOT NULL auto_increment,
  `username` varchar(250) NOT NULL,
  `password` varchar(32) NOT NULL,
  `firstname` varchar(250) default NULL,
  `lastname` varchar(250) default NULL,
  `language` varchar(5) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2;

--
-- Dumping data for table `user`
--

LOCK TABLES `User` WRITE;
/*!40000 ALTER TABLE `User` DISABLE KEYS */;
INSERT INTO `User` VALUES (1,'david','ab003765f3424bf8e2c8d1d69762d72c',NULL,NULL,'de_DE');
/*!40000 ALTER TABLE `User` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UserModuleSetting`
--

DROP TABLE IF EXISTS `UserModuleSetting`;
CREATE TABLE `UserModuleSetting` (
  `id` mediumint(9) NOT NULL auto_increment,
  `userId` mediumint(9) NOT NULL,
  `keyValue` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  `kind` varchar(250) NOT NULL,
  `module` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB ;

--
-- Dumping data for table `UserModuleSetting`
--

LOCK TABLES `UserModuleSetting` WRITE;
/*!40000 ALTER TABLE `UserModuleSetting` DISABLE KEYS */;
/*!40000 ALTER TABLE `UserModuleSetting` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `Tree`;
CREATE TABLE `Tree` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent` int(10) unsigned default NULL,
  `path` varchar(255) NOT NULL,
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tree`
--

LOCK TABLES `Tree` WRITE;
/*!40000 ALTER TABLE `Tree` DISABLE KEYS */;
INSERT INTO `Tree` VALUES (2,NULL,'/','Root'),(3,2,'/2/','Child 1'),(4,2,'/2/','Child 2'),(5,4,'/2/4/','Sub Child 1'),(6,4,'/2/4/','Sub Child 2'),(7,2,'/2/','Child 3');
/*!40000 ALTER TABLE `Tree` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
