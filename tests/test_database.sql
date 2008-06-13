-- MySQL dump 10.11
--
-- Host: localhost    Database: phprojekt-mvc
-- ------------------------------------------------------
-- Server version	5.0.38-Ubuntu_0ubuntu1-log

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
DROP TABLE IF EXISTS `Tabs`;
DROP TABLE IF EXISTS `SearchWords`;
DROP TABLE IF EXISTS `SearchWordModule`;
DROP TABLE IF EXISTS `SearchDisplay`;
DROP TABLE IF EXISTS `UserModuleSetting`;
DROP TABLE IF EXISTS `Todo`;
DROP TABLE IF EXISTS `RoleModulePermissions`;
DROP TABLE IF EXISTS `Role`;
DROP TABLE IF EXISTS `ProjectUserRoleRelation`;
DROP TABLE IF EXISTS `ModuleProjectRelation`;
DROP TABLE IF EXISTS `Project`;
DROP TABLE IF EXISTS `History`;
DROP TABLE IF EXISTS `GroupsUserRelation`;
DROP TABLE IF EXISTS `Groups`;
DROP TABLE IF EXISTS `Module`;
DROP TABLE IF EXISTS `User`;
DROP TABLE IF EXISTS `DatabaseManager`;
DROP TABLE IF EXISTS `ModuleInstance`;
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
  `password` varchar(32) NOT NULL,
  `firstname` varchar(255) default NULL,
  `lastname` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `language` varchar(5) NOT NULL,
  `status` varchar(1) default 'A',
  PRIMARY KEY(`id`),
  UNIQUE(`username`)
);


--
-- Table structure for table `Module`
--
CREATE TABLE `Module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(255) NOT NULL,
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
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`userId`) REFERENCES User(`id`)
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
  `oldValue` varchar(100) default NULL,
  `newValue` varchar(255) default NULL,
  `action` varchar(50) NOT NULL,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`userId`) REFERENCES User(`id`),
  FOREIGN KEY (`moduleId`) REFERENCES Module(`id`)
);
CREATE INDEX `History_userId` ON `History`(`userId`);


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
  `completePercent` float default '0',
  `hourlyWageRate` float default NULL,
  `budget` float default NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`ownerId`) REFERENCES User(`id`)
);
CREATE INDEX `Project_ownerId` ON `Project`(`ownerId`);


--
-- Table structure for table `ModuleProjectRelation`
--
CREATE TABLE `ModuleProjectRelation` (
    `moduleId` int(11) NOT NULL,
    `projectId` int(11) NOT NULL,
    `isActive` int(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (`moduleId`) REFERENCES Module(`id`),
    FOREIGN KEY (`projectId`) REFERENCES Project(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);


--
-- Table structure for table `ProjectUserRoleRelation`
--
CREATE TABLE `ProjectUserRoleRelation` (
  `projectId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL
);
CREATE INDEX `ProjectUserRoleRelation_projectId` ON `ProjectUserRoleRelation`(`projectId`);
CREATE INDEX `ProjectUserRoleRelation_userId` ON `ProjectUserRoleRelation`(`userId`);
CREATE INDEX `ProjectUserRoleRelation_roleId` ON `ProjectUserRoleRelation`(`roleId`);


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
-- Table structure for table `RoleModulePermissions`
--
CREATE TABLE `RoleModulePermissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleId` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `permission` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`roleId`) REFERENCES Role(`id`),
  FOREIGN KEY (`moduleId`) REFERENCES Module(`id`)
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
-- Table structure for table `UserModuleSetting`
--
CREATE TABLE `UserModuleSetting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `keyValue` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `identifier`  varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`userId`) REFERENCES User(`id`),
  FOREIGN KEY (`moduleId`) REFERENCES Module(`id`)
);
CREATE INDEX `UserModuleSetting_userId` ON `UserModuleSetting`(`userId`);

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
  `firstDisplay` varchar(255),
  `secondDisplay` varchar(255),
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
-- Table structure for table `Tabs`
--
CREATE TABLE `Tabs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);


--
-- Table structure for table `TabModuleRelation`
--
CREATE TABLE `TabModuleRelation` (
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
  `key` varchar(255) NOT NULL,
  `value` text default NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`moduleId`) REFERENCES Module(`id`)
);


--
-- Table structure for table `ItemRights`
--
CREATE TABLE `ItemRights` (
  `moduleId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `adminAccess` int(1) NOT NULL,
  `writeAccess` int(1) NOT NULL,
  `readAccess` int(1) NOT NULL,
  PRIMARY KEY  (`moduleId`,`itemId`,`userId`)
);


--
-- Table structure for table `Timecard`
--
CREATE TABLE `Timecard` (
  `id` int(11) NOT NULL auto_increment,
  `notes` text default NULL,
  `ownerId` int(11) default NULL,
  `projectId` int(11) default NULL,
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
  `projectId` int(11) default NULL,
  `date` date default NULL,
  `startTime` time default NULL,
  `endTime` time default NULL,
  PRIMARY KEY  (`id`)
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
CREATE INDEX `ModuleInstance_userId` ON `ModuleInstance`(`projectId`);

--
-- Table structure for table `Calendar`
--
CREATE TABLE `Calendar` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `notes` text default NULL,
  `ownerId` int(11) default NULL,
  `projectId` int(11) default NULL,
  `startDate` date default NULL,
  `endDate` date default NULL,
  `participantId` int(11) default NULL,
  `startTime` time default NULL,
  `endTime` time default NULL,
  `parentId` int(11) default NULL,
  PRIMARY KEY  (`id`)
);

--
-- INSERT DATA
--

INSERT INTO `Module` (`id`, `module`) VALUES
(1, 'Project'),
(2, 'Todo'),
(3, 'Note'),
(4, 'Timecard'),
(5, 'Timeproj'),
(6, 'Calendar');

INSERT INTO `DatabaseManager` (`id`, `tableName`, `tableField`, `formTab`, `formLabel`, `formTooltip`, `formType`, `formPosition`, `formColumns`, `formRegexp`, `formRange`, `defaultValue`, `listPosition`, `listAlign`, `listUseFilter`, `altPosition`, `status`, `isInteger`, `isRequired`, `isUnique`) VALUES
(0, 'Project', 'projectId', 1, 'parent', 'parent', 'tree', 1, 1, NULL, 'Project', '1', 2, 'left', 1, 1, '1', 1, 0, 0),
(0, 'Project', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Project', 'notes', 1, 'notes', 'notes', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(0, 'Project', 'startDate', 1, 'startDate', 'startDate', 'date', 4, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 1, 0),
(0, 'Project', 'endDate', 1, 'endDate', 'endDate', 'date', 5, 1, NULL, NULL, '', 4, 'center', 1, 4, '1', 0, 1, 0),
(0, 'Project', 'priority', 1, 'priority', 'priority', 'selectValues', 6, 1, NULL, '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10', '5', 5, 'center', 1, 5, '1', 1, 1, 0),
(0, 'Project', 'currentStatus', 1, 'currentStatus', 'currentStatus', 'selectValues', 7, 1, NULL, '1#Offered|2#Ordered|3#Working|4#Ended|5#Stopped|6#Re-Opened|7#Waiting', '1', 6, 'center', 1, 6, '1', 0, 0, 0),
(0, 'Project', 'completePercent', 1, 'completePercent', 'completePercent', 'text', 8, 1, NULL, NULL, '', 7, 'center', 1, 7, '1', 0, 0, 0),
(0, 'Project', 'budget', 1, 'budget', 'budget', 'text', 9, 1, NULL, NULL, '', 0, NULL, 1, 8, '1', 0, 0, 0),

(0, 'Todo', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Todo', 'notes', 1, 'notes', 'notes', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
(0, 'Todo', 'startDate', 1, 'startDate', 'startDate', 'date', 4, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 1, 0),
(0, 'Todo', 'endDate', 1, 'endDate', 'endDate', 'date', 5, 1, NULL, NULL, '', 4, 'center', 1, 4, '1', 0, 1, 0),
(0, 'Todo', 'priority', 1, 'priority', 'priority', 'selectValues', 6, 1, NULL, '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10', '5', 5, 'center', 1, 5, '1', 1, 1, 0),
(0, 'Todo', 'currentStatus', 1, 'currentStatus', 'currentStatus', 'selectValues', 7, 1, NULL, '1#Accepted|2#Working|4#Ended|5#Stopped|7#Waiting', '1', 6, 'center', 1, 6, '1', 0, 0, 0),
(0, 'Todo', 'projectId', 1, 'project', 'project', 'tree', 1, 1, NULL, 'Project', '', 2, 'center', 1, 1, '1', 1, 0, 0),

(0, 'History', 'userId', 1, 'UserId', 'UserId', 'userId', '1', '1', NULL, NULL, 0, 1, 'left', 1, 1, 1, 1, 1, 0),
(0, 'History', 'itemId', 1, 'ItemId', 'ItemId', 'text', '2', '1', NULL, NULL, 0, 2, 'center', 1, 2, 1, 1, 1, 0),
(0, 'History', 'moduleId', 1, 'Module', 'Module', 'text', '3', '1', NULL, NULL, '', 3, 'left', 1, 3, 1, 0, 1, 0),
(0, 'History', 'field', 1, 'Field', 'Field', 'text', '4', '1', NULL, NULL, '', 4, 'left', 1, 4, 1, 0, 1, 0),
(0, 'History', 'oldValue', 1, 'OldValue', 'OldValue', 'text', '5', '1', NULL, NULL, '', 0, '', 0, 0, 1, 0, 1, 0),
(0, 'History', 'newValue', 1, 'NewValue', 'NewValue', 'text', '6', '1', NULL, NULL, '', 0, '', 0, 0, 1, 0, 1, 0),
(0, 'History', 'action', 1, 'Action', 'Action', 'text', '7', '1', NULL, NULL, '', 7, 'left', 1, 7, 1, 0, 1, 0),
(0, 'History', 'datetime', 1, 'Datetime', 'Datetime', 'datetime', '8', '1', NULL, NULL, '', 8, 'center', 1, 8, 1, 0, 1, 0),

(0, 'Note', 'projectId', 1, 'project', 'project', 'tree', 1, 1, NULL, 'Project', '', 2, 'left', 1, 1, '1', 0, 1, 0),
(0, 'Note', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Note', 'comments', 1, 'comments', 'comments', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 1, 0),
(0, 'Note', 'category', 1, 'category', 'category', 'selectSqlAddOne', 4, 2, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0),
(0, 'Timecard', 'notes'    , 1, 'notes'    , 'notes'    , 'text'    , 1, 2, NULL, NULL     , '', 1, NULL    , 1, 0, '1', 0, 1, 0),
(0, 'Timecard', 'date'     , 1, 'date'     , 'date'     , 'date'    , 2, 1, NULL, NULL     , '', 2, 'center', 1, 1, '1', 0, 1, 0),
(0, 'Timecard', 'startTime', 1, 'startTime', 'startTime', 'time'    , 3, 1, NULL, NULL     , '', 3, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Timecard', 'endTime'  , 1, 'endTime'  , 'endTime'  , 'time'    , 4, 1, NULL, NULL     , '', 4, 'center', 1, 0, '1', 0, 0, 0),
(0, 'Timecard', 'projectId', 1, 'project'  , 'project'  , 'tree'    , 0, 0, NULL, 'Project', '', 0, 'center', 1, 0, '1', 1, 0, 0),
(0, 'Timeproj', 'notes'    , 1, 'notes'    , 'notes'    , 'text'    , 1, 2, NULL, NULL     , '', 1, NULL    , 1, 0, '1', 0, 1, 0),
(0, 'Timeproj', 'date'     , 1, 'date'     , 'date'     , 'date'    , 2, 1, NULL, NULL     , '', 2, 'center', 1, 1, '1', 0, 1, 0),
(0, 'Timeproj', 'startTime', 1, 'startTime', 'startTime', 'time'    , 3, 1, NULL, NULL     , '', 3, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Timeproj', 'endTime'  , 1, 'endTime'  , 'endTime'  , 'time'    , 4, 1, NULL, NULL     , '', 4, 'center', 1, 0, '1', 0, 0, 0),
(0, 'Timeproj', 'projectId', 1, 'project'  , 'project'  , 'tree'    , 5, 1, NULL, 'Project', '', 0, 'center', 1, 0, '1', 1, 1, 0),
(0, 'Calendar', 'title',     1, 'title'    , 'title'    , 'text'    , 1, 1, NULL, NULL     , '', 1, 'left'  , 1, 2, '1', 0, 1, 0),
(0, 'Calendar', 'notes',     1, 'notes'    , 'notes'    , 'textarea', 2, 2, NULL, NULL     , '', 0, NULL    , 1, 0, '1', 0, 0, 0),
(0, 'Calendar', 'startDate', 1, 'startDate', 'startDate', 'date'    , 3, 1, NULL, NULL     , '', 3, 'center', 1, 3, '1', 0, 1, 0),
(0, 'Calendar', 'endDate',   1, 'endDate'  , 'endDate'  , 'date'    , 5, 1, NULL, NULL     , '', 5, 'center', 1, 3, '1', 0, 1, 0),
(0, 'Calendar', 'participantId', 1, 'participantId', 'participantId'   , 'participantId'  , 8, 1, NULL, NULL     , '', 2, 'left'  , 1, 1, '1', 1, 1, 0),
(0, 'Calendar', 'startTime', 1, 'startTime', 'startTime', 'time'    , 4, 1, NULL, NULL     , '', 4, 'center', 1, 0, '1', 0, 1, 0),
(0, 'Calendar', 'endTime',   1, 'endTime'  , 'endTime'  , 'time'    , 6, 1, NULL, NULL     , '', 6, 'center', 1, 0, '1', 0, 0, 0),
(0, 'Calendar', 'projectId', 1, 'project' , 'project'   , 'tree'    , 7, 1, NULL, 'Project', '', 7, 'center', 1, 0, '1', 1, 1, 0);

INSERT INTO `User` (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `language`, `status`) VALUES
(1,'david','156c3239dbfa5c5222b51514e9d12948',NULL,NULL,'test@example.com','de_DE','A'),
(2,'gus','156c3239dbfa5c5222b51514e9d12948',NULL,NULL, '', 'de_DE','A'),
(3,'inactive','156c3239dbfa5c5222b51514e9d12948',NULL,NULL, '', 'de_DE','I');

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

INSERT INTO `GroupsUserRelation` (`id`, `groupsId`, `userId`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 1);

INSERT INTO `Role` (`id`, `name`, `parent`) VALUES
(1, 'admin', 0);

INSERT INTO `ProjectUserRoleRelation` (`projectId`, `userId`, `roleId`) VALUES
(1, 1, 1);

INSERT INTO `RoleModulePermissions` (`id`, `roleId`, `moduleId`, `permission`) VALUES
(1, 1, 1, 'write'),
(2, 1, 2, 'write'),
(3, 1, 3, 'write'),
(4, 1, 6, 'write');

INSERT INTO `ItemRights` (`moduleId`, `itemId`, `userId`, `adminAccess`, `writeAccess`, `readAccess`) VALUES
(1, 1, 1, 1, 1, 1),
(1, 1, 3, 1, 1, 1),
(1, 2, 1, 1, 1, 1),
(1, 2, 3, 0, 0, 1),
(1, 4, 1, 0, 0, 1),
(1, 4, 3, 0, 0, 1),
(1, 5, 1, 0, 0, 1),
(1, 5, 3, 0, 0, 1),
(1, 6, 1, 1, 1, 1),
(1, 7, 1, 1, 1, 1),
(1, 8, 1, 1, 1, 1),
(1, 9, 1, 1, 1, 1),
(1, 10, 1, 1, 1, 1),
(4, 1, 1, 1, 1, 1),
(4, 2, 1, 1, 1, 1),
(4, 3, 1, 1, 1, 1),
(4, 4, 1, 1, 1, 1),
(4, 5, 1, 1, 1, 1),
(4, 6, 1, 1, 1, 1),
(2, 1, 1, 1, 1, 1),
(2, 1, 3, 0, 1, 1),
(5, 1, 1, 1, 1, 1),
(5, 2, 1, 1, 1, 1),
(5, 3, 1, 1, 1, 1),
(5, 4, 1, 1, 1, 1),
(5, 5, 1, 1, 1, 1),
(5, 6, 1, 1, 1, 1);

INSERT INTO `Todo` (`id`, `title`, `notes`, `ownerId`, `projectId`, `startDate`, `endDate`, `priority`, `currentStatus`) VALUES
(1,'Todo of Test Project','',1,1,'2007-12-12','2007-12-31',0,'working');

INSERT INTO `Timecard` (`id`, `notes`, `ownerId`, `projectId`, `date`, `startTime`, `endTime`) VALUES
(1, 'Timecard row', 1, 1, '2008-04-29', '08:00:00', '13:00:00'),
(2, 'Timecard row 2', 1, 1, '2008-04-29', '14:00:00', '18:00:00'),
(3, 'Timecard row 3', 1, 1, '2008-04-30', '08:00:00', '13:00:00'),
(4, 'Timecard row 4', 1, 1, '2008-04-30', '14:00:00', '18:00:00'),
(5, 'Timecard row 6', 1, 1, '2008-05-02', '08:00:00', '13:00:00'),
(6, 'Timecard row 7', 1, 1, '2008-05-02', '14:00:00', '18:00:00');

INSERT INTO `Timeproj` (`id`, `notes`, `ownerId`, `projectId`, `date`, `startTime`, `endTime`) VALUES
(1, 'Timeproj row', 1, 1, '2008-04-29', '08:00:00', '13:00:00'),
(2, 'Timeproj row 2', 1, 1, '2008-04-29', '14:00:00', '18:00:00'),
(3, 'Timeproj row 3', 1, 1, '2008-04-30', '08:00:00', '13:00:00'),
(4, 'Timeproj row 4', 1, 1, '2008-04-30', '14:00:00', '18:00:00'),
(5, 'Timeproj row 6', 1, 1, '2008-05-02', '08:00:00', '13:00:00'),
(6, 'Timeproj row 7', 1, 1, '2008-05-02', '14:00:00', '18:00:00');

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

INSERT INTO `ModuleProjectRelation` (`moduleId`, `projectId`, `isActive`) VALUES
(1,1,1),
(2,1,1),
(3,1,1),
(4,1,1),
(5,1,1),
(6,1,1),
(1,2,1),
(2,2,1),
(3,2,1),
(4,2,1),
(5,2,1),
(6,2,1),
(1,3,1),
(2,3,1),
(3,3,1),
(4,3,1),
(5,3,1),
(6,3,1),
(1,4,1),
(2,4,1),
(3,4,1),
(4,4,1),
(5,4,1),
(6,4,1),
(1,5,1),
(2,5,1),
(3,5,1),
(4,5,1),
(5,5,1),
(6,5,1),
(1,6,1),
(2,6,0),
(3,6,1),
(4,6,1),
(5,6,1),
(6,6,1),
(1,7,1),
(2,7,1),
(3,7,1),
(4,7,1),
(5,7,1),
(6,7,1);
