CREATE TABLE IF NOT EXISTS `mc_indexnow` (
  `id_indexnow` smallint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `apikey` varchar(125) DEFAULT NULL,
  `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_indexnow`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;