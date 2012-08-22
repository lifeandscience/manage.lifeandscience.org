CREATE TABLE IF NOT EXISTS `json_tweets` (
  `id` bigint(20) unsigned NOT NULL,
  `cache_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at_date` date DEFAULT NULL,
  `search_query` varchar(40) DEFAULT NULL,
  `raw_tweet` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `text` varchar(160) NOT NULL, 
  PRIMARY KEY (`cache_id`),
  KEY `id` (`id`),
  KEY `created_at` (`created_at`),
  KEY `search_query` (`search_query`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
