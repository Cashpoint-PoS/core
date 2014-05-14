CREATE TABLE `plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `displayname` text NOT NULL,
  `directory` text NOT NULL,
  `active` int(11) NOT NULL DEFAULT '0',
  `menu_order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8