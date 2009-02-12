-- MySQL dump 10.11
--
-- Host: localhost    Database: phprojekt-mvc
-- ------------------------------------------------------
-- Server version    5.0.38-Ubuntu_0ubuntu1-log

BEGIN;

-- Drop table if exists
DROP TABLE IF EXISTS `Timecard`;
DROP TABLE IF EXISTS `Timeproj`;
DROP TABLE IF EXISTS `ItemRights`;
DROP TABLE IF EXISTS `Configuration`;
DROP TABLE IF EXISTS `Note`;
DROP TABLE IF EXISTS `TagsModules`;
DROP TABLE IF EXISTS `TagsUsers`;
DROP TABLE IF EXISTS `Tags`;
DROP TABLE IF EXISTS `TabModuleRelation`;
DROP TABLE IF EXISTS `ModuleTabRelation`;
DROP TABLE IF EXISTS `Tab`;
DROP TABLE IF EXISTS `SearchWords`;
DROP TABLE IF EXISTS `SearchWordModule`;
DROP TABLE IF EXISTS `SearchDisplay`;
DROP TABLE IF EXISTS `Todo`;
DROP TABLE IF EXISTS `RoleModulePermissions`;
DROP TABLE IF EXISTS `ProjectUserRoleRelation`;
DROP TABLE IF EXISTS `ProjectRoleUserPermissions`;
DROP TABLE IF EXISTS `ModuleProjectRelation`;
DROP TABLE IF EXISTS `ProjectModulePermissions`;
DROP TABLE IF EXISTS `Project`;
DROP TABLE IF EXISTS `History`;
DROP TABLE IF EXISTS `GroupsUserRelation`;
DROP TABLE IF EXISTS `Role`;
DROP TABLE IF EXISTS `Groups`;
DROP TABLE IF EXISTS `UserSetting`;
DROP TABLE IF EXISTS `Setting`;
DROP TABLE IF EXISTS `Module`;
DROP TABLE IF EXISTS `User`;
DROP TABLE IF EXISTS `DatabaseManager`;
DROP TABLE IF EXISTS `Calendar`;
DROP TABLE IF EXISTS `Filemanager`;
DROP TABLE IF EXISTS `Contact`;

--
-- Table structure for table `DatabaseManager`
--
CREATE TABLE `DatabaseManager` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tableName` varchar(50) default NULL,
  `tableField` varchar(60) default NULL,
  `formTab` int(11) default NULL,
  `formLabel` varchar(255) default NULL,
  `formType` varchar(50) default NULL,
  `formPosition` int(11) default NULL,
  `formColumns` int(11) default NULL,
  `formRegexp` varchar(255) default NULL,
  `formRange` text default NULL,
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
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `User`
--
CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `firstname` varchar(255) default NULL,
  `lastname` varchar(255) default NULL,
  `status` varchar(1) default 'A',
  `admin` int(1) NOT NULL default 0,
  PRIMARY KEY(`id`),
  UNIQUE(`username`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Module`
--
-- saveType can be 0 for projects, 1 for global, 2 for both
--
CREATE TABLE `Module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `saveType` int(1) NOT NULL default 0,
  `active` int(1) NOT NULL default 1,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Groups`
--
CREATE TABLE `Groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `GroupsUserRelation`
--
CREATE TABLE `GroupsUserRelation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupsId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `History`
--
CREATE TABLE `History` (
  `id` int(11) NOT NULL auto_increment,
  `moduleId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `field` varchar(255) NOT NULL,
  `oldValue` text default NULL,
  `newValue` text default NULL,
  `action` varchar(50) NOT NULL,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Project`
--
CREATE TABLE `Project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) default NULL,
  `path` varchar(25) NOT NULL default '/',
  `title` varchar(255) NOT NULL,
  `notes` text default NULL,
  `ownerId` int(11) default NULL,
  `startDate` date default NULL,
  `endDate` date default NULL,
  `priority` int(11) default NULL,
  `currentStatus` varchar(50) NOT NULL default 'working',
  `completePercent` varchar(4) default NULL,
  `hourlyWageRate` varchar(10) default NULL,
  `budget` varchar(10) default NULL,
  `contactId` int(11) default NULL,
  PRIMARY KEY (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `ProjectModulePermissions `
--
CREATE TABLE `ProjectModulePermissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `moduleId` int(11) NOT NULL,
    `projectId` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Role`
--
CREATE TABLE `Role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent` int(11) default NULL,
  PRIMARY KEY(`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `ProjectRoleUserPermissions `
--
CREATE TABLE `ProjectRoleUserPermissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `RoleModulePermissions`
--
CREATE TABLE `RoleModulePermissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleId` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `access` int(3) NOT NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Todo`
--
CREATE TABLE `Todo` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `notes` text default NULL,
  `ownerId` int(11) default NULL,
  `projectId` int(11) NOT NULL,
  `startDate` date default NULL,
  `endDate` date default NULL,
  `priority` int(11) default NULL,
  `currentStatus` varchar(50) NOT NULL default 'working',
  `userId` int(11) default NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Setting`
--
CREATE TABLE `Setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `keyValue` varchar(255) NOT NULL,
  `value` text default NULL,
  `identifier`  varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `SearchWords`
--
CREATE TABLE `SearchWords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `SearchWordModule`
--
CREATE TABLE `SearchWordModule` (
  `moduleId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `wordId` int(11) NOT NULL,
  PRIMARY KEY  (`itemId`,`moduleId`,`wordId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `SearchDisplay`
--
CREATE TABLE `SearchDisplay` (
  `moduleId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `firstDisplay` text,
  `secondDisplay` text,
  `projectId` int(11) NOT NULL,
  PRIMARY KEY  (`itemId`,`moduleId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Tags`
--
CREATE TABLE `Tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `crc32` bigint NOT NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `TagsUsers`
--
CREATE TABLE `TagsUsers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `tagId` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `TagsModules`
--
CREATE TABLE `TagsModules` (
  `moduleId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `tagUserId` int(11) NOT NULL,
  PRIMARY KEY  (`moduleId`, `itemId`, `tagUserId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Tab`
--
CREATE TABLE `Tab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `ModuleTabRelation`
--
CREATE TABLE `ModuleTabRelation` (
  `tabId` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  PRIMARY KEY (`tabId`, `moduleId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Note`
--
CREATE TABLE `Note` (
  `id` int(11) NOT NULL auto_increment,
  `projectId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `comments` text default NULL,
  `category` varchar(50) default NULL,
  `ownerId` int(11) default NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Configuration`
--
CREATE TABLE `Configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduleId` int(11) NOT NULL,
  `keyValue` varchar(255) NOT NULL,
  `value` text default NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `ItemRights`
--
CREATE TABLE `ItemRights` (
  `moduleId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `access` int(3) NOT NULL,
  PRIMARY KEY  (`moduleId`,`itemId`,`userId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Timecard`
--
CREATE TABLE `Timecard` (
  `id` int(11) NOT NULL auto_increment,
  `ownerId` int(11) default NULL,
  `date` date default NULL,
  `startTime` time default NULL,
  `endTime` time default NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Timeproj`
--
CREATE TABLE `Timeproj` (
  `id` int(11) NOT NULL auto_increment,
  `notes` text default NULL,
  `ownerId` int(11) default NULL,
  `projectId` int(11),
  `date` date default NULL,
  `amount` time default NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Calendar`
--
CREATE TABLE `Calendar` (
  `id` int(11) NOT NULL auto_increment,
  `parentId` int(11) default NULL,
  `ownerId` int(11) default NULL,
  `projectId` int(11) NOT NULL,
  `title` varchar(255) default NULL,
  `notes` text default NULL,
  `uid` varchar(255) NOT NULL,
  `startDate` date default NULL,
  `startTime` time default NULL,
  `endDate` date default NULL,
  `endTime` time default NULL,
  `created` int(11) default NULL,
  `modified` int(10) unsigned default NULL,
  `timezone` varchar(50)  default NULL,
  `location` varchar(255) default NULL,
  `categories` text default NULL,
  `attendee` text default NULL,
  `status` int(1) default NULL,
  `priority` int(1) default NULL,
  `class` int(1) default NULL,
  `transparent` int(1)  default NULL,
  `rrule` text default NULL,
  `properties` text default NULL,
  `deleted` int(1) default NULL,
  `participantId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `ownerid` (`ownerId`,`projectId`),
  KEY `startDate` (`startDate`,`startTime`),
  KEY `endDate` (`endDate`,`endTime`),
  KEY `parentId` (`parentId`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Filemanager`
--
CREATE TABLE `Filemanager` (
  `id` int(11) NOT NULL auto_increment,
  `ownerId` int(11) default NULL,
  `title` varchar(50) NOT NULL,
  `comments` text default NULL,
  `projectId` int(11) NOT NULL,
  `category` varchar(50) default NULL,
  `files` text NOT NULL,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


--
-- Table structure for table `Contact`
--
CREATE TABLE `Contact` (
  `id` int(11) NOT NULL auto_increment,
  `ownerId` int(11) default NULL,
  `projectId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `firstphone` varchar(255) NOT NULL,
  `secondphone` varchar(255) NOT NULL,
  `mobilephone` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `zipcode` varchar(50) NOT NULL,
  `country` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `private` int(1) default 0,
  PRIMARY KEY  (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;

--
-- INSERT DATA
--

INSERT INTO `Module` (`id`, `name`, `label`, `saveType`, `active`) VALUES
(1, 'Project', 'Project', 0, 1),
(2, 'Todo', 'Todo', 0, 1),
(3, 'Note', 'Note', 0, 1),
(4, 'Timecard', 'Timecard', 1, 1),
(5, 'Calendar', 'Calendar', 1, 1),
(6, 'Gantt', 'Gantt', 0, 1),
(7, 'Filemanager', 'Filemanager', 0, 1),
(8, 'Statistic', 'Statistic', 0, 1),
(9, 'Contact', 'Contact', 1, 1);

INSERT INTO `DatabaseManager` (`id`, `tableName`, `tableField`, `formTab`, `formLabel`, `formType`, `formPosition`, `formColumns`, `formRegexp`, `formRange`, `defaultValue`, `listPosition`, `listAlign`, `listUseFilter`, `altPosition`, `status`, `isInteger`, `isRequired`, `isUnique`) VALUES
(0, 'Project', 'title', 1, 'title', 'text', 1, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Project', 'notes', 1, 'notes', 'textarea', 2, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(0, 'Project', 'projectId', 1, 'parent', 'selectValues', 3, 1, NULL, 'Project#id#title', '1', 0, NULL, 1, 1, '1', 1, 0, 0),
(0, 'Project', 'startDate', 1, 'startDate', 'date', 4, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0),
(0, 'Project', 'endDate', 1, 'endDate', 'date', 5, 1, NULL, NULL, '', 4, 'center', 1, 4, '1', 0, 0, 0),
(0, 'Project', 'priority', 1, 'priority', 'selectValues', 6, 1, NULL, '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10', '5', 5, 'center', 1, 5, '1', 1, 0, 0),
(0, 'Project', 'currentStatus', 1, 'currentStatus', 'selectValues', 7, 1, NULL, '1#Offered|2#Ordered|3#Working|4#Ended|5#Stopped|6#Re-Opened|7#Waiting', '1', 6, 'center', 1, 6, '1', 0, 0, 0),
(0, 'Project', 'completePercent', 1, 'completePercent', 'percentage', 8, 1, NULL, NULL, '', 7, 'center', 1, 7, '1', 0, 0, 0),
(0, 'Project', 'budget', 1, 'budget', 'text', 9, 1, NULL, NULL, '', 0, NULL, 1, 8, '1', 0, 0, 0),
(0, 'Project', 'contactId', 1, 'Contact', 'selectValues', 10, 1, NULL, 'Contact#id#name', NULL, 0, NULL, 1, 1, '1', 1, 0, 0),

(0, 'Todo', 'title', 1, 'title', 'text', 1, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Todo', 'notes', 1, 'notes', 'textarea', 2, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(0, 'Todo', 'startDate', 1, 'startDate', 'date', 4, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0),
(0, 'Todo', 'endDate', 1, 'endDate', 'date', 5, 1, NULL, NULL, '', 4, 'center', 1, 4, '1', 0, 0, 0),
(0, 'Todo', 'priority', 1, 'priority', 'selectValues', 6, 1, NULL, '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10', '5', 5, 'center', 1, 5, '1', 1, 0, 0),
(0, 'Todo', 'currentStatus', 1, 'currentStatus', 'selectValues', 7, 1, NULL, '1#Accepted|2#Working|4#Ended|5#Stopped|7#Waiting', '1', 6, 'center', 1, 6, '1', 0, 0, 0),
(0, 'Todo', 'projectId', 1, 'project', 'selectValues', 3, 1, NULL, 'Project#id#title', '', 0, NULL, 1, 1, '1', 1, 1, 0),
(0, 'Todo', 'userId', 1, 'User', 'selectValues', 8, 1, NULL, 'User#id#lastname', '', 0, NULL, 1, 1, '1', 1, 0, 0),

(0, 'Note', 'projectId', 1, 'project', 'selectValues', 3, 1, NULL, 'Project#id#title', '', 0, NULL, 1, 1, '1', 0, 1, 0),
(0, 'Note', 'title', 1, 'title', 'text', 1, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Note', 'comments', 1, 'comments', 'textarea', 2, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(0, 'Note', 'category', 1, 'category', 'text', 4, 2, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0),

(0, 'Calendar', 'title', 1, 'title', 'text', 1, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Calendar', 'notes', 1, 'notes', 'textarea', 2, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(0, 'Calendar', 'startDate', 1, 'startDate', 'date', 3, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 1, 0),
(0, 'Calendar', 'startTime', 1, 'startTime', 'time', 4, 1, NULL, NULL, '', 4, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Calendar', 'endDate', 1, 'endDate', 'date', 0, 1, NULL, NULL, '', 0, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Calendar', 'endTime', 1, 'endTime', 'time', 6, 1, NULL, NULL, '', 6, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Calendar', 'participantId', 1, 'participantId', 'multipleSelectValues', 7, 1, NULL, 'User#id#username', '', 0, 'left', 1, 1, '1', 1, 1, 0),
(0, 'Calendar', 'rrule', 1, 'rrule', 'hidden', 9, 1, NULL, NULL, '', NULL, NULL, 1, 0, '1', 0, 0, 0),

(0, 'Filemanager', 'title', 1, 'Title', 'text', 1, 1, NULL, '', '', 1, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Filemanager', 'comments', 1, 'Comments', 'textarea', 2, 1, NULL, '', '', 0, 'center', 1, 0, '1', 0, 0, 0),
(0, 'Filemanager', 'projectId', 1, 'Project', 'selectValues', 3, 1, NULL, 'Project # id # title', '1', 0, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Filemanager', 'category', 1, 'Category', 'text', 4, 1, NULL, '', '', 2, 'center', 1, 0, '1', 0, 0, 0),
(0, 'Filemanager', 'files', 1, 'Upload', 'upload', 5, 1, NULL, '', '', 3, 'center', 1, 0, '1', 0, 1, 0),

(0, 'Contact', 'name', 1, 'Name', 'text', 1, 1, NULL, '', '', 1, 'left', 1, 0, '1', 0, 1, 0),
(0, 'Contact', 'email', 1, 'E-Mail', 'text', 2, 1, NULL, '', '', 2, 'left', 1, 0, '1', 0, 0, 0),
(0, 'Contact', 'company', 1, 'Company', 'text', 3, 1, NULL, '', '', 0, '', 1, 0, '1', 0, 0, 0),
(0, 'Contact', 'firstphone', 1, 'First phone', 'text', 4, 1, NULL, '', '',  3, 'left', 1, 0, '1', 0, 0, 0),
(0, 'Contact', 'secondphone', 1, 'Second phone', 'text', 5, 1, NULL, '', '', 0, '', 1, 0, '1', 0, 0, 0),
(0, 'Contact', 'mobilephone', 1, 'Mobile phone', 'text', 6, 1, NULL, '', '', 0, '', 1, 0, '1', 0, 0, 0),
(0, 'Contact', 'street', 1, 'Street', 'text', 7, 1, NULL, '', '', 4, 'left', 1, 0, '1', 0, 0, 0),
(0, 'Contact', 'city', 1, 'City', 'text', 8, 1, NULL, '', '', 0, '', 1, 0, '1', 0, 0, 0),
(0, 'Contact', 'zipcode', 1, 'Zip Code', 'text', 9, 1, NULL, '', '', 0, '', 1, 0, '1', 0, 0, 0),
(0, 'Contact', 'country', 1, 'Country', 'text', 10, 1, NULL, '', '', 0, '', 1, 0, '1', 0, 0, 0),
(0, 'Contact', 'comment', 1, 'Comment', 'textarea', 11, 1, NULL, '', '', 0, '', 1, 0, '1', 0, 0, 0),
(0, 'Contact', 'private', 1, 'Private', 'selectValues', 12, 1, NULL, '0#No|1#Yes', '0', 5, 'center', 1, 0, '1', 0, 0, 0);

INSERT INTO `User` (`id`, `username`,`firstname`, `lastname`,`status`, `admin`) VALUES
(1,'admin','MyName1','MyLastName1','A', 1),
(2,'test','MyName2','MyLastName2','A', 0);


INSERT INTO `Setting` (`id`, `userId`, `moduleId`, `keyvalue`, `value`, `identifier`) VALUES
(1, 1, 0, 'password','156c3239dbfa5c5222b51514e9d12948', 'Core'),
(2, 1, 0, 'email','test@example.com', 'Core'),
(3, 1, 0, 'language','en', 'Core'),
(4, 1, 0, 'timeZone','2', 'Core'),
(5, 2, 0, 'password','156c3239dbfa5c5222b51514e9d12948', 'Core'),
(6, 2, 0, 'email','test@example.com', 'Core'),
(7, 2, 0, 'language','en', 'Core'),
(8, 2, 0, 'timeZone','2', 'Core');

INSERT INTO `Project` (`id`, `projectId`, `path`, `title`, `notes`, `ownerId`, `startDate`, `endDate`, `priority`, `currentStatus`, `completePercent`, `hourlyWageRate`, `budget`) VALUES
(1, NULL, '/', 'PHProjekt', 'Test', 1, '2008-05-02', '2008-07-02', 1, 'working', 0, NULL, NULL),
(2, 1, '/1/', 'Project 1', 'Test', 1, '2008-05-02', '2008-07-02', 2, 'working', 0, NULL, NULL),
(3, 2, '/1/2/', 'Sub Project', 'Test',1, '2008-05-02', '2008-07-02', 2, 'working', 0, NULL, NULL);

INSERT INTO `Groups` (`id`, `name`) VALUES
(1, 'default'),
(2, 'ninatest');

INSERT INTO `Role` (`id`, `name`, `parent`) VALUES
(1, 'admin in all', null), #Necessary
(2, 'can Read TODOs and CALENDARsonly', null),
(3, 'admin in CALENDARs and PROJECTs', null),
(4, 'read only in All', null);

INSERT INTO `GroupsUserRelation` (`id`, `groupsId`, `userId`) VALUES
(1, 1, 1),
(2, 2, 2);


INSERT INTO `ProjectRoleUserPermissions` (`projectId`, `userId`, `roleId`) VALUES
(1, 1, 1),
(1, 2, 1),

(2, 1, 1),
(2, 2, 2),

(3, 1, 4),
(3, 2, 4);

INSERT INTO `RoleModulePermissions` (`roleId`, `moduleId`, `access`) VALUES
(1, 1, 139),
(1, 2, 139),
(1, 3, 139),
(1, 4, 139),
(1, 5, 139),
(1, 6, 139),
(1, 7, 139),
(1, 8, 139),
(1, 9, 139),

(2, 1, 0),
(2, 2, 1),
(2, 3, 0),
(2, 4, 0),
(2, 5, 1),
(2, 6, 0),
(2, 7, 0),
(2, 8, 0),
(2, 9, 0),

(3, 1, 139),
(3, 2, 0),
(3, 3, 0),
(3, 4, 0),
(3, 5, 139),
(3, 6, 0),
(3, 7, 0),
(3, 8, 0),
(3, 9, 0),

(4, 1, 1),
(4, 2, 1),
(4, 3, 1),
(4, 4, 1),
(4, 5, 1),
(4, 6, 1),
(4, 7, 1),
(4, 8, 1),
(4, 9, 1);

INSERT INTO `ItemRights` (`moduleId`, `itemId`, `userId`, `access`) VALUES
(1, 1, 1, 255),
(1, 1, 2, 255),

(1, 2, 1, 255),
(1, 2, 2, 1),

(1, 3, 1, 255),
(1, 3, 2, 255);

INSERT INTO `ProjectModulePermissions` (`moduleId`, `projectId`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),

(1, 2),
(2, 2),
(3, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),

(1, 3),
(6, 3);

INSERT INTO `Tab` (`id`, `label` ) VALUES
(1, 'Basic Data');

COMMIT;
