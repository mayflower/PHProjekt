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
  `table_name` varchar(50) default NULL,
  `table_field` varchar(60) default NULL,
  `form_tab` int(11) default NULL,
  `form_label` varchar(255) default NULL,
  `form_tooltip` varchar(255) default NULL,
  `form_type` varchar(50) default NULL,
  `form_position` int(11) default NULL,
  `form_columns` int(11) default NULL,
  `form_regexp` varchar(255) default NULL,
  `form_range` text,
  `default_value` varchar(255) default NULL,
  `list_position` int(11) default NULL,
  `list_align` varchar(20) default NULL,
  `list_use_filter` tinyint(4) default NULL,
  `alt_position` int(11) default NULL,
  `status` varchar(20) default NULL,
  `is_integer` tinyint(4) default NULL,
  `is_required` tinyint(4) default NULL,
  `is_unique` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `database_manager`
--

LOCK TABLES `database_manager` WRITE;
/*!40000 ALTER TABLE `database_manager` DISABLE KEYS */;
/*!40000 ALTER TABLE `database_manager` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `id` mediumint(9) NOT NULL auto_increment,
  `user_id` mediumint(9) NOT NULL,
  `dataobject_id` mediumint(9) NOT NULL,
  `module` varchar(50) NOT NULL,
  `old_value` varchar(100) NOT NULL,
  `new_value` varchar(250) NOT NULL,
  `action` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
  `project_id` mediumint(9) default NULL,
  `module` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

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
  `path` varchar(25) NOT NULL,
  `title` varchar(250) NOT NULL,
  `owner_id` mediumint(9) default NULL,
  `start_date` datetime default NULL,
  `end_date` datetime default NULL,
  `priority` int(11) default NULL,
  `current_status` varchar(50) NOT NULL,
  `complete_percent` float default '0',
  `hourly_wage_rate` float default NULL,
  `budget` float default NULL,
  PRIMARY KEY  (`id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `project`
--

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;
INSERT INTO `project` VALUES (1,NULL,'/','Test Project',NULL,NULL,NULL,NULL,'ordered',0,NULL,NULL);
/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_user_role_rel`
--

DROP TABLE IF EXISTS `project_user_role_rel`;
CREATE TABLE `project_user_role_rel` (
  `project_id` mediumint(9) NOT NULL,
  `user_id` mediumint(9) NOT NULL,
  `role_id` mediumint(9) NOT NULL,
  KEY `project_id` (`project_id`),
  KEY `role_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
  `name` varchar(200) default NULL,
  `module` varchar(250) NOT NULL,
  `permission` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

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
  `ID` int(11) NOT NULL auto_increment,
  `role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

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
  `user_id` mediumint(9) NOT NULL,
  `key_value` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  `module` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_module_setting`
--

LOCK TABLES `user_module_setting` WRITE;
/*!40000 ALTER TABLE `user_module_setting` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_module_setting` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-07-19 15:30:15
