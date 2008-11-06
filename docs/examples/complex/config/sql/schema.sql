CREATE TABLE IF NOT EXISTS `movies` (
  `id` int(100) NOT NULL auto_increment,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `title` varchar(200) default NULL,
  `director` varchar(250) default NULL,
  PRIMARY KEY  (`id`)
);

