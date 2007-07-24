DROP TABLE IF EXISTS module_instance
;
DROP TABLE IF EXISTS user_module_setting
;
DROP TABLE IF EXISTS project
;
DROP TABLE IF EXISTS project_user_role_rel
;
DROP TABLE IF EXISTS history
;
DROP TABLE IF EXISTS user
;
DROP TABLE IF EXISTS role
;
DROP TABLE IF EXISTS database_manager
;



CREATE TABLE module_instance
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	projectId MEDIUMINT,
	module VARCHAR(250) NOT NULL,
	name VARCHAR(250) NOT NULL,
	PRIMARY KEY (id),
	KEY (projectId)
)
;


CREATE TABLE user_module_setting
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	userId MEDIUMINT NOT NULL,
	keyValue VARCHAR(250) NOT NULL,
	value VARCHAR(250) NOT NULL,
	module VARCHAR(50) NOT NULL,
	PRIMARY KEY (id),
	KEY (userId)
)
;


CREATE TABLE project
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	parent MEDIUMINT,
	path VARCHAR(25) NOT NULL DEFAULT "\\",
	title VARCHAR(250) NOT NULL,
	notes TEXT NOT NULL,
	ownerId MEDIUMINT,
	startDate DATETIME,
	endDate DATETIME,
	priority INTEGER,
	currentStatus VARCHAR(50) NOT NULL DEFAULT 'working',
	completePercent FLOAT(0) DEFAULT 0,
	hourlyWageRate FLOAT(0),
	budget FLOAT(0),
	PRIMARY KEY (id),
	KEY (ownerId)
)
;


CREATE TABLE project_user_role_rel
(
	projectId MEDIUMINT NOT NULL,
	userId MEDIUMINT NOT NULL,
	roleId MEDIUMINT NOT NULL,
	KEY (projectId),
	KEY (roleId),
	KEY (userId)
)
;


CREATE TABLE history
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	userId MEDIUMINT NOT NULL,
	dataobjectId MEDIUMINT NOT NULL,
	module VARCHAR(50) NOT NULL,
	oldValue VARCHAR(100) NOT NULL,
	newValue VARCHAR(250) NOT NULL,
	action VARCHAR(50) NOT NULL,
	PRIMARY KEY (id),
	KEY (userId)
)
;


CREATE TABLE user
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	username VARCHAR(250) NOT NULL,
	password VARCHAR(32) NOT NULL,
	firstname VARCHAR(250),
	lastname VARCHAR(250),
	language VARCHAR(5) NOT NULL,
	PRIMARY KEY (id),
	UNIQUE (username)
)
;


CREATE TABLE role
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	module VARCHAR(250) NOT NULL,
	permission VARCHAR(50) NOT NULL,
	PRIMARY KEY (id)
)
;


CREATE TABLE database_manager
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	tableName VARCHAR(50),
	tableField VARCHAR(60),
	formTab INTEGER,
	formLabel VARCHAR(255),
	formTooltip VARCHAR(255),
	formType VARCHAR(50),
	formPosition INTEGER,
	formColumns INTEGER,
	formRegexp VARCHAR(255),
	formRange TEXT,
	defaultValue VARCHAR(255),
	listPosition INTEGER,
	listAlign VARCHAR(20),
	listUseFilter TINYINT,
	altPosition INTEGER,
	status VARCHAR(20),
	isInteger TINYINT,
	isRequired TINYINT,
	isUnique INTEGER,
	PRIMARY KEY (id)
)
;


## FOR TEST ##
INSERT INTO `database_manager` VALUES (1, 'project', 'title', 1, 'title', 'title', 'text', 1, 1, '', NULL, '', 1, 'left', 1, 1, '', 0, 1, 0);
INSERT INTO `database_manager` VALUES (2, 'project', 'notes', 1, 'notes', 'notes', 'textarea', 2, 2, '', NULL, '', 3, 'left', 1, 2, '1', 0, 1, 0);
INSERT INTO `database_manager` VALUES (3, 'project', 'priority', 1, 'priority', 'priority', 'text', 3, 1, NULL, NULL, '5', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL);