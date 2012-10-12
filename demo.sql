-- MySQL dump 10.13  Distrib 5.1.62, for pc-linux-gnu (x86_64)
--
-- Host: localhost    Database: phprojekt
-- ------------------------------------------------------
-- Server version	5.1.62

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar2`
--

LOCK TABLES `calendar2` WRITE;
/*!40000 ALTER TABLE `calendar2` DISABLE KEYS */;
INSERT INTO `calendar2` VALUES (1,1,'Release','','','','2012-10-25 08:00:00','2012-10-25 09:00:00','2012-10-25 09:00:00',3,'',NULL,1,'7145653-1350001487-5106@bernd','2012-10-12 00:24:47','7145653-1350001487-5106@bernd'),(2,1,'Business Lunch','','','','2012-10-02 08:00:00','2012-10-02 09:00:00','2012-10-02 09:00:00',3,'FREQ=WEEKLY;UNTIL=20121002T080000Z;INTERVAL=1;BYDAY=;WKST=TU',NULL,1,'1417543423-1350001522-5108@bernd','2012-10-12 00:25:51','1417543423-1350001522-5108@bernd'),(3,1,'Business Lunch','','','','2012-10-09 08:00:00',NULL,'2012-10-09 09:00:00',3,'FREQ=WEEKLY;INTERVAL=1;BYDAY=;WKST=TU',NULL,1,'1997318733-1350001551-5108@bernd','2012-10-12 00:25:51','1997318733-1350001551-5108@bernd'),(4,1,'Team phone conference','','','','2012-10-03 10:00:00','2012-10-03 11:00:00','2012-10-03 11:00:00',3,'',NULL,1,'1702531320-1350001594-5106@bernd','2012-10-12 00:32:12','1702531320-1350001594-5106@bernd');
/*!40000 ALTER TABLE `calendar2` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `calendar2_excluded_dates` WRITE;
/*!40000 ALTER TABLE `calendar2_excluded_dates` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar2_excluded_dates` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `calendar2_user_relation` WRITE;
/*!40000 ALTER TABLE `calendar2_user_relation` DISABLE KEYS */;
INSERT INTO `calendar2_user_relation` VALUES (1,3,2),(2,3,2),(3,3,2),(4,3,2);
/*!40000 ALTER TABLE `calendar2_user_relation` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `configuration` WRITE;
/*!40000 ALTER TABLE `configuration` DISABLE KEYS */;
/*!40000 ALTER TABLE `configuration` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
INSERT INTO `contact` VALUES (1,1,1,'Test','Test@test.de','','6516','65131216','324','65','6516','','','\n',0),(2,1,1,'ftghg','ghdfgh@dfhfdg.de','sdt436t5z','3q54rjzhmg','w4uj6rsnzfgfb','7i468okirumjdh','46hjwezrsnfg','','68okruejzthsd','sfdhkulr8o','\n37657i468o57p9lruktdzhnfh',0);
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `database_manager` WRITE;
/*!40000 ALTER TABLE `database_manager` DISABLE KEYS */;
INSERT INTO `database_manager` VALUES (1,'Project','title',1,'Title','text',1,1,NULL,NULL,NULL,1,'left',1,2,'1',0,1,0),(2,'Project','notes',1,'Notes','textarea',2,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(3,'Project','project_id',1,'Parent','selectValues',3,1,NULL,'Project#id#title',NULL,0,NULL,1,0,'1',1,1,0),(4,'Project','start_date',1,'Start date','date',4,1,NULL,NULL,NULL,3,'center',1,3,'1',0,0,0),(5,'Project','end_date',1,'End date','date',5,1,NULL,NULL,NULL,4,'center',1,4,'1',0,0,0),(6,'Project','priority',1,'Priority','rating',6,1,NULL,'10','5',5,'center',1,5,'1',1,0,0),(7,'Project','current_status',1,'Current status','selectValues',7,1,NULL,'1#Offered|2#Ordered|3#Working|4#Ended|5#Stopped|6#Re-Opened|7#Waiting','1',6,'center',1,6,'1',1,0,0),(8,'Project','complete_percent',1,'Complete percent','percentage',8,1,NULL,NULL,NULL,7,'center',1,7,'1',0,0,0),(9,'Project','budget',1,'Budget','text',9,1,NULL,NULL,NULL,0,NULL,1,8,'1',0,0,0),(10,'Project','hourly_wage_rate',1,'Hourly wage rate','text',10,1,NULL,NULL,NULL,0,NULL,1,0,'0',0,0,0),(11,'Project','contact_id',1,'Contact','selectValues',11,1,NULL,'Contact#id#name',NULL,0,NULL,1,9,'1',1,0,0),(72,'Contact','country',1,'Country','text',10,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(71,'Contact','zipcode',1,'Zip code','text',9,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(70,'Contact','city',1,'City','text',8,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(69,'Contact','street',1,'Street','text',7,1,NULL,NULL,NULL,4,'left',1,0,'1',0,0,0),(68,'Contact','mobilephone',1,'Mobile phone','text',6,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(67,'Contact','secondphone',1,'Second phone','text',5,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(66,'Contact','firstphone',1,'First phone','text',4,1,NULL,NULL,NULL,3,'left',1,0,'1',0,0,0),(65,'Contact','company',1,'Company','text',3,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(64,'Contact','email',1,'E-Mail','text',2,1,NULL,NULL,NULL,2,'left',1,0,'1',0,0,0),(63,'Contact','name',1,'Name','text',1,1,NULL,NULL,NULL,1,'left',1,0,'1',0,1,0),(80,'Filemanager','comments',1,'Comments','textarea',2,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(81,'Filemanager','project_id',1,'Project','selectValues',3,1,NULL,'Project#id#title',NULL,0,'0',1,0,'1',1,1,0),(82,'Filemanager','files',1,'Upload','upload',5,1,NULL,NULL,NULL,3,'center',1,0,'1',0,0,0),(28,'Helpdesk','title',1,'Title','text',1,1,NULL,NULL,NULL,1,'center',1,0,'1',0,1,0),(29,'Helpdesk','assigned',1,'Assigned','selectValues',3,1,NULL,'User#id#lastname',NULL,4,'center',1,0,'1',1,0,0),(30,'Helpdesk','date',1,'Date','display',4,1,NULL,NULL,NULL,2,'center',1,0,'1',0,1,0),(31,'Helpdesk','project_id',1,'Project','selectValues',6,1,NULL,'Project#id#title',NULL,0,NULL,1,0,'1',1,1,0),(32,'Helpdesk','priority',1,'Priority','rating',7,1,NULL,'10','5',5,'center',1,0,'1',1,0,0),(33,'Helpdesk','attachments',1,'Attachments','upload',8,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(34,'Helpdesk','description',1,'Description','textarea',11,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(35,'Helpdesk','status',1,'Status','selectValues',12,1,NULL,'1#Open|2#Assigned|3#Solved|4#Verified|5#Closed','1',6,'center',1,0,'1',1,1,0),(36,'Helpdesk','due_date',1,'Due date','date',5,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(37,'Helpdesk','author',1,'Author','display',2,1,NULL,'User#id#lastname',NULL,3,'center',1,0,'1',1,1,0),(38,'Helpdesk','solved_by',1,'Solved by','display',9,1,NULL,'User#id#lastname',NULL,0,NULL,1,0,'1',1,0,0),(39,'Helpdesk','solved_date',1,'Solved date','display',10,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(40,'Helpdesk','contact_id',1,'Contact','selectValues',13,1,NULL,'Contact#id#name',NULL,0,NULL,1,0,'1',1,0,0),(41,'Minutes','title',1,'Title','text',1,1,NULL,NULL,NULL,3,'center',1,0,'1',0,1,0),(42,'Minutes','meeting_datetime',1,'Start','datetime',2,1,NULL,NULL,NULL,1,'center',1,0,'1',0,1,0),(43,'Minutes','end_time',1,'End','time',3,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(44,'Minutes','project_id',1,'Project','selectValues',4,1,NULL,'Project#id#title',NULL,0,NULL,1,0,'1',1,1,0),(45,'Minutes','description',1,'Description','textarea',5,1,NULL,NULL,NULL,4,'center',1,0,'1',0,0,0),(46,'Minutes','place',1,'Place','text',6,1,NULL,NULL,NULL,5,'center',1,0,'1',0,0,0),(47,'Minutes','moderator',1,'Moderator','text',7,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(48,'Minutes','participants_invited',2,'Invited','multipleSelectValues',8,1,NULL,'User#id#username',NULL,0,NULL,1,0,'1',0,0,0),(49,'Minutes','participants_attending',2,'Attending','multipleSelectValues',9,1,NULL,'User#id#username',NULL,0,NULL,1,0,'1',0,0,0),(50,'Minutes','participants_excused',2,'Excused','multipleSelectValues',10,1,NULL,'User#id#username',NULL,0,NULL,1,0,'1',0,0,0),(51,'Minutes','item_status',1,'Status','selectValues',11,1,NULL,'1#Planned|2#Empty|3#Filled|4#Final','1',6,'center',1,0,'1',1,0,0),(52,'Note','project_id',1,'Project','selectValues',3,1,NULL,'Project#id#title',NULL,0,NULL,1,0,'1',1,1,0),(53,'Note','title',1,'Title','text',1,1,NULL,NULL,NULL,1,'left',1,2,'1',0,1,0),(54,'Note','comments',1,'Comments','textarea',2,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(55,'Todo','title',1,'Title','text',1,1,NULL,NULL,NULL,1,'left',1,2,'1',0,1,0),(56,'Todo','notes',1,'Notes','textarea',2,1,NULL,NULL,NULL,0,NULL,1,0,'1',0,0,0),(57,'Todo','start_date',1,'Start date','date',4,1,NULL,NULL,NULL,3,'center',1,3,'1',0,0,0),(58,'Todo','end_date',1,'End date','date',5,1,NULL,NULL,NULL,4,'center',1,4,'1',0,0,0),(59,'Todo','priority',1,'Priority','rating',6,1,NULL,'10','5',5,'center',1,5,'1',1,0,0),(60,'Todo','current_status',1,'Current status','selectValues',7,1,NULL,'1#Waiting|2#Accepted|3#Working|4#Stopped|5#Ended','1',7,'center',1,6,'1',1,0,0),(61,'Todo','project_id',1,'Project','selectValues',3,1,NULL,'Project#id#title',NULL,0,NULL,1,0,'1',1,1,0),(62,'Todo','user_id',1,'User','selectValues',8,1,NULL,'User#id#lastname',NULL,6,'left',1,7,'1',1,0,0),(73,'Contact','comment',1,'Comment','textarea',11,1,NULL,NULL,NULL,0,'',1,0,'1',0,0,0),(74,'Contact','private',1,'Private','selectValues',12,1,NULL,'0#No|1#Yes',NULL,5,'center',1,0,'1',1,0,0),(79,'Filemanager','title',1,'Title','text',1,1,NULL,NULL,NULL,1,'center',1,0,'1',0,1,0);
/*!40000 ALTER TABLE `database_manager` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filemanager`
--

LOCK TABLES `filemanager` WRITE;
/*!40000 ALTER TABLE `filemanager` DISABLE KEYS */;
INSERT INTO `filemanager` VALUES (2,3,'Login Layout','',9,'234e59f2e3c89b88257fde99628a1f81|login-mask.png');
/*!40000 ALTER TABLE `filemanager` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=262 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `frontend_message`
--

LOCK TABLES `frontend_message` WRITE;
/*!40000 ALTER TABLE `frontend_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `frontend_message` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `helpdesk` WRITE;
/*!40000 ALTER TABLE `helpdesk` DISABLE KEYS */;
/*!40000 ALTER TABLE `helpdesk` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=129 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history`
--

LOCK TABLES `history` WRITE;
/*!40000 ALTER TABLE `history` DISABLE KEYS */;
INSERT INTO `history` VALUES (1,3,1,1,'name','','Test','add','2012-05-15 10:44:54'),(2,3,1,1,'email','','Test@test.de','add','2012-05-15 10:44:54'),(3,3,1,1,'firstphone','','6516','add','2012-05-15 10:44:54'),(4,3,1,1,'secondphone','','65131216','add','2012-05-15 10:44:54'),(5,3,1,1,'mobilephone','','324','add','2012-05-15 10:44:54'),(6,3,1,1,'street','','65','add','2012-05-15 10:44:54'),(7,3,1,1,'city','','6516','add','2012-05-15 10:44:54'),(8,3,1,2,'name','','ftghg','add','2012-05-15 10:48:10'),(9,3,1,2,'email','','ghdfgh@dfhfdg.de','add','2012-05-15 10:48:10'),(10,3,1,2,'company','','sdt436t5z','add','2012-05-15 10:48:10'),(11,3,1,2,'firstphone','','3q54rjzhmg','add','2012-05-15 10:48:10'),(12,3,1,2,'secondphone','','w4uj6rsnzfgfb','add','2012-05-15 10:48:10'),(13,3,1,2,'mobilephone','','7i468okirumjdh','add','2012-05-15 10:48:10'),(14,3,1,2,'street','','46hjwezrsnfg','add','2012-05-15 10:48:10'),(15,3,1,2,'zipcode','','68okruejzthsd','add','2012-05-15 10:48:10'),(16,3,1,2,'country','','sfdhkulr8o','add','2012-05-15 10:48:10'),(17,3,1,2,'comment','','\n37657i468o57p9lruktdzhnfh','add','2012-05-15 10:48:10'),(36,1,1,2,'title','Project 1','Project of the Day','edit','2012-10-11 14:25:47'),(37,1,3,2,'title','Project of the Day','Web Design','edit','2012-10-11 21:48:46'),(38,1,3,2,'notes','Test','','edit','2012-10-11 21:48:46'),(39,1,3,3,'title','Sub Project','Layouts','edit','2012-10-11 21:49:16'),(40,1,3,3,'notes','Test','','edit','2012-10-11 21:49:16'),(41,1,3,6,'title','','Content','add','2012-10-11 21:49:32'),(42,1,3,6,'projectId','','2','add','2012-10-11 21:49:32'),(43,1,3,6,'startDate','','2012-10-12','add','2012-10-11 21:49:32'),(44,1,3,6,'endDate','','2012-10-12','add','2012-10-11 21:49:32'),(45,1,3,6,'priority','','5','add','2012-10-11 21:49:32'),(46,1,3,6,'currentStatus','','1','add','2012-10-11 21:49:32'),(47,1,3,7,'title','','Mobile','add','2012-10-11 21:49:41'),(48,1,3,7,'projectId','','3','add','2012-10-11 21:49:41'),(49,1,3,7,'startDate','','2012-10-12','add','2012-10-11 21:49:41'),(50,1,3,7,'endDate','','2012-10-12','add','2012-10-11 21:49:41'),(51,1,3,7,'priority','','5','add','2012-10-11 21:49:41'),(52,1,3,7,'currentStatus','','1','add','2012-10-11 21:49:41'),(53,1,3,8,'title','','Print','add','2012-10-11 21:50:00'),(54,1,3,8,'projectId','','3','add','2012-10-11 21:50:00'),(55,1,3,8,'startDate','','2012-10-12','add','2012-10-11 21:50:00'),(56,1,3,8,'endDate','','2012-10-12','add','2012-10-11 21:50:00'),(57,1,3,8,'priority','','5','add','2012-10-11 21:50:00'),(58,1,3,8,'currentStatus','','1','add','2012-10-11 21:50:00'),(59,1,3,9,'title','','Desktop','add','2012-10-11 21:50:08'),(60,1,3,9,'projectId','','3','add','2012-10-11 21:50:08'),(61,1,3,9,'startDate','','2012-10-12','add','2012-10-11 21:50:08'),(62,1,3,9,'endDate','','2012-10-12','add','2012-10-11 21:50:08'),(63,1,3,9,'priority','','5','add','2012-10-11 21:50:08'),(64,1,3,9,'currentStatus','','1','add','2012-10-11 21:50:08'),(65,1,3,10,'title','','Overview','add','2012-10-11 21:50:45'),(66,1,3,10,'projectId','','6','add','2012-10-11 21:50:45'),(67,1,3,10,'startDate','','2012-10-12','add','2012-10-11 21:50:45'),(68,1,3,10,'endDate','','2012-10-12','add','2012-10-11 21:50:45'),(69,1,3,10,'priority','','5','add','2012-10-11 21:50:45'),(70,1,3,10,'currentStatus','','1','add','2012-10-11 21:50:45'),(71,1,3,11,'title','','Benefits','add','2012-10-11 21:50:51'),(72,1,3,11,'projectId','','6','add','2012-10-11 21:50:51'),(73,1,3,11,'startDate','','2012-10-12','add','2012-10-11 21:50:51'),(74,1,3,11,'endDate','','2012-10-12','add','2012-10-11 21:50:51'),(75,1,3,11,'priority','','5','add','2012-10-11 21:50:51'),(76,1,3,11,'currentStatus','','1','add','2012-10-11 21:50:51'),(77,1,3,12,'title','','Examples','add','2012-10-11 21:51:13'),(78,1,3,12,'projectId','','6','add','2012-10-11 21:51:13'),(79,1,3,12,'startDate','','2012-10-12','add','2012-10-11 21:51:13'),(80,1,3,12,'endDate','','2012-10-12','add','2012-10-11 21:51:13'),(81,1,3,12,'priority','','5','add','2012-10-11 21:51:13'),(82,1,3,12,'currentStatus','','1','add','2012-10-11 21:51:13'),(83,1,3,4,'title','Serious Business','Legal','edit','2012-10-11 21:51:26'),(84,2,3,1,'summary','','Release','add','2012-10-11 22:24:47'),(85,2,3,1,'start','','2012-10-25 08:00:00','add','2012-10-11 22:24:47'),(86,2,3,1,'end','','2012-10-25 09:00:00','add','2012-10-11 22:24:47'),(87,2,3,1,'occurrence','','2012-10-25 08:00:00','add','2012-10-11 22:24:47'),(88,2,3,1,'confirmationStatus','','2','add','2012-10-11 22:24:47'),(89,2,3,1,'visibility','','1','add','2012-10-11 22:24:47'),(90,2,3,1,'participants','',NULL,'add','2012-10-11 22:24:47'),(91,2,3,1,'confirmationStatuses','',NULL,'add','2012-10-11 22:24:47'),(92,2,3,1,'ownerId','','3','add','2012-10-11 22:24:47'),(93,2,3,2,'summary','','Business Lunch','add','2012-10-11 22:25:22'),(94,2,3,2,'start','','2012-10-02 08:00:00','add','2012-10-11 22:25:22'),(95,2,3,2,'end','','2012-10-02 09:00:00','add','2012-10-11 22:25:22'),(96,2,3,2,'occurrence','','2012-10-02 08:00:00','add','2012-10-11 22:25:22'),(97,2,3,2,'confirmationStatus','','2','add','2012-10-11 22:25:22'),(98,2,3,2,'visibility','','1','add','2012-10-11 22:25:22'),(99,2,3,2,'participants','',NULL,'add','2012-10-11 22:25:22'),(100,2,3,2,'rrule','','FREQ=WEEKLY;UNTIL=20121012T060000Z;INTERVAL=1;BYDAY=;WKST=TU','add','2012-10-11 22:25:22'),(101,2,3,2,'confirmationStatuses','',NULL,'add','2012-10-11 22:25:22'),(102,2,3,2,'ownerId','','3','add','2012-10-11 22:25:22'),(103,2,3,2,'rrule','FREQ=WEEKLY;UNTIL=20121012T060000Z;INTERVAL=1;BYDAY=;WKST=TU','FREQ=WEEKLY;UNTIL=20121002T080000Z;INTERVAL=1;BYDAY=;WKST=TU','edit','2012-10-11 22:25:51'),(104,2,3,3,'summary','','Business Lunch','add','2012-10-11 22:25:51'),(105,2,3,3,'start','','2012-10-09 08:00:00','add','2012-10-11 22:25:51'),(106,2,3,3,'end','','2012-10-09 09:00:00','add','2012-10-11 22:25:51'),(107,2,3,3,'occurrence','','2012-10-09 08:00:00','add','2012-10-11 22:25:51'),(108,2,3,3,'confirmationStatus','','2','add','2012-10-11 22:25:51'),(109,2,3,3,'visibility','','1','add','2012-10-11 22:25:51'),(110,2,3,3,'participants','',NULL,'add','2012-10-11 22:25:51'),(111,2,3,3,'rrule','','FREQ=WEEKLY;INTERVAL=1;BYDAY=;WKST=TU','add','2012-10-11 22:25:51'),(112,2,3,3,'confirmationStatuses','',NULL,'add','2012-10-11 22:25:51'),(113,2,3,3,'ownerId','','3','add','2012-10-11 22:25:51'),(114,2,3,4,'summary','','Team phone conference','add','2012-10-11 22:26:34'),(115,2,3,4,'start','','2012-10-11 10:00:00','add','2012-10-11 22:26:34'),(116,2,3,4,'end','','2012-10-11 11:00:00','add','2012-10-11 22:26:34'),(117,2,3,4,'occurrence','','2012-10-11 10:00:00','add','2012-10-11 22:26:34'),(118,2,3,4,'confirmationStatus','','2','add','2012-10-11 22:26:34'),(119,2,3,4,'visibility','','1','add','2012-10-11 22:26:34'),(120,2,3,4,'participants','',NULL,'add','2012-10-11 22:26:34'),(121,2,3,4,'confirmationStatuses','',NULL,'add','2012-10-11 22:26:34'),(122,2,3,4,'ownerId','','3','add','2012-10-11 22:26:34'),(123,2,3,4,'start','2012-10-11 10:00:00','2012-10-03 10:00:00','edit','2012-10-11 22:32:12'),(124,2,3,4,'end','2012-10-11 11:00:00','2012-10-03 11:00:00','edit','2012-10-11 22:32:12'),(125,2,3,4,'occurrence','2012-10-11 10:00:00','2012-10-03 10:00:00','edit','2012-10-11 22:32:12'),(126,4,3,2,'title','','Login Layout','add','2012-10-11 22:57:32'),(127,4,3,2,'projectId','','9','add','2012-10-11 22:57:32'),(128,4,3,2,'files','','234e59f2e3c89b88257fde99628a1f81|login-mask.png','add','2012-10-11 22:57:32');
/*!40000 ALTER TABLE `history` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `item_rights` WRITE;
/*!40000 ALTER TABLE `item_rights` DISABLE KEYS */;
INSERT INTO `item_rights` VALUES (1,1,1,255),(1,1,2,255),(1,2,4,255),(1,6,4,255),(1,2,3,255),(1,3,4,255),(1,1,3,255),(1,1,4,255),(1,6,3,255),(1,3,3,255),(1,4,3,255),(1,4,4,255),(1,5,3,255),(1,5,4,255),(1,7,3,255),(1,7,4,255),(1,8,3,255),(1,8,4,255),(1,9,4,255),(1,9,3,255),(1,10,3,255),(1,10,4,255),(1,11,3,255),(1,11,4,255),(1,12,3,255),(1,12,4,255),(2,1,3,255),(2,2,3,255),(2,3,3,255),(2,4,3,255),(4,2,4,255),(4,2,3,255);
/*!40000 ALTER TABLE `item_rights` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `minutes` WRITE;
/*!40000 ALTER TABLE `minutes` DISABLE KEYS */;
/*!40000 ALTER TABLE `minutes` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `minutes_item` WRITE;
/*!40000 ALTER TABLE `minutes_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `minutes_item` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `module` WRITE;
/*!40000 ALTER TABLE `module` DISABLE KEYS */;
INSERT INTO `module` VALUES (1,'Project','Project',0,'6.2.1',1),(2,'Calendar2','Calendar',1,'6.1.0-beta1',1),(3,'Contact','Contact',1,'6.1.0',1),(4,'Filemanager','Filemanager',0,'6.2.0-dev',1),(5,'Gantt','Gantt',0,'6.0.0',1),(6,'Helpdesk','Helpdesk',0,'6.0.0',1),(7,'Minutes','Minute',0,'6.0.0',1),(8,'Note','Note',0,'6.0.0',1),(9,'Statistic','Statistic',0,'6.0.0',1),(10,'Timecard','Timecard',1,'6.1.4',1),(11,'Todo','Todo',0,'6.0.0',1);
/*!40000 ALTER TABLE `module` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `module_tab_relation` WRITE;
/*!40000 ALTER TABLE `module_tab_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_tab_relation` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `note` WRITE;
/*!40000 ALTER TABLE `note` DISABLE KEYS */;
/*!40000 ALTER TABLE `note` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project`
--

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;
INSERT INTO `project` VALUES (1,NULL,'/','PHProjekt','Test',1,'2009-05-12','2009-07-28',1,3,'0',NULL,NULL,NULL),(2,1,'/1/','Web Design','',1,'2009-05-02','2009-07-02',2,3,'0',NULL,'',0),(3,2,'/1/2/','Layouts','\n',1,'2009-05-02','2009-07-02',2,3,'0',NULL,'',0),(4,1,'/1/','Legal','\n',NULL,'2012-10-10','2012-10-16',5,1,'0',NULL,'',0),(5,4,'/1/4/','Lawsuit','',NULL,'2012-10-06','2012-10-06',5,1,'0',NULL,'',0),(6,2,'/1/2/','Content','\n',NULL,'2012-10-12','2012-10-12',5,1,'0',NULL,'',0),(7,3,'/1/2/3/','Mobile','\n',NULL,'2012-10-12','2012-10-12',5,1,'0',NULL,'',0),(8,3,'/1/2/3/','Print','\n',NULL,'2012-10-12','2012-10-12',5,1,'0',NULL,'',0),(9,3,'/1/2/3/','Desktop','',NULL,'2012-10-12','2012-10-12',5,1,'0',NULL,'',0),(10,6,'/1/2/6/','Overview','\n',NULL,'2012-10-12','2012-10-12',5,1,'0',NULL,'',0),(11,6,'/1/2/6/','Benefits','\n',NULL,'2012-10-12','2012-10-12',5,1,'0',NULL,'',0),(12,6,'/1/2/6/','Examples','\n',NULL,'2012-10-12','2012-10-12',5,1,'0',NULL,'',0);
/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=131 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_module_permissions`
--

LOCK TABLES `project_module_permissions` WRITE;
/*!40000 ALTER TABLE `project_module_permissions` DISABLE KEYS */;
INSERT INTO `project_module_permissions` VALUES (7,1,1),(82,11,2),(81,9,2),(80,8,2),(79,7,2),(78,6,2),(77,5,2),(76,4,2),(126,5,3),(75,1,2),(125,4,3),(124,4,4),(123,1,4),(61,1,5),(62,4,5),(85,1,6),(86,4,6),(87,5,6),(88,6,6),(89,7,6),(90,8,6),(91,9,6),(92,11,6),(93,1,7),(94,5,7),(95,1,8),(96,5,8),(129,5,9),(128,4,9),(99,1,10),(100,4,10),(101,5,10),(102,6,10),(103,7,10),(104,8,10),(105,9,10),(106,11,10),(107,1,11),(108,4,11),(109,5,11),(110,6,11),(111,7,11),(112,8,11),(113,9,11),(114,11,11),(115,1,12),(116,4,12),(117,5,12),(118,6,12),(119,7,12),(120,8,12),(121,9,12),(122,11,12),(127,1,3),(130,1,9);
/*!40000 ALTER TABLE `project_module_permissions` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_role_user_permissions`
--

LOCK TABLES `project_role_user_permissions` WRITE;
/*!40000 ALTER TABLE `project_role_user_permissions` DISABLE KEYS */;
INSERT INTO `project_role_user_permissions` VALUES (1,1,1,1),(2,1,2,1),(34,2,4,1),(33,2,1,1),(62,3,4,1),(61,3,1,1),(63,3,3,1),(35,2,3,1),(60,4,1,1),(27,5,1,1),(39,6,1,1),(40,6,4,1),(41,6,3,1),(42,7,1,1),(43,7,4,1),(44,7,3,1),(45,8,1,1),(46,8,4,1),(47,8,3,1),(66,9,3,1),(65,9,4,1),(64,9,1,1),(51,10,1,1),(52,10,4,1),(53,10,3,1),(54,11,1,1),(55,11,4,1),(56,11,3,1),(57,12,1,1),(58,12,4,1),(59,12,3,1);
/*!40000 ALTER TABLE `project_role_user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'Admin in All',NULL),(2,'can Read TODOs only',NULL),(3,'admin in PROJECTs',NULL),(4,'read only in All',NULL);
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `role_module_permissions` WRITE;
/*!40000 ALTER TABLE `role_module_permissions` DISABLE KEYS */;
INSERT INTO `role_module_permissions` VALUES (1,3,1,139),(2,2,1,0),(3,4,1,1),(4,2,4,0),(5,4,4,1),(6,3,4,0),(7,2,5,0),(8,4,5,1),(9,3,5,0),(10,2,6,0),(11,4,6,1),(12,3,6,0),(13,2,7,0),(14,4,7,1),(15,3,7,0),(16,2,8,0),(17,4,8,1),(18,3,8,0),(19,2,9,0),(20,4,9,1),(21,3,9,0),(22,3,11,0),(23,2,11,1),(24,4,11,1),(25,1,1,139),(26,1,2,139),(27,1,3,139),(28,1,4,139),(29,1,5,139),(30,1,6,139),(31,1,7,139),(32,1,8,139),(33,1,9,139),(34,1,10,139),(35,1,11,139);
/*!40000 ALTER TABLE `role_module_permissions` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `search_display` WRITE;
/*!40000 ALTER TABLE `search_display` DISABLE KEYS */;
INSERT INTO `search_display` VALUES (1,3,2,'Layouts','\n'),(1,2,1,'Web Design',''),(1,4,1,'Legal','\n'),(4,1,4,'omg',''),(1,5,4,'dzfgdfgfdg','\n'),(2,1,0,'Release',''),(1,6,2,'Content','\n'),(1,7,3,'Mobile','\n'),(1,8,3,'Print','\n'),(1,9,3,'Desktop',''),(1,10,6,'Overview','\n'),(1,11,6,'Benefits','\n'),(1,12,6,'Examples','\n'),(2,2,1,'Business Lunch',''),(2,3,1,'Business Lunch',''),(2,4,1,'Team phone conference',''),(4,2,9,'Login Layout','');
/*!40000 ALTER TABLE `search_display` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `search_word_module` WRITE;
/*!40000 ALTER TABLE `search_word_module` DISABLE KEYS */;
INSERT INTO `search_word_module` VALUES (1,2,35),(1,4,9),(2,1,17),(2,1,18),(2,2,38),(2,2,39),(2,4,50),(2,4,51),(3,1,46),(3,2,38),(3,2,39),(4,1,34),(4,2,43),(4,2,44),(4,2,45),(5,1,11),(6,1,21),(7,1,23),(8,1,25),(9,1,47),(10,1,29),(11,1,31),(12,1,33);
/*!40000 ALTER TABLE `search_word_module` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_words`
--

LOCK TABLES `search_words` WRITE;
/*!40000 ALTER TABLE `search_words` DISABLE KEYS */;
INSERT INTO `search_words` VALUES (23,'mobile',1),(21,'content',1),(46,'layouts',1),(35,'release',1),(9,'omg',1),(11,'dzfgdfgfdg',1),(34,'legal',1),(18,'design',1),(17,'web',1),(25,'print',1),(47,'desktop',1),(29,'overview',1),(31,'benefits',1),(33,'examples',1),(38,'business',2),(39,'lunch',2),(45,'conference',1),(44,'phone',1),(43,'team',1),(51,'layout',1),(50,'login',1);
/*!40000 ALTER TABLE `search_words` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting`
--

LOCK TABLES `setting` WRITE;
/*!40000 ALTER TABLE `setting` DISABLE KEYS */;
INSERT INTO `setting` VALUES (1,1,0,'password','2d89c2ebd8ef6d3b98b4afa339fb5b82','Core'),(2,1,0,'email','admin@admin.com','Core'),(3,1,0,'language','en','Core'),(4,1,0,'timeZone','000','Core'),(5,2,0,'password','156c3239dbfa5c5222b51514e9d12948','Core'),(6,2,0,'email','test@test.com','Core'),(7,2,0,'language','en','Core'),(8,2,0,'timeZone','000','Core'),(9,1,0,'tutorialDisplayed','true','Core'),(10,3,0,'password','7629ba5ca7702487e24d91650d332881','Core'),(11,3,0,'email','','Core'),(12,3,0,'language','en','Core'),(13,3,0,'timeZone','000','Core'),(14,4,0,'password','55518687349a1c910f39594b1b0a9b11','Core'),(15,4,0,'email','','Core'),(16,4,0,'language','de','Core'),(17,4,0,'timeZone','000','Core'),(18,3,0,'tutorialDisplayed','true','Core'),(19,4,0,'tutorialDisplayed','true','Core');
/*!40000 ALTER TABLE `setting` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `tab` WRITE;
/*!40000 ALTER TABLE `tab` DISABLE KEYS */;
INSERT INTO `tab` VALUES (1,'Basic Data'),(2,'People');
/*!40000 ALTER TABLE `tab` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (1,'layout'),(2,'login-mask'),(3,'desktop');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `tags_modules` WRITE;
/*!40000 ALTER TABLE `tags_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags_modules` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags_modules_items`
--

LOCK TABLES `tags_modules_items` WRITE;
/*!40000 ALTER TABLE `tags_modules_items` DISABLE KEYS */;
INSERT INTO `tags_modules_items` VALUES (1,1,4,2),(2,2,4,2),(3,3,4,2);
/*!40000 ALTER TABLE `tags_modules_items` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `tags_users` WRITE;
/*!40000 ALTER TABLE `tags_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags_users` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timecard`
--

LOCK TABLES `timecard` WRITE;
/*!40000 ALTER TABLE `timecard` DISABLE KEYS */;
INSERT INTO `timecard` VALUES (5,3,'2012-10-12 12:00:00','15:00:00',180,8,'',NULL,NULL,'112099682-1350004491-5106@bernd','112099682-1350004491-5106@bernd'),(4,5,'2012-10-08 18:00:00','19:00:00',60,4,'',NULL,NULL,'1722207393-1349709318-4294@bernd','1722207393-1349709318-4294@bernd');
/*!40000 ALTER TABLE `timecard` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `todo` WRITE;
/*!40000 ALTER TABLE `todo` DISABLE KEYS */;
/*!40000 ALTER TABLE `todo` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uploaded_unused_files`
--

LOCK TABLES `uploaded_unused_files` WRITE;
/*!40000 ALTER TABLE `uploaded_unused_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `uploaded_unused_files` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'admin','Admin','Admin','A',1),(2,'test','Test','Test','I',0),(3,'english','English','Standard','A',0),(4,'deutsch','Deutscher','Benutzer','A',0);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

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

LOCK TABLES `user_proxy` WRITE;
/*!40000 ALTER TABLE `user_proxy` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_proxy` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-10-12  3:18:16
