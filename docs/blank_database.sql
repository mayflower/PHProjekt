-- MySQL dump 10.11
--
-- Host: localhost    Database: phprojekt-mvc
-- ------------------------------------------------------
-- Server version	5.0.38-Ubuntu_0ubuntu1-log

BEGIN;

-- 
-- Tabellenstruktur für Tabelle `databasemanager`
-- 

DROP TABLE IF EXISTS `databasemanager`;
CREATE TABLE `databasemanager` (
  `id` int(11) NOT NULL auto_increment,
  `tableName` varchar(50) collate latin1_general_ci default NULL,
  `tableField` varchar(60) collate latin1_general_ci default NULL,
  `formTab` int(11) default NULL,
  `formLabel` varchar(255) collate latin1_general_ci default NULL,
  `formTooltip` varchar(255) collate latin1_general_ci default NULL,
  `formType` varchar(50) collate latin1_general_ci default NULL,
  `formPosition` int(11) default NULL,
  `formColumns` int(11) default NULL,
  `formRegexp` varchar(255) collate latin1_general_ci default NULL,
  `formRange` text collate latin1_general_ci,
  `defaultValue` varchar(255) collate latin1_general_ci default NULL,
  `listPosition` int(11) default NULL,
  `listAlign` varchar(20) collate latin1_general_ci default NULL,
  `listUseFilter` smallint(6) default NULL,
  `altPosition` int(11) default NULL,
  `status` varchar(20) collate latin1_general_ci default NULL,
  `isInteger` smallint(6) default NULL,
  `isRequired` smallint(6) default NULL,
  `isUnique` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=29 ;

-- 
-- Daten für Tabelle `databasemanager`
-- 

INSERT INTO `databasemanager` (`id`, `tableName`, `tableField`, `formTab`, `formLabel`, `formTooltip`, `formType`, `formPosition`, `formColumns`, `formRegexp`, `formRange`, `defaultValue`, `listPosition`, `listAlign`, `listUseFilter`, `altPosition`, `status`, `isInteger`, `isRequired`, `isUnique`) VALUES 
(1, 'Project', 'parent', 1, 'parent', 'parent', 'tree', 1, 1, NULL, 'Project', '1', 2, 'left', 1, 1, '1', 1, 0, 0),
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
(17, 'History', 'userId', 1, 'UserId', 'UserId', 'userId', 1, 1, NULL, NULL, '0', 1, 'left', 1, 1, '1', 1, 1, 0),
(18, 'History', 'dataobjectId', 1, 'DataobjectId', 'DataobjectId', 'text', 2, 1, NULL, NULL, '0', 2, 'center', 1, 2, '1', 1, 1, 0),
(19, 'History', 'module', 1, 'Module', 'Module', 'text', 3, 1, NULL, NULL, '', 3, 'left', 1, 3, '1', 0, 1, 0),
(20, 'History', 'field', 1, 'Field', 'Field', 'text', 4, 1, NULL, NULL, '', 4, 'left', 1, 4, '1', 0, 1, 0),
(21, 'History', 'oldValue', 1, 'OldValue', 'OldValue', 'text', 5, 1, NULL, NULL, '', 0, '', 0, 0, '1', 0, 1, 0),
(22, 'History', 'newValue', 1, 'NewValue', 'NewValue', 'text', 6, 1, NULL, NULL, '', 0, '', 0, 0, '1', 0, 1, 0),
(23, 'History', 'action', 1, 'Action', 'Action', 'text', 7, 1, NULL, NULL, '', 7, 'left', 1, 7, '1', 0, 1, 0),
(24, 'History', 'datetime', 1, 'Datetime', 'Datetime', 'datetime', 8, 1, NULL, NULL, '', 8, 'center', 1, 8, '1', 0, 1, 0),
(25, 'Note', 'projectId', 1, 'project', 'project', 'tree', 1, 1, NULL, 'Project', '', 2, 'left', 1, 1, '1', 0, 1, 0),
(26, 'Note', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
(27, 'Note', 'comments', 1, 'comments', 'comments', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 1, 0),
(28, 'Note', 'category', 1, 'category', 'category', 'selectSqlAddOne', 4, 2, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `default`
-- 

DROP TABLE IF EXISTS `default`;
CREATE TABLE `default` (
  `ID` int(8) NOT NULL auto_increment,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `default`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `groups`
-- 

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=5 ;

-- 
-- Daten für Tabelle `groups`
-- 

INSERT INTO `groups` (`id`, `name`) VALUES 
(1, 'default'),
(2, 'ninatest'),
(3, 'ninasgruppe'),
(4, 'testgruppe');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `groupsuserrelation`
-- 

DROP TABLE IF EXISTS `groupsuserrelation`;
CREATE TABLE `groupsuserrelation` (
  `id` int(11) NOT NULL auto_increment,
  `groupsId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=4 ;

-- 
-- Daten für Tabelle `groupsuserrelation`
-- 

INSERT INTO `groupsuserrelation` (`id`, `groupsId`, `userId`) VALUES 
(1, 1, 1),
(2, 2, 2),
(3, 3, 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `history`
-- 

DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `id` mediumint(9) NOT NULL auto_increment,
  `userId` mediumint(9) NOT NULL,
  `dataobjectId` mediumint(9) NOT NULL,
  `module` varchar(50) collate latin1_general_ci NOT NULL,
  `field` varchar(255) collate latin1_general_ci NOT NULL,
  `oldValue` varchar(100) collate latin1_general_ci default NULL,
  `newValue` varchar(250) collate latin1_general_ci default NULL,
  `action` varchar(50) collate latin1_general_ci NOT NULL,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`),
  KEY `History_userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=12 ;

-- 
-- Daten für Tabelle `history`
-- 

INSERT INTO `history` (`id`, `userId`, `dataobjectId`, `module`, `field`, `oldValue`, `newValue`, `action`, `datetime`) VALUES 
(1, 1, 2, 'Project', 'startDate', NULL, '2007-10-10', 'edit', '2007-11-30 12:21:21'),
(2, 1, 2, 'Project', 'endDate', NULL, '2007-11-11', 'edit', '2007-11-30 12:21:21'),
(3, 1, 2, 'Project', 'priority', NULL, '1', 'edit', '2007-11-30 12:21:21'),
(4, 1, 2, 'Project', 'currentStatus', 'working', '1', 'edit', '2007-11-30 12:21:21'),
(5, 1, 1, 'Todo', 'projectId', '', '4', 'add', '2007-11-30 17:02:35'),
(6, 1, 1, 'Todo', 'title', '', 'blabla', 'add', '2007-11-30 17:02:35'),
(7, 1, 1, 'Todo', 'notes', '', 'hsadkjhdsa ', 'add', '2007-11-30 17:02:35'),
(8, 1, 1, 'Todo', 'startDate', '', '2007-10-10', 'add', '2007-11-30 17:02:35'),
(9, 1, 1, 'Todo', 'endDate', '', '2007-11-11', 'add', '2007-11-30 17:02:35'),
(10, 1, 1, 'Todo', 'priority', '', '1', 'add', '2007-11-30 17:02:35'),
(11, 1, 1, 'Todo', 'currentStatus', '', '1', 'add', '2007-11-30 17:02:35');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `moduleinstance`
-- 

DROP TABLE IF EXISTS `moduleinstance`;
CREATE TABLE `moduleinstance` (
  `id` int(11) NOT NULL auto_increment,
  `projectId` int(11) default NULL,
  `module` varchar(250) collate latin1_general_ci NOT NULL,
  `name` varchar(250) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ModuleInstance_userId` (`projectId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `moduleinstance`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `note`
-- 

DROP TABLE IF EXISTS `note`;
CREATE TABLE `note` (
  `id` int(11) NOT NULL auto_increment,
  `projectId` int(11) default NULL,
  `title` varchar(250) collate latin1_general_ci NOT NULL,
  `comments` text collate latin1_general_ci,
  `category` varchar(50) collate latin1_general_ci default NULL,
  `ownerId` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `note`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `project`
-- 

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(11) NOT NULL auto_increment,
  `parent` int(11) default NULL,
  `path` varchar(25) collate latin1_general_ci NOT NULL default '\\',
  `title` varchar(250) collate latin1_general_ci NOT NULL,
  `notes` text collate latin1_general_ci,
  `ownerId` int(11) default NULL,
  `startDate` date default NULL,
  `endDate` date default NULL,
  `priority` int(11) default NULL,
  `currentStatus` varchar(50) collate latin1_general_ci NOT NULL default 'working',
  `completePercent` float default '0',
  `hourlyWageRate` float default NULL,
  `budget` float default NULL,
  `read` int(11) default NULL,
  `write` int(11) default NULL,
  `admin` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `Project_ownerId` (`ownerId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=5 ;

-- 
-- Daten für Tabelle `project`
-- 

INSERT INTO `project` (`id`, `parent`, `path`, `title`, `notes`, `ownerId`, `startDate`, `endDate`, `priority`, `currentStatus`, `completePercent`, `hourlyWageRate`, `budget`, `read`, `write`, `admin`) VALUES 
(1, NULL, '/', 'Invisible Root', '', NULL, NULL, NULL, NULL, 'working', 0, NULL, NULL, 1, 1, 1),
(2, 1, '/1/', 'Project 1', '', 1, '2007-10-10', '2007-11-11', 1, '1', 0, NULL, 0, 1, 1, NULL),
(3, 1, '/1/', 'Project 2', '', 1, NULL, NULL, NULL, 'working', 0, NULL, NULL, NULL, NULL, NULL),
(4, 2, '/1/2/', 'Sub Project', '', NULL, NULL, NULL, NULL, 'working', 0, NULL, NULL, 3, NULL, NULL);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `projectuserrolerelation`
-- 

DROP TABLE IF EXISTS `projectuserrolerelation`;
CREATE TABLE `projectuserrolerelation` (
  `projectId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  KEY `ProjectUserRoleRelation_projectId` (`projectId`),
  KEY `ProjectUserRoleRelation_userId` (`userId`),
  KEY `ProjectUserRoleRelation_roleId` (`roleId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- 
-- Daten für Tabelle `projectuserrolerelation`
-- 

INSERT INTO `projectuserrolerelation` (`projectId`, `userId`, `roleId`) VALUES 
(1, 1, 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `role`
-- 

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(250) collate latin1_general_ci NOT NULL,
  `parent` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `role`
-- 

INSERT INTO `role` (`id`, `name`, `parent`) VALUES 
(1, 'admin', 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `rolemodulepermissions`
-- 

DROP TABLE IF EXISTS `rolemodulepermissions`;
CREATE TABLE `rolemodulepermissions` (
  `id` int(11) NOT NULL auto_increment,
  `role_id` int(8) NOT NULL,
  `module` varchar(250) collate latin1_general_ci NOT NULL,
  `permission` varchar(50) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=5 ;

-- 
-- Daten für Tabelle `rolemodulepermissions`
-- 

INSERT INTO `rolemodulepermissions` (`id`, `role_id`, `module`, `permission`) VALUES 
(1, 1, 'Project', 'write'),
(2, 1, 'Todo', 'write');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `searchwords`
-- 

DROP TABLE IF EXISTS `searchwords`;
CREATE TABLE `searchwords` (
  `module` varchar(255) collate latin1_general_ci NOT NULL,
  `itemId` int(11) NOT NULL,
  `word` varchar(255) collate latin1_general_ci NOT NULL,
  `crc32` int(11) NOT NULL,
  PRIMARY KEY  (`itemId`,`module`,`crc32`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- 
-- Daten für Tabelle `searchwords`
-- 

INSERT INTO `searchwords` (`module`, `itemId`, `word`, `crc32`) VALUES 
('Project', 2, 'PROJECT', 284569332),
('Todo', 1, 'BLABLA', 1676716149),
('Todo', 1, 'HSADKJHDSA', -1092005488);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `todo`
-- 

DROP TABLE IF EXISTS `todo`;
CREATE TABLE `todo` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(250) collate latin1_general_ci NOT NULL,
  `notes` text collate latin1_general_ci,
  `ownerId` int(11) default NULL,
  `projectId` int(11) default NULL,
  `startDate` date default NULL,
  `endDate` date default NULL,
  `priority` int(11) default NULL,
  `currentStatus` varchar(50) collate latin1_general_ci NOT NULL default 'working',
  `read` int(11) default NULL,
  `write` int(11) default NULL,
  `admin` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `todo`
-- 

INSERT INTO `todo` (`id`, `title`, `notes`, `ownerId`, `projectId`, `startDate`, `endDate`, `priority`, `currentStatus`, `read`, `write`, `admin`) VALUES 
(1, 'blabla', 'hsadkjhdsa ', 1, 4, '2007-10-10', '2007-11-11', 1, '1', NULL, NULL, NULL);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `user`
-- 

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(250) collate latin1_general_ci NOT NULL,
  `password` varchar(32) collate latin1_general_ci NOT NULL,
  `firstname` varchar(250) collate latin1_general_ci default NULL,
  `lastname` varchar(250) collate latin1_general_ci default NULL,
  `email` varchar(250) collate latin1_general_ci default NULL,
  `language` varchar(5) collate latin1_general_ci NOT NULL,
  `status` varchar(1) collate latin1_general_ci default 'A',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `user`
-- 

INSERT INTO `user` (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `language`, `status`) VALUES 
(1, 'dsp', '156c3239dbfa5c5222b51514e9d12948', NULL, NULL, 'gustavo.solt@gmail.com', '', 'A'),
(2, 'nina', '156c3239dbfa5c5222b51514e9d12948', NULL, NULL, 'nina@infochick.de', '', 'A');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `usermodulesetting`
-- 

DROP TABLE IF EXISTS `usermodulesetting`;
CREATE TABLE `usermodulesetting` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `keyValue` varchar(250) collate latin1_general_ci NOT NULL,
  `value` varchar(250) collate latin1_general_ci NOT NULL,
  `module` varchar(50) collate latin1_general_ci NOT NULL,
  `identifier` varchar(50) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `UserModuleSetting_userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `usermodulesetting`
-- 




COMMIT;
