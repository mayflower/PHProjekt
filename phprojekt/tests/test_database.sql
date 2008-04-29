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
  `id` int NOT NULL AUTO_INCREMENT,
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
  `listUseFilter` int(4) default NULL,
  `altPosition` int(11) default NULL,
  `status` varchar(20) default NULL,
  `isInteger` int(4) default NULL,
  `isRequired` int(4) default NULL,
  `isUnique` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;


--
-- Table structure for table `Groups`
--
DROP TABLE IF EXISTS `Groups`;
CREATE TABLE `Groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;


--
-- Table structure for table `GroupsUserRelation`
--
DROP TABLE IF EXISTS `GroupsUserRelation`;
CREATE TABLE `GroupsUserRelation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupsId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;


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
  `newValue` varchar(255) default NULL,
  `action` varchar(50) NOT NULL,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB;
CREATE INDEX `History_userId` ON `History`(`userId`);


--
-- Table structure for table `ModuleInstance`
--
DROP TABLE IF EXISTS `ModuleInstance`;
CREATE TABLE `ModuleInstance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) default NULL,
  `module` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
CREATE INDEX `ModuleInstance_userId` ON `ModuleInstance`(`projectId`);


--
-- Table structure for table `Project`
--
DROP TABLE IF EXISTS `Project`;
CREATE TABLE `Project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) default NULL,
  `path` varchar(25) NOT NULL default '\\',
  `title` varchar(255) NOT NULL,
  `notes` text default NULL,
  `ownerId` int(11) default NULL,
  `startDate` date default NULL,
  `endDate` date default NULL,
  `priority` int(11) default NULL,
  `currentStatus` varchar(50) NOT NULL default 'working',
  `completePercent` float default '0',
  `hourlyWageRate` float default NULL,
  `budget` float default NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB;
CREATE INDEX `Project_ownerId` ON `Project`(`ownerId`);


--
-- Table structure for table `ProjectUserRoleRelation`
--
DROP TABLE IF EXISTS `ProjectUserRoleRelation`;
CREATE TABLE `ProjectUserRoleRelation` (
  `projectId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL
) ENGINE=InnoDB;
CREATE INDEX `ProjectUserRoleRelation_projectId` ON `ProjectUserRoleRelation`(`projectId`);
CREATE INDEX `ProjectUserRoleRelation_userId` ON `ProjectUserRoleRelation`(`userId`);
CREATE INDEX `ProjectUserRoleRelation_roleId` ON `ProjectUserRoleRelation`(`roleId`);


--
-- Table structure for table `Role`
--
DROP TABLE IF EXISTS `Role`;
CREATE TABLE `Role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent` int(11) default NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB;


--
-- Table structure for table `RoleModulePermissions`
--
DROP TABLE IF EXISTS `RoleModulePermissions`;
CREATE TABLE `RoleModulePermissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `roleId` int(8) NOT NULL,
  `module` varchar(255) NOT NULL,
  `permission` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;


--
-- Table structure for table `Todo`
--
DROP TABLE IF EXISTS `Todo`;
CREATE TABLE `Todo` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `notes` text,
  `ownerId` int(11) default NULL,
  `projectId` int(11) default NULL,
  `startDate` date default NULL,
  `endDate` date default NULL,
  `priority` int(11) default NULL,
  `currentStatus` varchar(50) NOT NULL default 'working',
  PRIMARY KEY  (`id`)
 ) ENGINE=InnoDB;


--
-- Table structure for table `User`
--
DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `firstname` varchar(255) default NULL,
  `lastname` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `language` varchar(5) NOT NULL,
  `status` varchar(1) default 'A',
  PRIMARY KEY  (`id`),
  UNIQUE(`username`)
) ENGINE=InnoDB;


--
-- Table structure for table `UserModuleSetting`
--
DROP TABLE IF EXISTS `UserModuleSetting`;
CREATE TABLE `UserModuleSetting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `keyValue` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `module` varchar(50) NOT NULL,
  `identifier`  varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
CREATE INDEX `UserModuleSetting_userId` ON `UserModuleSetting`(`userId`);


--
-- Table structure for table `SearchWords`
--
DROP TABLE IF EXISTS `SearchWords`;
CREATE TABLE `SearchWords` (
  `module` varchar(255) NOT NULL,
  `itemId` int(11) NOT NULL,
  `word` varchar(255) NOT NULL,
  `crc32` bigint NOT NULL,
  PRIMARY KEY(`itemId`,`module`,`crc32`)
) ENGINE=InnoDB;


--
-- Table structure for table `Note`
--
DROP TABLE IF EXISTS `Note`;
CREATE TABLE `Note` (
  `id` int(11) NOT NULL auto_increment,
  `projectId` int(11) default NULL,
  `title` varchar(255) NOT NULL,
  `comments` text,
  `category` varchar(50) default NULL,
  `ownerId` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `Configuration`;
CREATE TABLE `Configuration` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `module` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `Tree`
--
DROP TABLE IF EXISTS `Tree`;

--
-- Table structure for table `Tags`
--
DROP TABLE IF EXISTS `Tags`;
CREATE TABLE `Tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `crc32` bigint NOT NULL,
  PRIMARY KEY  (`id`)
);

INSERT INTO Tags (`id`, `word`, `crc32`) VALUES
(1,'this',-17923545),
(2,'todo',1510913696);


--
-- Table structure for table `TagsUsers`
--
DROP TABLE IF EXISTS `TagsUsers`;
CREATE TABLE `TagsUsers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `tagId` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
);

INSERT INTO TagsUsers (`id`, `userId`, `tagId`) VALUES
(1, 1, 1),
(2, 1, 2);

--
-- Table structure for table `TagsModules`
--
DROP TABLE IF EXISTS `TagsModules`;
CREATE TABLE `TagsModules` (
  `module` varchar(255) NOT NULL,
  `itemId` int(11) NOT NULL,
  `tagUserId` int(11) NOT NULL,
  PRIMARY KEY  (`module`, `itemId`, `tagUserId`)
);


DROP TABLE IF EXISTS `ItemRights`;
CREATE TABLE `ItemRights` (
  `module` varchar(255) NOT NULL,
  `itemId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `adminAccess` int(1) NOT NULL,
  `writeAccess` int(1) NOT NULL,
  `readAccess` int(1) NOT NULL,
  PRIMARY KEY  (`module`,`itemId`,`userId`)
);

DROP TABLE IF EXISTS `timecard`;
CREATE TABLE `timecard` (
  `id` int(11) NOT NULL auto_increment,
  `notes` text,
  `ownerId` int(11) default NULL,
  `projectId` int(11) default NULL,
  `date` date default NULL,
  `startTime` time default NULL,
  `endTime` time default NULL,
  PRIMARY KEY  (`id`)
);



--
-- INSERT DATA
--

LOCK TABLES `DatabaseManager` WRITE;
/*!40000 ALTER TABLE `DatabaseManager` DISABLE KEYS */;
INSERT INTO DatabaseManager (`id`, `tableName`, `tableField`, `formTab`, `formLabel`, `formTooltip`, `formType`, `formPosition`, `formColumns`, `formRegexp`, `formRange`, `defaultValue`, `listPosition`, `listAlign`, `listUseFilter`, `altPosition`, `status`, `isInteger`, `isRequired`, `isUnique`) VALUES
(1, 'Project', 'projectId', 1, 'projectId', 'projectId', 'tree', 1, 1, NULL, 'Project', '1', 2, 'left', 1, 1, '1', 1, 0, 0),
(2, 'Project', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(3, 'Project', 'notes', 1, 'notes', 'notes', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(4, 'Project', 'startDate', 1, 'startDate', 'startDate', 'date', 4, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 1, 0),
(5, 'Project', 'endDate', 1, 'endDate', 'endDate', 'date', 5, 1, NULL, NULL, '', 4, 'center', 1, 4, '1', 0, 1, 0),
(6, 'Project', 'priority', 1, 'priority', 'priority', 'selectValues', 6, 1, NULL, '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10', '5', 5, 'center', 1, 5, '1', 1, 1, 0),
(7, 'Project', 'currentStatus', 1, 'currentStatus', 'currentStatus', 'selectValues', 7, 1, NULL, '1#Offered|2#Ordered|3#Working|4#Ended|5#Stopped|6#Re-Opened|7#Waiting', '1', 6, 'center', 1, 6, '1', 0, 0, 0),
(8, 'Project', 'completePercent', 1, 'completePercent', 'completePercent', 'text', 8, 1, NULL, NULL, '', 7, 'center', 1, 7, '1', 0, 0, 0),
(9, 'Project', 'budget', 1, 'budget', 'budget', 'text', 9, 1, NULL, NULL, '', 0, NULL, 1, 8, '1', 0, 0, 0),

(10, 'Todo', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(11, 'Todo', 'notes', 1, 'notes', 'notes', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(12, 'Todo', 'startDate', 1, 'startDate', 'startDate', 'date', 4, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 1, 0),
(13, 'Todo', 'endDate', 1, 'endDate', 'endDate', 'date', 5, 1, NULL, NULL, '', 4, 'center', 1, 4, '1', 0, 1, 0),
(14, 'Todo', 'priority', 1, 'priority', 'priority', 'selectValues', 6, 1, NULL, '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10', '5', 5, 'center', 1, 5, '1', 1, 1, 0),
(15, 'Todo', 'currentStatus', 1, 'currentStatus', 'currentStatus', 'selectValues', 7, 1, NULL, '1#Accepted|2#Working|4#Ended|5#Stopped|7#Waiting', '1', 6, 'center', 1, 6, '1', 0, 0, 0),
(16, 'Todo', 'projectId', 1, 'project', 'project', 'tree', 1, 1, NULL, 'Project', '', 2, 'center', 1, 1, '1', 1, 0, 0),

(17, 'History', 'userId', 1, 'UserId', 'UserId', 'userId', '1', '1', NULL, NULL, 0, 1, 'left', 1, 1, 1, 1, 1, 0),
(18, 'History', 'dataobjectId', 1, 'DataobjectId', 'DataobjectId', 'text', '2', '1', NULL, NULL, 0, 2, 'center', 1, 2, 1, 1, 1, 0),
(19, 'History', 'module', 1, 'Module', 'Module', 'text', '3', '1', NULL, NULL, '', 3, 'left', 1, 3, 1, 0, 1, 0),
(20, 'History', 'field', 1, 'Field', 'Field', 'text', '4', '1', NULL, NULL, '', 4, 'left', 1, 4, 1, 0, 1, 0),
(21, 'History', 'oldValue', 1, 'OldValue', 'OldValue', 'text', '5', '1', NULL, NULL, '', 0, '', 0, 0, 1, 0, 1, 0),
(22, 'History', 'newValue', 1, 'NewValue', 'NewValue', 'text', '6', '1', NULL, NULL, '', 0, '', 0, 0, 1, 0, 1, 0),
(23, 'History', 'action', 1, 'Action', 'Action', 'text', '7', '1', NULL, NULL, '', 7, 'left', 1, 7, 1, 0, 1, 0),
(24, 'History', 'datetime', 1, 'Datetime', 'Datetime', 'datetime', '8', '1', NULL, NULL, '', 8, 'center', 1, 8, 1, 0, 1, 0),

(25, 'Note', 'projectId', 1, 'project', 'project', 'tree', 1, 1, NULL, 'Project', '', 2, 'left', 1, 1, '1', 0, 1, 0),
(26, 'Note', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(27, 'Note', 'comments', 1, 'comments', 'comments', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 1, 0),
(28, 'Note', 'category', 1, 'category', 'category', 'selectSqlAddOne', 4, 2, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0),
(29, 'Timecard', 'notes'    , 1, 'notes'    , 'notes'    , 'text'    , 1, 2, NULL, NULL     , '', 1, NULL    , 1, 0, '1', 0, 1, 0),
(30, 'Timecard', 'date'     , 1, 'date'     , 'date'     , 'date'    , 2, 1, NULL, NULL     , '', 2, 'center', 1, 1, '1', 0, 1, 0),
(31, 'Timecard', 'startTime', 1, 'startTime', 'startTime', 'time'    , 3, 1, NULL, NULL     , '', 3, 'center', 1, 0, '1', 0, 1, 0),
(32, 'Timecard', 'endTime'  , 1, 'endTime'  , 'endTime'  , 'time'    , 4, 1, NULL, NULL     , '', 4, 'center', 1, 0, '1', 0, 1, 0),
(33, 'Timecard', 'projectId', 1, 'project'  , 'project'  , 'tree'    , 0, 0, NULL, 'Project', '', 0, 'center', 1, 0, '1', 1, 0, 0);
/*!40000 ALTER TABLE `DatabaseManager` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `User` WRITE;
/*!40000 ALTER TABLE `User` DISABLE KEYS */;
INSERT INTO `User` (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `language`, `status`) VALUES
(1,'david','156c3239dbfa5c5222b51514e9d12948',NULL,NULL,'test@example.com','de_DE','A'),
(3,'inactive','156c3239dbfa5c5222b51514e9d12948',NULL,NULL, '', 'de_DE','I');
/*!40000 ALTER TABLE `User` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `Project` WRITE;
/*!40000 ALTER TABLE `Project` DISABLE KEYS */;
INSERT INTO `Project` (`id`, `projectId`, `path`, `title`, `notes`, `ownerId`, `startDate`, `endDate`, `priority`, `currentStatus`, `completePercent`, `hourlyWageRate`, `budget`) VALUES
(1,NULL,'/','Invisible Root','',1,NULL,NULL,NULL,'working',0,NULL,NULL),
(2,1,'/1/','Project 1','',1,NULL,NULL,NULL,'working',0,NULL,NULL),
(3,1,'/1/','Project 2','',2,NULL,NULL,NULL,'working',0,NULL,NULL),
(4,2,'/1/2/','Sub Project','',1,NULL,NULL,NULL,'working',0,NULL,NULL),
(5,2,'/1/2/','Test Project','Test note',1,NULL,NULL,NULL,'ordered',0,NULL,NULL),
(6,4,'/1/2/4/','Sub Sub Project 1','',1,NULL,NULL,NULL,'working',0,NULL,NULL),
(7,4,'/1/2/4/','Sub Sub Project 2','',1,NULL,NULL,NULL,'working',0,NULL,NULL);
/*!40000 ALTER TABLE `Project` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `Groups` WRITE;
/*!40000 ALTER TABLE `Groups` DISABLE KEYS */;
INSERT INTO `Groups` (`id`, `name`) VALUES
(1, 'default'),
(2, 'ninatest'),
(3, 'ninasgruppe'),
(4, 'testgruppe');
/*!40000 ALTER TABLE `Groups` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `GroupsUserRelation` WRITE;
/*!40000 ALTER TABLE `GroupsUserRelation` DISABLE KEYS */;
INSERT INTO `GroupsUserRelation` (`id`, `groupsId`, `userId`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 1);
/*!40000 ALTER TABLE `GroupsUserRelation` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `Role` WRITE;
/*!40000 ALTER TABLE `Role` DISABLE KEYS */;
INSERT INTO `Role` (`id`, `name`, `parent`) VALUES
(1, 'admin', 0);
/*!40000 ALTER TABLE `Role` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `ProjectUserRoleRelation` WRITE;
/*!40000 ALTER TABLE `ProjectUserRoleRelation` DISABLE KEYS */;
INSERT INTO `ProjectUserRoleRelation` (`projectId`, `userId`, `roleId`) VALUES
(1, 1, 1);
/*!40000 ALTER TABLE `ProjectUserRoleRelation` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `RoleModulePermissions` WRITE;
/*!40000 ALTER TABLE `RoleModulePermissions` DISABLE KEYS */;
INSERT INTO `RoleModulePermissions` (`id`, `roleId`, `module`, `permission`) VALUES
(1, 1, 'Project', 'write'),
(2, 1, 'Todo', 'write'),
(3, 1, 'Note', 'write');
/*!40000 ALTER TABLE `RoleModulePermissions` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `Todo` WRITE;
/*!40000 ALTER TABLE `Todo` DISABLE KEYS */;
INSERT INTO `Todo` (`id`, `title`, `notes`, `ownerId`, `projectId`, `startDate`, `endDate`, `priority`, `currentStatus`) VALUES
(1,'Todo of Test Project','',1,1,'2007-12-12','2007-12-31',0,'working');
/*!40000 ALTER TABLE `Todo` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `ModuleInstance` WRITE;
/*!40000 ALTER TABLE `ModuleInstance` DISABLE KEYS */;
INSERT INTO `ModuleInstance` VALUES
(1,5,'Task','Developer Tasks'),
(2,5,'Tasks','Project Tasks');
/*!40000 ALTER TABLE `ModuleInstance` ENABLE KEYS */;
UNLOCK TABLES;

INSERT INTO TagsModules (`module`, `itemId`, `tagUserId`) VALUES
('Default', 1, 1);

INSERT INTO `ItemRights` (`module`, `itemId`, `userId`, `adminAccess`, `writeAccess`, `readAccess`) VALUES
('Project', 1, 1, 1, 1, 1),
('Project', 2, 1, 1, 1, 1),
('Project', 4, 1, 0, 0, 1),
('Project', 4, 3, 0, 0, 1),
('Project', 5, 1, 0, 0, 1),
('Project', 5, 3, 0, 0, 1),
('Project', 6, 1, 1, 1, 1),
('Project', 7, 1, 1, 1, 1),
('Project', 8, 1, 1, 1, 1),
('Project', 9, 1, 1, 1, 1),
('Project', 10, 1, 1, 1, 1),
('Timecard', 1, 1, 1, 1, 1),
('Timecard', 2, 1, 1, 1, 1),
('Timecard', 3, 1, 1, 1, 1),
('Timecard', 4, 1, 1, 1, 1),
('Timecard', 5, 1, 1, 1, 1),
('Timecard', 6, 1, 1, 1, 1);


INSERT INTO `timecard` (`id`, `notes`, `ownerId`, `projectId`, `date`, `startTime`, `endTime`) VALUES
(1, 'Timecard row', 1, 1, '2008-04-29', '08:00:00', '13:00:00'),
(2, 'Timecard row 2', 1, 1, '2008-04-29', '14:00:00', '18:00:00'),
(3, 'Timecard row 3', 1, 1, '2008-04-30', '08:00:00', '13:00:00'),
(4, 'Timecard row 4', 1, 1, '2008-04-30', '14:00:00', '18:00:00'),
(5, 'Timecard row 6', 1, 1, '2008-05-02', '08:00:00', '13:00:00'),
(6, 'Timecard row 7', 1, 1, '2008-05-02', '14:00:00', '18:00:00');

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
