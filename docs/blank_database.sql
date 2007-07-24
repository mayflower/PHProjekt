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
	project_id MEDIUMINT,
	module VARCHAR(250) NOT NULL,
	name VARCHAR(250) NOT NULL,
	PRIMARY KEY (id),
	KEY (project_id)
)
;


CREATE TABLE user_module_setting
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	user_id MEDIUMINT NOT NULL,
	key_value VARCHAR(250) NOT NULL,
	value VARCHAR(250) NOT NULL,
	module VARCHAR(50) NOT NULL,
	PRIMARY KEY (id),
	KEY (user_id)
)
;


CREATE TABLE project
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	parent MEDIUMINT,
	path VARCHAR(25) NOT NULL DEFAULT "\\",
	title VARCHAR(250) NOT NULL,
	notes TEXT NOT NULL,
	owner_id MEDIUMINT,
	start_date DATETIME,
	end_date DATETIME,
	priority INTEGER,
	current_status VARCHAR(50) NOT NULL DEFAULT 'working',
	complete_percent FLOAT(0) DEFAULT 0,
	hourly_wage_rate FLOAT(0),
	budget FLOAT(0),
	PRIMARY KEY (id),
	KEY (owner_id)
)
;


CREATE TABLE project_user_role_rel
(
	project_id MEDIUMINT NOT NULL,
	user_id MEDIUMINT NOT NULL,
	role_id MEDIUMINT NOT NULL,
	KEY (project_id),
	KEY (role_id),
	KEY (user_id)
)
;


CREATE TABLE history
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	user_id MEDIUMINT NOT NULL,
	dataobject_id MEDIUMINT NOT NULL,
	module VARCHAR(50) NOT NULL,
	old_value VARCHAR(100) NOT NULL,
	new_value VARCHAR(250) NOT NULL,
	action VARCHAR(50) NOT NULL,
	PRIMARY KEY (id),
	KEY (user_id)
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