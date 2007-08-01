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
-- Table structure for table `database_manager`
--

DROP TABLE IF EXISTS `database_manager`;
CREATE TABLE `database_manager` (
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
-- Dumping data for table `database_manager`
--

LOCK TABLES `database_manager` WRITE;
/*!40000 ALTER TABLE `database_manager` DISABLE KEYS */;
INSERT INTO `database_manager` VALUES (1,'project','title',1,'title','title','text',1,1,'',NULL,'',1,'left',1,1,'',0,1,0),(2,'project','notes',1,'notes','notes','textarea',2,2,'',NULL,'',3,'left',1,2,'1',0,1,0),(3,'project','priority',1,'priority','priority','text',3,1,NULL,NULL,'5',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `database_manager` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `id` mediumint(9) NOT NULL auto_increment,
  `userId` mediumint(9) NOT NULL,
  `dataobjectId` mediumint(9) NOT NULL,
  `module` varchar(50) NOT NULL,
  `oldValue` varchar(100) NOT NULL,
  `newValue` varchar(250) NOT NULL,
  `action` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB ;

--
-- Dumping data for table `history`
--

LOCK TABLES `history` WRITE;
/*!40000 ALTER TABLE `history` DISABLE KEYS */;
/*!40000 ALTER TABLE `history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_instance`
--

DROP TABLE IF EXISTS `module_instance`;
CREATE TABLE `module_instance` (
  `id` mediumint(9) NOT NULL auto_increment,
  `projectId` mediumint(9) default NULL,
  `module` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB AUTO_INCREMENT=3;

--
-- Dumping data for table `module_instance`
--

LOCK TABLES `module_instance` WRITE;
/*!40000 ALTER TABLE `module_instance` DISABLE KEYS */;
INSERT INTO `module_instance` VALUES (1,1,'Task','Developer Tasks'),(2,1,'Tasks','Project Tasks');
/*!40000 ALTER TABLE `module_instance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` mediumint(9) NOT NULL auto_increment,
  `parent` mediumint(9) default NULL,
  `path` varchar(25) NOT NULL default '\\',
  `title` varchar(250) NOT NULL,
  `notes` text NOT NULL,
  `ownerId` mediumint(9) default NULL,
  `startDate` datetime default NULL,
  `endDate` datetime default NULL,
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

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;
INSERT INTO `project` VALUES (1,NULL,'/','Test Project','Test note',NULL,NULL,NULL,NULL,'ordered',0,NULL,NULL);
/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_user_role_rel`
--

DROP TABLE IF EXISTS `project_user_role_rel`;
CREATE TABLE `project_user_role_rel` (
  `projectId` mediumint(9) NOT NULL,
  `userId` mediumint(9) NOT NULL,
  `roleId` mediumint(9) NOT NULL,
  KEY `projectId` (`projectId`),
  KEY `roleId` (`roleId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB ;

--
-- Dumping data for table `project_user_role_rel`
--

LOCK TABLES `project_user_role_rel` WRITE;
/*!40000 ALTER TABLE `project_user_role_rel` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_user_role_rel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL,
  `module` varchar(250) NOT NULL,
  `permission` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'Developer','Task','read'),(2,'Senior Developer','Task','write');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user_rel`
--

DROP TABLE IF EXISTS `role_user_rel`;
CREATE TABLE `role_user_rel` (
  `id` int(11) NOT NULL auto_increment,
  `roleId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3;

--
-- Dumping data for table `role_user_rel`
--

LOCK TABLES `role_user_rel` WRITE;
/*!40000 ALTER TABLE `role_user_rel` DISABLE KEYS */;
INSERT INTO `role_user_rel` VALUES (1,1,1),(2,2,1);
/*!40000 ALTER TABLE `role_user_rel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
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

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'david','ab003765f3424bf8e2c8d1d69762d72c',NULL,NULL,'de_DE');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_module_setting`
--

DROP TABLE IF EXISTS `user_module_setting`;
CREATE TABLE `user_module_setting` (
  `id` mediumint(9) NOT NULL auto_increment,
  `userId` mediumint(9) NOT NULL,
  `keyValue` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  `module` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB ;

--
-- Dumping data for table `user_module_setting`
--

LOCK TABLES `user_module_setting` WRITE;
/*!40000 ALTER TABLE `user_module_setting` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_module_setting` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `tree`;
CREATE TABLE `tree` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent` int(10) unsigned default NULL,
  `path` varchar(255) NOT NULL,
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tree`
--

LOCK TABLES `tree` WRITE;
/*!40000 ALTER TABLE `tree` DISABLE KEYS */;
INSERT INTO `tree` VALUES (2,NULL,'/','Root'),(3,2,'/2/','Child 1'),(4,2,'/2/','Child 2'),(5,4,'/2/4/','Sub Child 1'),(6,4,'/2/4/','Sub Child 2'),(7,2,'/2/','Child 3');
/*!40000 ALTER TABLE `tree` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


--
-- Updates
--
UPDATE database_manager set isInteger = 1 WHERE id = 3
