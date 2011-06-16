CREATE TABLE IF NOT EXISTS `xSITE_NAMESPACEx_photo` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(25) NOT NULL DEFAULT '',
  `image` longblob NOT NULL,
  `size` int(7) NOT NULL DEFAULT '0',
  `height` int(4) NOT NULL DEFAULT '0',
  `width` int(4) NOT NULL DEFAULT '0',
  `category` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
