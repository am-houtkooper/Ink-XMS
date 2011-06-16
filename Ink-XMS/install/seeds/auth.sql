CREATE TABLE IF NOT EXISTS `auth` (
  `name` varchar(10) NOT NULL DEFAULT '',
  `pass` varchar(40) NOT NULL DEFAULT '',
  `site` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;