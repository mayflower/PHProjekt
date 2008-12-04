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
DROP TABLE IF EXISTS `ModuleInstance`;
DROP TABLE IF EXISTS `Module`;
DROP TABLE IF EXISTS `User`;
DROP TABLE IF EXISTS `DatabaseManager`;
DROP TABLE IF EXISTS `Calendar`;

--
-- Table structure for table `DatabaseManager`
--
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
);


--
-- Table structure for table `User`
--
CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `firstname` varchar(255) default NULL,
  `lastname` varchar(255) default NULL,
  `status` varchar(1) default 'A',
  PRIMARY KEY(`id`),
  UNIQUE(`username`)
);


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
);


--
-- Table structure for table `Groups`
--
CREATE TABLE `Groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  PRIMARY KEY  (`id`)
);


--
-- Table structure for table `GroupsUserRelation`
--
CREATE TABLE `GroupsUserRelation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupsId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
);


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
);


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
  `completePercent` varchar(50) default NULL,
  `hourlyWageRate` varchar(10) default NULL,
  `budget` varchar(10) default NULL,
  PRIMARY KEY (`id`)
);


--
-- Table structure for table `ProjectModulePermissions `
--
CREATE TABLE `ProjectModulePermissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `moduleId` int(11) NOT NULL,
    `projectId` int(11) NOT NULL,
    PRIMARY KEY (`id`)
);


--
-- Table structure for table `Role`
--
CREATE TABLE `Role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent` int(11) default NULL,
  PRIMARY KEY(`id`)
);


--
-- Table structure for table `ProjectRoleUserPermissions `
--
CREATE TABLE `ProjectRoleUserPermissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);


--
-- Table structure for table `RoleModulePermissions`
--
CREATE TABLE `RoleModulePermissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleId` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `access` int(3) NOT NULL,
  PRIMARY KEY  (`id`)
);


--
-- Table structure for table `Todo`
--
CREATE TABLE `Todo` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `notes` text default NULL,
  `ownerId` int(11) default NULL,
  `projectId` int(11) default NULL,
  `startDate` date default NULL,
  `endDate` date default NULL,
  `priority` int(11) default NULL,
  `currentStatus` varchar(50) NOT NULL default 'working',
  PRIMARY KEY  (`id`)
);


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
);


--
-- Table structure for table `SearchWords`
--
CREATE TABLE `SearchWords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
);


--
-- Table structure for table `SearchWordModule`
--
CREATE TABLE `SearchWordModule` (
  `moduleId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `wordId` int(11) NOT NULL,
  PRIMARY KEY  (`itemId`,`moduleId`,`wordId`)
);


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
);


--
-- Table structure for table `Tags`
--
CREATE TABLE `Tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `crc32` bigint NOT NULL,
  PRIMARY KEY  (`id`)
);


--
-- Table structure for table `TagsUsers`
--
CREATE TABLE `TagsUsers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `tagId` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
);


--
-- Table structure for table `TagsModules`
--
CREATE TABLE `TagsModules` (
  `moduleId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `tagUserId` int(11) NOT NULL,
  PRIMARY KEY  (`moduleId`, `itemId`, `tagUserId`)
);


--
-- Table structure for table `Tab`
--
CREATE TABLE `Tab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);


--
-- Table structure for table `ModuleTabRelation`
--
CREATE TABLE `ModuleTabRelation` (
  `tabId` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  PRIMARY KEY (`tabId`, `moduleId`)
);


--
-- Table structure for table `Note`
--
CREATE TABLE `Note` (
  `id` int(11) NOT NULL auto_increment,
  `projectId` int(11) default NULL,
  `title` varchar(255) NOT NULL,
  `comments` text default NULL,
  `category` varchar(50) default NULL,
  `ownerId` int(11) default NULL,
  PRIMARY KEY  (`id`)
);


--
-- Table structure for table `Configuration`
--
CREATE TABLE `Configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduleId` int(11) NOT NULL,
  `keyValue` varchar(255) NOT NULL,
  `value` text default NULL,
  PRIMARY KEY  (`id`)
);


--
-- Table structure for table `ItemRights`
--
CREATE TABLE `ItemRights` (
  `moduleId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `access` int(3) NOT NULL,
  PRIMARY KEY  (`moduleId`,`itemId`,`userId`)
);


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
);


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
);


--
-- Table structure for table `Calendar`
--
CREATE TABLE `Calendar` (
  `id` int(11) NOT NULL auto_increment,
  `parentId` int(11) default NULL,
  `ownerId` int(11) default NULL,
  `projectId` int(11) NOT NULL,
  `title` varchar(255) default NULL,
  `notes` text,
  `uid` varchar(255) NOT NULL,
  `recurrence_id` varchar(100) default NULL,
  `startDate` date default NULL,
  `startTime` time default NULL,
  `endDate` date default NULL,
  `endTime` time default NULL,
  `created` int(11) default NULL,
  `modified` int(10) unsigned default NULL,
  `timezone` varchar(50) NOT NULL,
  `location` varchar(255) default NULL,
  `categories` text NOT NULL,
  `attendee` text,
  `status` int(1) default NULL,
  `priority` int(1) default NULL,
  `class` int(1) default NULL,
  `transparent` int(1) NOT NULL,
  `rrule` text,
  `properties` text,
  `deleted` int(1) default NULL,
  `participantId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `ownerid` (`ownerId`,`projectId`),
  KEY `startDate` (`startDate`,`startTime`),
  KEY `endDate` (`endDate`,`endTime`),
  KEY `parentId` (`parentId`)
);


--
-- Table structure for table `ModuleInstance`
--
CREATE TABLE `ModuleInstance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) default NULL,
  `module` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
);


--
-- INSERT DATA
--

INSERT INTO `Module` (`id`, `name`, `label`, `saveType`, `active`) VALUES
(1, 'Project', 'Project', 0, 1),
(2, 'Todo', 'Todo', 0, 1),
(3, 'Note', 'Note', 0, 1),
(4, 'Timecard', 'Timecard', 1, 1),
(5, 'Calendar', 'Calendar', 1, 1),
(6, 'Gantt', 'Gantt', 0, 1);

INSERT INTO `DatabaseManager` (`id`, `tableName`, `tableField`, `formTab`, `formLabel`, `formTooltip`, `formType`, `formPosition`, `formColumns`, `formRegexp`, `formRange`, `defaultValue`, `listPosition`, `listAlign`, `listUseFilter`, `altPosition`, `status`, `isInteger`, `isRequired`, `isUnique`) VALUES
(0, 'Project', 'title', 1, 'title', 'title', 'text', 1, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Project', 'notes', 1, 'notes', 'notes', 'textarea', 2, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(0, 'Project', 'projectId', 1, 'parent', 'parent', 'selectValues', 3, 1, NULL, 'Project#id#title', '1', 0, NULL, 1, 1, '1', 1, 0, 0),
(0, 'Project', 'startDate', 1, 'startDate', 'startDate', 'date', 4, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0),
(0, 'Project', 'endDate', 1, 'endDate', 'endDate', 'date', 5, 1, NULL, NULL, '', 4, 'center', 1, 4, '1', 0, 0, 0),
(0, 'Project', 'priority', 1, 'priority', 'priority', 'selectValues', 6, 1, NULL, '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10', '5', 5, 'center', 1, 5, '1', 1, 0, 0),
(0, 'Project', 'currentStatus', 1, 'currentStatus', 'currentStatus', 'selectValues', 7, 1, NULL, '1#Offered|2#Ordered|3#Working|4#Ended|5#Stopped|6#Re-Opened|7#Waiting', '1', 6, 'center', 1, 6, '1', 0, 0, 0),
(0, 'Project', 'completePercent', 1, 'completePercent', 'completePercent', 'percentage', 8, 1, NULL, NULL, '', 7, 'center', 1, 7, '1', 0, 0, 0),
(0, 'Project', 'budget', 1, 'budget', 'budget', 'text', 9, 1, NULL, NULL, '', 0, NULL, 1, 8, '1', 0, 0, 0),

(0, 'Todo', 'title', 1, 'title', 'title', 'text', 1, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Todo', 'notes', 1, 'notes', 'notes', 'textarea', 2, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(0, 'Todo', 'startDate', 1, 'startDate', 'startDate', 'date', 4, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0),
(0, 'Todo', 'endDate', 1, 'endDate', 'endDate', 'date', 5, 1, NULL, NULL, '', 4, 'center', 1, 4, '1', 0, 0, 0),
(0, 'Todo', 'priority', 1, 'priority', 'priority', 'selectValues', 6, 1, NULL, '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10', '5', 5, 'center', 1, 5, '1', 1, 0, 0),
(0, 'Todo', 'currentStatus', 1, 'currentStatus', 'currentStatus', 'selectValues', 7, 1, NULL, '1#Accepted|2#Working|4#Ended|5#Stopped|7#Waiting', '1', 6, 'center', 1, 6, '1', 0, 0, 0),
(0, 'Todo', 'projectId', 1, 'project', 'project', 'selectValues', 3, 1, NULL, 'Project#id#title', '', 0, NULL, 1, 1, '1', 1, 0, 0),

(0, 'Note', 'projectId', 1, 'project', 'project', 'selectValues', 3, 1, NULL, 'Project#id#title', '', 0, NULL, 1, 1, '1', 0, 1, 0),
(0, 'Note', 'title', 1, 'title', 'title', 'text', 1, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 0, 0),
(0, 'Note', 'comments', 1, 'comments', 'comments', 'textarea', 2, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(0, 'Note', 'category', 1, 'category', 'category', 'text', 4, 2, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0),

(0, 'Calendar', 'title', 1, 'title', 'title', 'text', 1, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Calendar', 'notes', 1, 'notes', 'notes', 'textarea', 2, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(0, 'Calendar', 'startDate', 1, 'startDate', 'startDate', 'date', 3, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 1, 0),
(0, 'Calendar', 'startTime', 1, 'startTime', 'startTime', 'time', 4, 1, NULL, NULL, '', 4, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Calendar', 'endDate', 1, 'endDate', 'endDate', 'date', 5, 1, NULL, NULL, '', 5, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Calendar', 'endTime', 1, 'endTime', 'endTime', 'time', 6, 1, NULL, NULL, '', 6, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Calendar', 'participantId', 1, 'participantId', 'participantId', 'multipleSelectValues', 8, 1, NULL, 'User#id#username', '', 2, 'left', 1, 1, '1', 1, 1, 0),
(0, 'Calendar', 'rrule', 1, 'rrule', 'rrule', 'hidden', 9, 1, NULL, NULL, '', NULL, NULL, 1, 0, '1', 0, 0, 0);

INSERT INTO `User` (`id`, `username`, `firstname`, `lastname`, `status`) VALUES
(1,'david', NULL, NULL, 'A'),
(2,'gus', NULL, NULL, 'A'),
(3,'inactive', NULL, NULL, 'I');

INSERT INTO `Setting` (`id`, `userId`, `moduleId`, `keyvalue`, `value`, `identifier`) VALUES
(1, 1, 0, 'password','156c3239dbfa5c5222b51514e9d12948', 'Core'),
(2, 1, 0, 'email','test@example.com', 'Core'),
(3, 1, 0, 'language','en', 'Core'),
(4, 1, 0, 'timeZone','2', 'Core'),
(5, 2, 0, 'password','156c3239dbfa5c5222b51514e9d12948', 'Core'),
(6, 2, 0, 'email','test@example.com', 'Core'),
(7, 2, 0, 'language','en', 'Core'),
(8, 2, 0, 'timeZone','2', 'Core'),
(9, 3, 0, 'password','156c3239dbfa5c5222b51514e9d12948', 'Core'),
(10, 3, 0, 'email','test@example.com', 'Core'),
(11, 3, 0, 'language','en', 'Core'),
(12, 3, 0, 'timeZone','2', 'Core');

INSERT INTO `Project` (`id`, `projectId`, `path`, `title`, `notes`, `ownerId`, `startDate`, `endDate`, `priority`, `currentStatus`, `completePercent`, `hourlyWageRate`, `budget`) VALUES
(1,NULL,'/','Invisible Root','',1,NULL,NULL,NULL,'working',0,NULL,NULL),
(2,1,'/1/','Project 1','',1,NULL,NULL,NULL,'working',0,NULL,NULL),
(3,1,'/1/','Project 2','',2,NULL,NULL,NULL,'working',0,NULL,NULL),
(4,2,'/1/2/','Sub Project','',1,NULL,NULL,NULL,'working',0,NULL,NULL),
(5,2,'/1/2/','Test Project','Test note',1,NULL,NULL,NULL,'ordered',0,NULL,NULL),
(6,4,'/1/2/4/','Sub Sub Project 1','',1,NULL,NULL,NULL,'working',0,NULL,NULL),
(7,4,'/1/2/4/','Sub Sub Project 2','',1,NULL,NULL,NULL,'working',0,NULL,NULL);

INSERT INTO `Groups` (`id`, `name`) VALUES
(1, 'default'),
(2, 'ninatest'),
(3, 'ninasgruppe'),
(4, 'testgruppe');

INSERT INTO `Role` (`id`, `name`, `parent`) VALUES
(1, 'admin', 0);
INSERT INTO `GroupsUserRelation` (`id`, `groupsId`, `userId`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 1);

INSERT INTO `ProjectRoleUserPermissions` (`projectId`, `userId`, `roleId`) VALUES
(1, 1, 1);

INSERT INTO `RoleModulePermissions` (`roleId`, `moduleId`, `access`) VALUES
(1, 1, 139),
(1, 2, 139),
(1, 3, 139),
(1, 5, 139);

INSERT INTO `ItemRights` (`moduleId`, `itemId`, `userId`, `access`) VALUES
(1, 1, 1, 255),
(1, 1, 3, 255),
(1, 2, 1, 255),
(1, 2, 3, 255),
(1, 4, 1, 255),
(1, 4, 3, 255),
(1, 5, 1, 255),
(1, 5, 3, 255),
(1, 6, 1, 255),
(1, 7, 1, 255),
(1, 8, 1, 255),
(1, 9, 1, 255),
(1, 10, 1, 255),
(1, 11, 1, 255),
(4, 1, 1, 255),
(4, 2, 1, 255),
(4, 3, 1, 255),
(4, 4, 1, 255),
(4, 5, 1, 255),
(4, 6, 1, 255),
(2, 1, 1, 255),
(2, 1, 3, 255);

INSERT INTO `Todo` (`id`, `title`, `notes`, `ownerId`, `projectId`, `startDate`, `endDate`, `priority`, `currentStatus`) VALUES
(1,'Todo of Test Project','',1,1,'2007-12-12','2007-12-31',0,'working');

INSERT INTO `Timecard` (`id`, `ownerId`, `date`, `startTime`, `endTime`) VALUES
(1, 1, '2008-04-29', '0800', '1300'),
(2, 1, '2008-04-29', '1400', '1800'),
(3, 1, '2008-04-30', '0800', '1300'),
(4, 1, '2008-04-30', '1400', '1800'),
(5, 1, '2008-05-02', '0800', '1300'),
(6, 1, '2008-05-02', '1400', '1800');

INSERT INTO Tags (`id`, `word`, `crc32`) VALUES
(1,'this',-17923545),
(2,'todo',1510913696);

INSERT INTO TagsUsers (`id`, `userId`, `tagId`) VALUES
(1, 1, 1),
(2, 1, 2);

INSERT INTO TagsModules (`moduleId`, `itemId`, `tagUserId`) VALUES
(1, 1, 1);

INSERT INTO `ModuleInstance` VALUES
(1,5,'Task','Developer Tasks'),
(2,5,'Tasks','Project Tasks');

INSERT INTO `ProjectModulePermissions` (`moduleId`, `projectId`) VALUES
(1,1),
(2,1),
(3,1),
(4,1),
(5,1),
(1,2),
(2,2),
(3,2),
(4,2),
(5,2),
(1,3),
(2,3),
(3,3),
(4,3),
(5,3),
(1,4),
(2,4),
(3,4),
(4,4),
(5,4),
(1,5),
(2,5),
(3,5),
(4,5),
(5,5),
(1,6),
(2,6),
(3,6),
(4,6),
(5,6),
(1,7),
(2,7),
(3,7),
(4,7),
(5,7);

INSERT INTO SearchWords (`id`, `word`, `count`) VALUES
(1, 'NOTE', 1);

INSERT INTO searchwordmodule (`ModuleId`, `ItemId`, `WordId`) VALUES
(1, 1, 1);

INSERT INTO searchdisplay (`ModuleId`, `ItemId`, `firstDisplay`, `projectId`) VALUES
(1, 1, 'test', 1);

INSERT INTO `Tab` (`id`, `label` ) VALUES
(1, 'Basic Data');