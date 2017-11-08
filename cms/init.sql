DROP TABLE IF EXISTS `elements`;
CREATE TABLE `elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) DEFAULT NULL,
  `element_id` char(32) COLLATE utf8_slovak_ci NOT NULL,
  `tag` varchar(16) COLLATE utf8_slovak_ci NOT NULL,
  `attr` text COLLATE utf8_slovak_ci NOT NULL,
  `text` text COLLATE utf8_slovak_ci NOT NULL,
  `parent_id` char(32) COLLATE utf8_slovak_ci NOT NULL,
  `self_closing` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  CONSTRAINT `elements_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_slovak_ci NOT NULL,
  `closing_tags` text COLLATE utf8_slovak_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
