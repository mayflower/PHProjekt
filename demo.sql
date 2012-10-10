-- MySQL dump 10.13  Distrib 5.5.24, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: phprojekt
-- ------------------------------------------------------
-- Server version	5.5.24-0ubuntu0.12.04.1

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
-- Table structure for table `calendar2`
--

DROP TABLE IF EXISTS `calendar2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL DEFAULT '1',
  `summary` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `last_end` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `rrule` varchar(255) DEFAULT NULL,
  `recurrence_id` datetime DEFAULT NULL,
  `visibility` int(1) DEFAULT '1',
  `uid` varchar(255) NOT NULL,
  `last_modified` datetime NOT NULL,
  `uri` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar2`
--


--
-- Table structure for table `calendar2_excluded_dates`
--

DROP TABLE IF EXISTS `calendar2_excluded_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar2_excluded_dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `calendar2_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar2_excluded_dates`
--


--
-- Table structure for table `calendar2_user_relation`
--

DROP TABLE IF EXISTS `calendar2_user_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar2_user_relation` (
  `calendar2_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `confirmation_status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`calendar2_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar2_user_relation`
--


--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `key_value` varchar(255) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuration`
--


--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `firstphone` varchar(255) DEFAULT NULL,
  `secondphone` varchar(255) DEFAULT NULL,
  `mobilephone` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `zipcode` varchar(50) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `comment` text,
  `private` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` VALUES (1,1,1,'Test','Test@test.de','','6516','65131216','324','65','6516','','','\n',0),(2,1,1,'ftghg','ghdfgh@dfhfdg.de','sdt436t5z','3q54rjzhmg','w4uj6rsnzfgfb','7i468okirumjdh','46hjwezrsnfg','','68okruejzthsd','sfdhkulr8o','\n37657i468o57p9lruktdzhnfh',0);

--
-- Table structure for table `database_manager`
--

DROP TABLE IF EXISTS `database_manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `database_manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(50) DEFAULT NULL,
  `table_field` varchar(60) DEFAULT NULL,
  `form_tab` int(11) DEFAULT NULL,
  `form_label` varchar(255) DEFAULT NULL,
  `form_type` varchar(50) DEFAULT NULL,
  `form_position` int(11) DEFAULT NULL,
  `form_columns` int(11) DEFAULT NULL,
  `form_regexp` varchar(255) DEFAULT NULL,
  `form_range` text,
  `default_value` varchar(255) DEFAULT NULL,
  `list_position` int(11) DEFAULT NULL,
  `list_align` varchar(20) DEFAULT NULL,
  `list_use_filter` int(4) DEFAULT NULL,
  `alt_position` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `is_integer` int(4) DEFAULT NULL,
  `is_required` int(4) DEFAULT NULL,
  `is_unique` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=83 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_manager`
--

INSERT INTO `database_manager` VALUES (1,'Project','title',1,'Title','text',1,1,NULL,NULL,NULL,1,'left',1,2,'1',0,1,0),(2,'Project','notes',1,'Notes','textarea',2,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(3,'Project','project_id',1,'Parent','selectValues',3,1,NULL,'Project#id#title',NULL,0,NULL,1,0,'1',1,1,0),(4,'Project','start_date',1,'Start date','date',4,1,NULL,NULL,NULL,3,'center',1,3,'1',0,0,0),(5,'Project','end_date',1,'End date','date',5,1,NULL,NULL,NULL,4,'center',1,4,'1',0,0,0),(6,'Project','priority',1,'Priority','rating',6,1,NULL,'10','5',5,'center',1,5,'1',1,0,0),(7,'Project','current_status',1,'Current status','selectValues',7,1,NULL,'1#Offered|2#Ordered|3#Working|4#Ended|5#Stopped|6#Re-Opened|7#Waiting','1',6,'center',1,6,'1',1,0,0),(8,'Project','complete_percent',1,'Complete percent','percentage',8,1,NULL,NULL,NULL,7,'center',1,7,'1',0,0,0),(9,'Project','budget',1,'Budget','text',9,1,NULL,NULL,NULL,0,NULL,1,8,'1',0,0,0),(10,'Project','hourly_wage_rate',1,'Hourly wage rate','text',10,1,NULL,NULL,NULL,0,NULL,1,0,'0',0,0,0),(11,'Project','contact_id',1,'Contact','selectValues',11,1,NULL,'Contact#id#name',NULL,0,NULL,1,9,'1',1,0,0),(72,'Contact','country',1,'Country','text',10,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(71,'Contact','zipcode',1,'Zip code','text',9,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(70,'Contact','city',1,'City','text',8,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(69,'Contact','street',1,'Street','text',7,1,NULL,NULL,NULL,4,'left',1,0,'1',0,0,0),(68,'Contact','mobilephone',1,'Mobile phone','text',6,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(67,'Contact','secondphone',1,'Second phone','text',5,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(66,'Contact','firstphone',1,'First phone','text',4,1,NULL,NULL,NULL,3,'left',1,0,'1',0,0,0),(65,'Contact','company',1,'Company','text',3,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(64,'Contact','email',1,'E-Mail','text',2,1,NULL,NULL,NULL,2,'left',1,0,'1',0,0,0),(63,'Contact','name',1,'Name','text',1,1,NULL,NULL,NULL,1,'left',1,0,'1',0,1,0),(80,'Filemanager','comments',1,'Comments','textarea',2,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(81,'Filemanager','project_id',1,'Project','selectValues',3,1,NULL,'Project#id#title',NULL,0,'0',1,0,'1',1,1,0),(82,'Filemanager','files',1,'Upload','upload',5,1,NULL,NULL,NULL,3,'center',1,0,'1',0,0,0),(28,'Helpdesk','title',1,'Title','text',1,1,NULL,NULL,NULL,1,'center',1,0,'1',0,1,0),(29,'Helpdesk','assigned',1,'Assigned','selectValues',3,1,NULL,'User#id#lastname',NULL,4,'center',1,0,'1',1,0,0),(30,'Helpdesk','date',1,'Date','display',4,1,NULL,NULL,NULL,2,'center',1,0,'1',0,1,0),(31,'Helpdesk','project_id',1,'Project','selectValues',6,1,NULL,'Project#id#title',NULL,0,NULL,1,0,'1',1,1,0),(32,'Helpdesk','priority',1,'Priority','rating',7,1,NULL,'10','5',5,'center',1,0,'1',1,0,0),(33,'Helpdesk','attachments',1,'Attachments','upload',8,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(34,'Helpdesk','description',1,'Description','textarea',11,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(35,'Helpdesk','status',1,'Status','selectValues',12,1,NULL,'1#Open|2#Assigned|3#Solved|4#Verified|5#Closed','1',6,'center',1,0,'1',1,1,0),(36,'Helpdesk','due_date',1,'Due date','date',5,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(37,'Helpdesk','author',1,'Author','display',2,1,NULL,'User#id#lastname',NULL,3,'center',1,0,'1',1,1,0),(38,'Helpdesk','solved_by',1,'Solved by','display',9,1,NULL,'User#id#lastname',NULL,0,NULL,1,0,'1',1,0,0),(39,'Helpdesk','solved_date',1,'Solved date','display',10,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(40,'Helpdesk','contact_id',1,'Contact','selectValues',13,1,NULL,'Contact#id#name',NULL,0,NULL,1,0,'1',1,0,0),(41,'Minutes','title',1,'Title','text',1,1,NULL,NULL,NULL,3,'center',1,0,'1',0,1,0),(42,'Minutes','meeting_datetime',1,'Start','datetime',2,1,NULL,NULL,NULL,1,'center',1,0,'1',0,1,0),(43,'Minutes','end_time',1,'End','time',3,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(44,'Minutes','project_id',1,'Project','selectValues',4,1,NULL,'Project#id#title',NULL,0,NULL,1,0,'1',1,1,0),(45,'Minutes','description',1,'Description','textarea',5,1,NULL,NULL,NULL,4,'center',1,0,'1',0,0,0),(46,'Minutes','place',1,'Place','text',6,1,NULL,NULL,NULL,5,'center',1,0,'1',0,0,0),(47,'Minutes','moderator',1,'Moderator','text',7,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(48,'Minutes','participants_invited',2,'Invited','multipleSelectValues',8,1,NULL,'User#id#username',NULL,0,NULL,1,0,'1',0,0,0),(49,'Minutes','participants_attending',2,'Attending','multipleSelectValues',9,1,NULL,'User#id#username',NULL,0,NULL,1,0,'1',0,0,0),(50,'Minutes','participants_excused',2,'Excused','multipleSelectValues',10,1,NULL,'User#id#username',NULL,0,NULL,1,0,'1',0,0,0),(51,'Minutes','item_status',1,'Status','selectValues',11,1,NULL,'1#Planned|2#Empty|3#Filled|4#Final','1',6,'center',1,0,'1',1,0,0),(52,'Note','project_id',1,'Project','selectValues',3,1,NULL,'Project#id#title',NULL,0,NULL,1,0,'1',1,1,0),(53,'Note','title',1,'Title','text',1,1,NULL,NULL,NULL,1,'left',1,2,'1',0,1,0),(54,'Note','comments',1,'Comments','textarea',2,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(55,'Todo','title',1,'Title','text',1,1,NULL,NULL,NULL,1,'left',1,2,'1',0,1,0),(56,'Todo','notes',1,'Notes','textarea',2,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(57,'Todo','start_date',1,'Start date','date',4,1,NULL,NULL,NULL,3,'center',1,3,'1',0,0,0),(58,'Todo','end_date',1,'End date','date',5,1,NULL,NULL,NULL,4,'center',1,4,'1',0,0,0),(59,'Todo','priority',1,'Priority','rating',6,1,NULL,'10','5',5,'center',1,5,'1',1,0,0),(60,'Todo','current_status',1,'Current status','selectValues',7,1,NULL,'1#Waiting|2#Accepted|3#Working|4#Stopped|5#Ended','1',7,'center',1,6,'1',1,0,0),(61,'Todo','project_id',1,'Project','selectValues',3,1,NULL,'Project#id#title',NULL,0,NULL,1,0,'1',1,1,0),(62,'Todo','user_id',1,'User','selectValues',8,1,NULL,'User#id#lastname',NULL,6,'left',1,7,'1',1,0,0),(73,'Contact','comment',1,'Comment','textarea',11,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(74,'Contact','private',1,'Private','selectValues',12,1,NULL,'0#No|1#Yes',NULL,5,'center',1,0,'1',1,0,0),(79,'Filemanager','title',1,'Title','text',1,1,NULL,NULL,NULL,1,'center',1,0,'1',0,1,0);

--
-- Table structure for table `filemanager`
--

DROP TABLE IF EXISTS `filemanager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filemanager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `comments` text,
  `project_id` int(11) NOT NULL,
  `files` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `titleproject_id` (`title`,`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filemanager`
--

INSERT INTO `filemanager` VALUES (1,1,'testupload','\n',2,'70a9479cf7fa8aae3cd23a4220bc8fed|logo.png');

--
-- Table structure for table `frontend_message`
--

DROP TABLE IF EXISTS `frontend_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frontend_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `process` varchar(255) DEFAULT NULL,
  `recipient_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `valid_until` datetime NOT NULL,
  `valid_from` datetime NOT NULL,
  `description` text NOT NULL,
  `details` text NOT NULL,
  `delivered` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=146 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `frontend_message`
--

INSERT INTO `frontend_message` VALUES (138,1,2,'add',3,4,2,'Untitled Folder','2012-10-10 15:49:41','2012-10-10 15:47:41','has created the new entry','a:2:{i:0;a:10:{s:6:\"userId\";s:1:\"3\";s:8:\"moduleId\";s:1:\"4\";s:6:\"itemId\";s:1:\"2\";s:5:\"field\";s:5:\"title\";s:5:\"label\";s:5:\"Title\";s:8:\"oldValue\";s:15:\"Untitled Folder\";s:8:\"newValue\";s:15:\"Untitled Folder\";s:6:\"action\";s:3:\"add\";s:8:\"datetime\";s:19:\"2012-10-10 15:31:50\";s:4:\"type\";s:4:\"text\";}i:1;a:10:{s:6:\"userId\";s:1:\"3\";s:8:\"moduleId\";s:1:\"4\";s:6:\"itemId\";s:1:\"2\";s:5:\"field\";s:9:\"projectId\";s:5:\"label\";s:7:\"Project\";s:8:\"oldValue\";N;s:8:\"newValue\";s:1:\"2\";s:6:\"action\";s:3:\"add\";s:8:\"datetime\";s:19:\"2012-10-10 15:31:50\";s:4:\"type\";s:9:\"selectbox\";}}',0),(139,1,2,'add',4,4,2,'Untitled Folder','2012-10-10 15:49:41','2012-10-10 15:47:41','has created the new entry','a:2:{i:0;a:10:{s:6:\"userId\";s:1:\"3\";s:8:\"moduleId\";s:1:\"4\";s:6:\"itemId\";s:1:\"2\";s:5:\"field\";s:5:\"title\";s:5:\"label\";s:5:\"Title\";s:8:\"oldValue\";s:15:\"Untitled Folder\";s:8:\"newValue\";s:15:\"Untitled Folder\";s:6:\"action\";s:3:\"add\";s:8:\"datetime\";s:19:\"2012-10-10 15:31:50\";s:4:\"type\";s:4:\"text\";}i:1;a:10:{s:6:\"userId\";s:1:\"3\";s:8:\"moduleId\";s:1:\"4\";s:6:\"itemId\";s:1:\"2\";s:5:\"field\";s:9:\"projectId\";s:5:\"label\";s:7:\"Project\";s:8:\"oldValue\";N;s:8:\"newValue\";s:1:\"2\";s:6:\"action\";s:3:\"add\";s:8:\"datetime\";s:19:\"2012-10-10 15:31:50\";s:4:\"type\";s:9:\"selectbox\";}}',0),(140,1,2,'delete',3,4,2,'Untitled Folder','2012-10-10 16:05:12','2012-10-10 16:03:12','has deleted the entry','a:0:{}',0),(141,1,2,'delete',4,4,2,'Untitled Folder','2012-10-10 16:05:12','2012-10-10 16:03:12','has deleted the entry','a:0:{}',0),(142,1,2,'add',3,4,3,'asdf','2012-10-10 16:06:35','2012-10-10 16:04:35','has created the new entry','a:2:{i:0;a:10:{s:6:\"userId\";s:1:\"1\";s:8:\"moduleId\";s:1:\"4\";s:6:\"itemId\";s:1:\"3\";s:5:\"field\";s:5:\"title\";s:5:\"label\";s:5:\"Title\";s:8:\"oldValue\";s:4:\"asdf\";s:8:\"newValue\";s:4:\"asdf\";s:6:\"action\";s:3:\"add\";s:8:\"datetime\";s:19:\"2012-10-10 16:04:35\";s:4:\"type\";s:4:\"text\";}i:1;a:10:{s:6:\"userId\";s:1:\"1\";s:8:\"moduleId\";s:1:\"4\";s:6:\"itemId\";s:1:\"3\";s:5:\"field\";s:9:\"projectId\";s:5:\"label\";s:7:\"Project\";s:8:\"oldValue\";N;s:8:\"newValue\";s:1:\"2\";s:6:\"action\";s:3:\"add\";s:8:\"datetime\";s:19:\"2012-10-10 16:04:35\";s:4:\"type\";s:9:\"selectbox\";}}',0),(143,1,2,'add',4,4,3,'asdf','2012-10-10 16:06:35','2012-10-10 16:04:35','has created the new entry','a:2:{i:0;a:10:{s:6:\"userId\";s:1:\"1\";s:8:\"moduleId\";s:1:\"4\";s:6:\"itemId\";s:1:\"3\";s:5:\"field\";s:5:\"title\";s:5:\"label\";s:5:\"Title\";s:8:\"oldValue\";s:4:\"asdf\";s:8:\"newValue\";s:4:\"asdf\";s:6:\"action\";s:3:\"add\";s:8:\"datetime\";s:19:\"2012-10-10 16:04:35\";s:4:\"type\";s:4:\"text\";}i:1;a:10:{s:6:\"userId\";s:1:\"1\";s:8:\"moduleId\";s:1:\"4\";s:6:\"itemId\";s:1:\"3\";s:5:\"field\";s:9:\"projectId\";s:5:\"label\";s:7:\"Project\";s:8:\"oldValue\";N;s:8:\"newValue\";s:1:\"2\";s:6:\"action\";s:3:\"add\";s:8:\"datetime\";s:19:\"2012-10-10 16:04:35\";s:4:\"type\";s:9:\"selectbox\";}}',0),(144,1,2,'delete',3,4,3,'asdf','2012-10-10 16:06:44','2012-10-10 16:04:44','has deleted the entry','a:0:{}',0),(145,1,2,'delete',4,4,3,'asdf','2012-10-10 16:06:44','2012-10-10 16:04:44','has deleted the entry','a:0:{}',0);

--
-- Table structure for table `helpdesk`
--

DROP TABLE IF EXISTS `helpdesk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `helpdesk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `assigned` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `priority` int(2) DEFAULT NULL,
  `attachments` text,
  `description` text,
  `status` int(2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `author` int(11) DEFAULT NULL,
  `solved_by` int(11) DEFAULT NULL,
  `solved_date` date DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `helpdesk`
--


--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `field` varchar(255) NOT NULL,
  `old_value` text,
  `new_value` text,
  `action` varchar(50) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history`
--

INSERT INTO `history` VALUES (1,3,1,1,'name','','Test','add','2012-05-15 10:44:54'),(2,3,1,1,'email','','Test@test.de','add','2012-05-15 10:44:54'),(3,3,1,1,'firstphone','','6516','add','2012-05-15 10:44:54'),(4,3,1,1,'secondphone','','65131216','add','2012-05-15 10:44:54'),(5,3,1,1,'mobilephone','','324','add','2012-05-15 10:44:54'),(6,3,1,1,'street','','65','add','2012-05-15 10:44:54'),(7,3,1,1,'city','','6516','add','2012-05-15 10:44:54'),(8,3,1,2,'name','','ftghg','add','2012-05-15 10:48:10'),(9,3,1,2,'email','','ghdfgh@dfhfdg.de','add','2012-05-15 10:48:10'),(10,3,1,2,'company','','sdt436t5z','add','2012-05-15 10:48:10'),(11,3,1,2,'firstphone','','3q54rjzhmg','add','2012-05-15 10:48:10'),(12,3,1,2,'secondphone','','w4uj6rsnzfgfb','add','2012-05-15 10:48:10'),(13,3,1,2,'mobilephone','','7i468okirumjdh','add','2012-05-15 10:48:10'),(14,3,1,2,'street','','46hjwezrsnfg','add','2012-05-15 10:48:10'),(15,3,1,2,'zipcode','','68okruejzthsd','add','2012-05-15 10:48:10'),(16,3,1,2,'country','','sfdhkulr8o','add','2012-05-15 10:48:10'),(17,3,1,2,'comment','','\n37657i468o57p9lruktdzhnfh','add','2012-05-15 10:48:10'),(18,1,1,4,'title','','test','add','2012-10-10 13:03:26'),(19,1,1,4,'projectId','','1','add','2012-10-10 13:03:26'),(20,1,1,4,'startDate','','2012-10-10','add','2012-10-10 13:03:26'),(21,1,1,4,'endDate','','2012-10-10','add','2012-10-10 13:03:26'),(22,1,1,4,'priority','','5','add','2012-10-10 13:03:26'),(23,1,1,4,'currentStatus','','1','add','2012-10-10 13:03:26'),(24,1,1,4,'title','test','','delete','2012-10-10 13:05:01'),(25,1,1,4,'notes','\n','','delete','2012-10-10 13:05:01'),(26,1,1,4,'projectId','1','','delete','2012-10-10 13:05:01'),(27,1,1,4,'startDate','2012-10-10','','delete','2012-10-10 13:05:01'),(28,1,1,4,'endDate','2012-10-10','','delete','2012-10-10 13:05:01'),(29,1,1,4,'priority','5','','delete','2012-10-10 13:05:01'),(30,1,1,4,'currentStatus','1','','delete','2012-10-10 13:05:01'),(31,1,1,4,'completePercent','0','','delete','2012-10-10 13:05:01'),(32,1,1,4,'budget','','','delete','2012-10-10 13:05:01'),(33,1,1,4,'contactId','0','','delete','2012-10-10 13:05:01'),(34,4,1,1,'title','','testupload','add','2012-10-10 13:17:42'),(35,4,1,1,'projectId','','2','add','2012-10-10 13:17:42'),(36,4,1,1,'files','','70a9479cf7fa8aae3cd23a4220bc8fed|logo.png','add','2012-10-10 13:17:42'),(37,4,3,2,'title','','Untitled Folder','add','2012-10-10 13:31:50'),(38,4,3,2,'projectId','','2','add','2012-10-10 13:31:50'),(39,4,1,2,'title','Untitled Folder','','delete','2012-10-10 14:03:12'),(40,4,1,2,'comments','\n','','delete','2012-10-10 14:03:12'),(41,4,1,2,'projectId','2','','delete','2012-10-10 14:03:12'),(42,4,1,2,'files','','','delete','2012-10-10 14:03:12'),(43,4,1,3,'title','','asdf','add','2012-10-10 14:04:35'),(44,4,1,3,'projectId','','2','add','2012-10-10 14:04:35'),(45,4,1,3,'title','asdf','','delete','2012-10-10 14:04:44'),(46,4,1,3,'comments','\n','','delete','2012-10-10 14:04:44'),(47,4,1,3,'projectId','2','','delete','2012-10-10 14:04:44'),(48,4,1,3,'files','','','delete','2012-10-10 14:04:44');

--
-- Table structure for table `item_rights`
--

DROP TABLE IF EXISTS `item_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_rights` (
  `module_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access` int(3) NOT NULL,
  PRIMARY KEY (`module_id`,`item_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_rights`
--

INSERT INTO `item_rights` VALUES (1,1,1,255),(1,1,2,255),(1,2,3,255),(1,2,4,255),(1,2,1,255),(1,3,4,255),(1,1,3,255),(1,1,4,255),(1,3,1,255),(1,3,3,255),(4,1,4,255),(4,1,3,255),(4,1,1,255);

--
-- Table structure for table `minutes`
--

DROP TABLE IF EXISTS `minutes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `minutes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `meeting_datetime` datetime DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `place` varchar(255) DEFAULT NULL,
  `moderator` varchar(255) DEFAULT NULL,
  `participants_invited` text,
  `participants_attending` text,
  `participants_excused` text,
  `item_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `minutes`
--


--
-- Table structure for table `minutes_item`
--

DROP TABLE IF EXISTS `minutes_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `minutes_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `minutes_id` int(11) NOT NULL,
  `topic_type` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `comment` text,
  `topic_date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `minutes_item`
--


--
-- Table structure for table `module`
--

DROP TABLE IF EXISTS `module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `save_type` int(1) NOT NULL DEFAULT '0',
  `version` varchar(20) DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module`
--

INSERT INTO `module` VALUES (1,'Project','Project',0,'6.2.1',1),(2,'Calendar2','Calendar',1,'6.1.0-beta1',1),(3,'Contact','Contact',1,'6.1.0',1),(4,'Filemanager','Filemanager',0,'6.2.1',1),(5,'Gantt','Gantt',0,'6.0.0',1),(6,'Helpdesk','Helpdesk',0,'6.0.0',1),(7,'Minutes','Minute',0,'6.0.0',1),(8,'Note','Note',0,'6.0.0',1),(9,'Statistic','Statistic',0,'6.0.0',1),(10,'Timecard','Timecard',1,'6.1.4',1),(11,'Todo','Todo',0,'6.0.0',1);

--
-- Table structure for table `module_tab_relation`
--

DROP TABLE IF EXISTS `module_tab_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_tab_relation` (
  `tab_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  PRIMARY KEY (`tab_id`,`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_tab_relation`
--


--
-- Table structure for table `note`
--

DROP TABLE IF EXISTS `note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `comments` text,
  `owner_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note`
--


--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `path` varchar(50) NOT NULL DEFAULT '/',
  `title` varchar(255) NOT NULL,
  `notes` text,
  `owner_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `current_status` int(2) NOT NULL DEFAULT '3',
  `complete_percent` varchar(4) DEFAULT NULL,
  `hourly_wage_rate` varchar(10) DEFAULT NULL,
  `budget` varchar(10) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `titleproject_id` (`title`,`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project`
--

INSERT INTO `project` VALUES (1,NULL,'/','PHProjekt','Test',1,'2009-05-12','2009-07-28',1,3,'0',NULL,NULL,NULL),(2,1,'/1/','Project 1','Test',1,'2009-05-02','2009-07-02',2,3,'0',NULL,'',0),(3,2,'/1/2/','Sub Project','Test',1,'2009-05-02','2009-07-02',2,3,'0',NULL,'',0);

--
-- Table structure for table `project_module_permissions`
--

DROP TABLE IF EXISTS `project_module_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_module_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_module_permissions`
--

INSERT INTO `project_module_permissions` VALUES (7,1,1),(53,11,2),(52,9,2),(51,8,2),(50,7,2),(49,6,2),(48,5,2),(47,4,2),(57,5,3),(46,1,2),(56,1,3);

--
-- Table structure for table `project_role_user_permissions`
--

DROP TABLE IF EXISTS `project_role_user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_role_user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_role_user_permissions`
--

INSERT INTO `project_role_user_permissions` VALUES (1,1,1,1),(2,1,2,1),(17,2,4,1),(16,2,1,1),(23,3,4,1),(22,3,1,1),(24,3,3,1),(18,2,3,1);

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

INSERT INTO `role` VALUES (1,'Admin in All',NULL),(2,'can Read TODOs only',NULL),(3,'admin in PROJECTs',NULL),(4,'read only in All',NULL);

--
-- Table structure for table `role_module_permissions`
--

DROP TABLE IF EXISTS `role_module_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_module_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `access` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_module_permissions`
--

INSERT INTO `role_module_permissions` VALUES (1,3,1,139),(2,2,1,0),(3,4,1,1),(4,2,4,0),(5,4,4,1),(6,3,4,0),(7,2,5,0),(8,4,5,1),(9,3,5,0),(10,2,6,0),(11,4,6,1),(12,3,6,0),(13,2,7,0),(14,4,7,1),(15,3,7,0),(16,2,8,0),(17,4,8,1),(18,3,8,0),(19,2,9,0),(20,4,9,1),(21,3,9,0),(22,3,11,0),(23,2,11,1),(24,4,11,1),(25,1,1,139),(26,1,2,139),(27,1,3,139),(28,1,4,139),(29,1,5,139),(30,1,6,139),(31,1,7,139),(32,1,8,139),(33,1,9,139),(34,1,10,139),(35,1,11,139);

--
-- Table structure for table `search_display`
--

DROP TABLE IF EXISTS `search_display`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_display` (
  `module_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `first_display` text,
  `second_display` text,
  PRIMARY KEY (`module_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_display`
--

INSERT INTO `search_display` VALUES (1,3,2,'Sub Project','Test'),(1,2,1,'Project 1','Test'),(4,1,2,'testupload','');

--
-- Table structure for table `search_word_module`
--

DROP TABLE IF EXISTS `search_word_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_word_module` (
  `item_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `word_id` int(11) NOT NULL,
  PRIMARY KEY (`item_id`,`module_id`,`word_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_word_module`
--

INSERT INTO `search_word_module` VALUES (1,4,6),(2,1,2),(2,1,3),(3,1,2),(3,1,3),(3,1,5);

--
-- Table structure for table `search_words`
--

DROP TABLE IF EXISTS `search_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_words`
--

INSERT INTO `search_words` VALUES (5,'sub',1),(2,'project',2),(3,'test',2),(6,'testupload',1);

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `key_value` varchar(255) NOT NULL,
  `value` text,
  `identifier` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` VALUES (1,1,0,'password','2d89c2ebd8ef6d3b98b4afa339fb5b82','Core'),(2,1,0,'email','admin@admin.com','Core'),(3,1,0,'language','en','Core'),(4,1,0,'timeZone','000','Core'),(5,2,0,'password','156c3239dbfa5c5222b51514e9d12948','Core'),(6,2,0,'email','test@test.com','Core'),(7,2,0,'language','en','Core'),(8,2,0,'timeZone','000','Core'),(9,1,0,'tutorialDisplayed','true','Core'),(10,3,0,'password','7629ba5ca7702487e24d91650d332881','Core'),(11,3,0,'email','','Core'),(12,3,0,'language','en','Core'),(13,3,0,'timeZone','000','Core'),(14,4,0,'password','55518687349a1c910f39594b1b0a9b11','Core'),(15,4,0,'email','','Core'),(16,4,0,'language','de','Core'),(17,4,0,'timeZone','000','Core'),(18,3,0,'tutorialDisplayed','true','Core'),(19,4,0,'tutorialDisplayed','true','Core');

--
-- Table structure for table `tab`
--

DROP TABLE IF EXISTS `tab`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tab`
--

INSERT INTO `tab` VALUES (1,'Basic Data 1'),(2,'People');

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--


--
-- Table structure for table `tags_modules`
--

DROP TABLE IF EXISTS `tags_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags_modules` (
  `module_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `tag_user_id` int(11) NOT NULL,
  PRIMARY KEY (`module_id`,`item_id`,`tag_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags_modules`
--


--
-- Table structure for table `tags_modules_items`
--

DROP TABLE IF EXISTS `tags_modules_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags_modules_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags_modules_items`
--


--
-- Table structure for table `tags_users`
--

DROP TABLE IF EXISTS `tags_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags_users`
--


--
-- Table structure for table `timecard`
--

DROP TABLE IF EXISTS `timecard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timecard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `minutes` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `notes` text,
  `module_id` int(11) DEFAULT '1',
  `item_id` int(11) DEFAULT NULL,
  `uri` varchar(255) NOT NULL,
  `uid` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timecard`
--


--
-- Table structure for table `todo`
--

DROP TABLE IF EXISTS `todo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `todo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `notes` text,
  `owner_id` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `current_status` int(2) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `todo`
--


--
-- Table structure for table `uploaded_unused_files`
--

DROP TABLE IF EXISTS `uploaded_unused_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uploaded_unused_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uploaded_unused_files`
--

INSERT INTO `uploaded_unused_files` VALUES (1,'2012-10-10 17:17:21','70a9479cf7fa8aae3cd23a4220bc8fed');

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `status` varchar(1) DEFAULT 'A',
  `admin` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

INSERT INTO `user` VALUES (1,'admin','Admin','Admin','A',1),(2,'test','Test','Test','I',0),(3,'english','English','Standard','A',0),(4,'deutsch','Deutscher','Benutzer','A',0);

--
-- Table structure for table `user_proxy`
--

DROP TABLE IF EXISTS `user_proxy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_proxy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proxyed_id` int(11) NOT NULL,
  `proxying_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_proxy`
--

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-10-10 18:20:11
