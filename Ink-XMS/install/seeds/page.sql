CREATE TABLE IF NOT EXISTS `xSITE_NAMESPACEx_page` (
  `title` varchar(100) NOT NULL,
  `language` enum('Nederlands','Francais','Deutsch','English') NOT NULL DEFAULT 'Francais',
  `content` longtext NOT NULL,
  `filetype` enum('xml','txt','bin') NOT NULL DEFAULT 'xml',
  `updated` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`title`,`language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;