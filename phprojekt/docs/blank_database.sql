DROP TABLE IF EXISTS module_instances
;
DROP TABLE IF EXISTS user_module_settings
;
DROP TABLE IF EXISTS projects
;
DROP TABLE IF EXISTS project_user_role_rel
;
DROP TABLE IF EXISTS history
;
DROP TABLE IF EXISTS users
;
DROP TABLE IF EXISTS roles
;
DROP TABLE IF EXISTS database_manager
;



CREATE TABLE module_instances
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	project_id MEDIUMINT,
	module VARCHAR(250) NOT NULL,
	name VARCHAR(250) NOT NULL,
	PRIMARY KEY (id),
	KEY (project_id)
) 
;


CREATE TABLE user_module_settings
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


CREATE TABLE projects
(
	id MEDIUMINT NOT NULL AUTO_INCREMENT,
	parent MEDIUMINT,
	path VARCHAR(25) NOT NULL,
	title VARCHAR(250) NOT NULL,
	owner_id MEDIUMINT,
	start_date DATETIME,
	end_date DATETIME,
	priority INTEGER,
	current_status VARCHAR(50) NOT NULL,
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


CREATE TABLE users
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


CREATE TABLE roles
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
	table_name VARCHAR(50),
	table_field VARCHAR(60),
	form_tab INTEGER,
	form_label VARCHAR(255),
	form_tooltip VARCHAR(255),
	form_type VARCHAR(50),
	form_position INTEGER,
	form_columns INTEGER,
	form_regexp VARCHAR(255),
	form_range TEXT,
	default_value VARCHAR(255),
	list_position INTEGER,
	list_align VARCHAR(20),
	list_use_filter TINYINT,
	alt_position INTEGER,
	status VARCHAR(20),
	is_integer TINYINT,
	is_required TINYINT,
	is_unique INTEGER,
	PRIMARY KEY (id)
) 
;
