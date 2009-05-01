CREATE TABLE IF NOT EXISTS `attachments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `model` varchar(255) NOT NULL,
  `foreign_key` int(10) NOT NULL,
  `dirname` varchar(255) default NULL,
  `basename` varchar(255) NOT NULL,
  `checksum` varchar(255) NOT NULL,
  `alternative` varchar(50) default NULL,
  `group` varchar(255) default NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`)
);

