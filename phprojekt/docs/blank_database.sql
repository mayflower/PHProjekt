-- MySQL dump 10.11
--
-- Host: localhost    Database: phprojekt-mvc-test
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
  `formTab` int default NULL,
  `formLabel` varchar(255) default NULL,
  `formTooltip` varchar(255) default NULL,
  `formType` varchar(50) default NULL,
  `formPosition` int default NULL,
  `formColumns` int default NULL,
  `formRegexp` varchar(255) default NULL,
  `formRange` text,
  `defaultValue` varchar(255) default NULL,
  `listPosition` int default NULL,
  `listAlign` varchar(20) default NULL,
  `listUseFilter` smallint default NULL,
  `altPosition` int default NULL,
  `status` varchar(20) default NULL,
  `isInteger` smallint default NULL,
  `isRequired` smallint default NULL,
  `isUnique` int default NULL,
  PRIMARY KEY  (`id`)
);

--
-- Table structure for table `History`
--

DROP TABLE IF EXISTS `History`;
CREATE TABLE `History` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `dataobjectId` int NOT NULL,
  `module` varchar(50) NOT NULL,
  `oldValue` varchar(100) NOT NULL,
  `newValue` varchar(250) NOT NULL,
  `action` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE INDEX `History_userId` ON `History`(`userId`);

--
-- Table structure for table `ModuleInstance`
--

DROP TABLE IF EXISTS `ModuleInstance`;
CREATE TABLE `ModuleInstance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projectId` int default NULL,
  `module` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE INDEX `ModuleInstance_userId` ON `ModuleInstance`(`projectId`);
--
-- Table structure for table `Project`
--

DROP TABLE IF EXISTS `Project`;
CREATE TABLE `Project` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent` int default NULL,
  `path` varchar(25) NOT NULL default '\\',
  `title` varchar(250) NOT NULL,
  `notes` text NOT NULL,
  `ownerId` int default NULL,
  `startDate` datetime default NULL,
  `endDate` datetime default NULL,
  `priority` int default NULL,
  `currentStatus` varchar(50) NOT NULL default 'working',
  `completePercent` float default '0',
  `hourlyWageRate` float default NULL,
  `budget` float default NULL,
  PRIMARY KEY(`id`)
);
CREATE INDEX `Project_ownerId` ON `Project`(`ownerId`);

--
-- Table structure for table `ProjectUserRoleRelation`
--

DROP TABLE IF EXISTS `ProjectUserRoleRelation`;
CREATE TABLE `ProjectUserRoleRelation` (
  `projectId` int NOT NULL,
  `userId` int NOT NULL,
  `roleId` int NOT NULL
);

CREATE INDEX `ProjectUserRoleRelation_projectId` ON `ProjectUserRoleRelation`(`projectId`);
CREATE INDEX `ProjectUserRoleRelation_userId` ON `ProjectUserRoleRelation`(`userId`);
CREATE INDEX `ProjectUserRoleRelation_roleId` ON `ProjectUserRoleRelation`(`roleId`);

--
-- Table structure for table `Role`
--

DROP TABLE IF EXISTS `Role`;
CREATE TABLE `Role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `module` varchar(250) NOT NULL,
  `permission` varchar(50) NOT NULL,
  PRIMARY KEY(`id`)
);

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(250) NOT NULL,
  `password` varchar(32) NOT NULL,
  `firstname` varchar(250) default NULL,
  `lastname` varchar(250) default NULL,
  `language` varchar(5) NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE(`username`)
);

--
-- Table structure for table `UserModuleSetting`
--
DROP TABLE IF EXISTS `UserModuleSetting`;
CREATE TABLE `UserModuleSetting` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `keyValue` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  `module` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE INDEX `UserModuleSetting_userId` ON `UserModuleSetting`(`userId`);

COMMIT;