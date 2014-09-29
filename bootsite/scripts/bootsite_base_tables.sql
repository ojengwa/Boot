########################
#   BootSite Config
########################
CREATE TABLE IF NOT EXISTS `bootsite_config` (
	`id` int NOT NULL AUTO_INCREMENT,
	`bootsite_users` TINYINT(1) NOT NULL default 1,
	`bootsite_emails` TINYINT(1) NOT NULL default 1,
	`active` TINYINT(1) NOT NULL default 1,
	PRIMARY KEY (`id`)
);

INSERT INTO `bootsite_config`
VALUES ('', 1, 1, 1);

########################
#		Users
########################
CREATE TABLE IF NOT EXISTS `bootsite_users` (
	`id` int NOT NULL AUTO_INCREMENT,
	`email` varchar(250) NOT NULL default '',
	`firstname` varchar(250) NOT NULL default '',
	`lastname` varchar(250) NOT NULL default '',
	`password` varchar(128) NOT NULL default '',
	`confirmed` TINYINT(1) NOT NULL default 0,
	`active` TINYINT(1) NOT NULL default 0,
	PRIMARY KEY (`id`)
);

