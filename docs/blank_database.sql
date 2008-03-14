-- MySQL dump 10.11
--
-- Host: localhost    Database: phprojekt-mvc
-- ------------------------------------------------------
-- Server version	5.0.38-Ubuntu_0ubuntu1-log

BEGIN;

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
);


-- 
-- Table structure for table `Groups`
-- 
DROP TABLE IF EXISTS `Groups`;
CREATE TABLE `Groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  PRIMARY KEY  (`id`)
);


-- 
-- Table structure for table `GroupsUserRelation`
-- 
DROP TABLE IF EXISTS `GroupsUserRelation`;
CREATE TABLE `GroupsUserRelation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupsId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
);


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
);
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
);
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
  `read` int(11) default NULL,
  `write` int(11) default NULL,
  `admin` int(11) default NULL,
  PRIMARY KEY(`id`)
);
CREATE INDEX `Project_ownerId` ON `Project`(`ownerId`);


--
-- Table structure for table `ProjectUserRoleRelation`
--
DROP TABLE IF EXISTS `ProjectUserRoleRelation`;
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
DROP TABLE IF EXISTS `Role`;
CREATE TABLE `Role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent` int(11) default NULL,
  PRIMARY KEY(`id`)
);


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
);


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
  `read` int(11) default NULL,
  `write` int(11) default NULL,
  `admin` int(11) default NULL,
  PRIMARY KEY  (`id`)
 );


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
  PRIMARY KEY(`id`),
  UNIQUE(`username`)
);


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
);
CREATE INDEX `UserModuleSetting_userId` ON `UserModuleSetting`(`userId`);	 

--
-- Table structure for table `Tree`
--
DROP TABLE IF EXISTS `Tree`;


--
-- Table structure for table `SearchWords`
--
DROP TABLE IF EXISTS `SearchWords`;
CREATE TABLE `SearchWords` (
  `module` varchar(255) NOT NULL,
  `itemId` int(11) NOT NULL,
  `word` varchar(255) NOT NULL,
  `crc32` bigint NOT NULL,
  PRIMARY KEY  (`itemId`,`module`,`crc32`)
);
	  	

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
  `read` int(11) default NULL,
  `write` int(11) default NULL,
  `admin` int(11) default NULL,
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `Configuration`;
CREATE TABLE `Configuration` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `module` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text,
  PRIMARY KEY  (`id`)
);

--
-- INSERT DATA
--

INSERT INTO DatabaseManager (`id`, `tableName`, `tableField`, `formTab`, `formLabel`, `formTooltip`, `formType`, `formPosition`, `formColumns`, `formRegexp`, `formRange`, `defaultValue`, `listPosition`, `listAlign`, `listUseFilter`, `altPosition`, `status`, `isInteger`, `isRequired`, `isUnique`) VALUES 
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
(0, 'History', 'dataobjectId', 1, 'DataobjectId', 'DataobjectId', 'text', '2', '1', NULL, NULL, 0, 2, 'center', 1, 2, 1, 1, 1, 0),
(0, 'History', 'module', 1, 'Module', 'Module', 'text', '3', '1', NULL, NULL, '', 3, 'left', 1, 3, 1, 0, 1, 0),
(0, 'History', 'field', 1, 'Field', 'Field', 'text', '4', '1', NULL, NULL, '', 4, 'left', 1, 4, 1, 0, 1, 0),
(0, 'History', 'oldValue', 1, 'OldValue', 'OldValue', 'text', '5', '1', NULL, NULL, '', 0, '', 0, 0, 1, 0, 1, 0),
(0, 'History', 'newValue', 1, 'NewValue', 'NewValue', 'text', '6', '1', NULL, NULL, '', 0, '', 0, 0, 1, 0, 1, 0),
(0, 'History', 'action', 1, 'Action', 'Action', 'text', '7', '1', NULL, NULL, '', 7, 'left', 1, 7, 1, 0, 1, 0),
(0, 'History', 'datetime', 1, 'Datetime', 'Datetime', 'datetime', '8', '1', NULL, NULL, '', 8, 'center', 1, 8, 1, 0, 1, 0),

(0, 'Note', 'projectId', 1, 'project', 'project', 'tree', 1, 1, NULL, 'Project', '', 2, 'left', 1, 1, '1', 0, 1, 0),
(0, 'Note', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(0, 'Note', 'comments', 1, 'comments', 'comments', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 1, 0),
(0, 'Note', 'category', 1, 'category', 'category', 'selectSqlAddOne', 4, 2, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0);

INSERT INTO `User` (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `language`, `status`) VALUES
(1,'dsp','156c3239dbfa5c5222b51514e9d12948',NULL,NULL,'gustavo.solt@gmail.com','','A');

INSERT INTO `Project` (`id`, `projectId`, `path`, `title`, `notes`, `ownerId`, `startDate`, `endDate`, `priority`, `currentStatus`, `completePercent`, `hourlyWageRate`, `budget`, `read`, `write`, `admin`) VALUES
(1, NULL, '/', 'Invisible Root', '', NULL, NULL, NULL, NULL, 'working', 0, NULL, NULL, 1, 1, 1),
(2, 1, '/1/', 'Project 1', '', NULL, NULL, NULL, NULL, 'working', 0, NULL, NULL, 1, 1, NULL),
(3, 1, '/1/', 'Project 2', '', NULL, NULL, NULL, NULL, 'working' ,0, NULL, NULL, NULL, NULL, NULL),
(4, 2, '/1/2/', 'Sub Project', '',NULL, NULL, NULL, NULL, 'working', 0, NULL, NULL, 3, NULL, NULL);

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
				   
INSERT INTO `RoleModulePermissions` (`id`, `roleId`, `module`, `permission`) VALUES 
(1, 1, 'Project', 'write'),
(2, 1, 'Todo', 'write');

COMMIT;
