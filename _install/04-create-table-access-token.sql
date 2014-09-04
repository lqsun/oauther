CREATE TABLE IF NOT EXISTS `oauther`.`access_tokens` (
 `token_no` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing no of each token, unique index',
 `token_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'token hash',
 `token_uid` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'token''s user id',
 `token_client_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'token''s appKey',
 `token_scope` varchar(256) COLLATE utf8_unicode_ci NOT NULL COMMENT 'token''s scope',
 `token_create_at` bigint(20) DEFAULT NULL COMMENT 'create time of token',
 `token_expire_in` bigint(20) DEFAULT NULL COMMENT 'expire time of token',
 PRIMARY KEY (`token_no`),
 UNIQUE KEY `token_id` (`token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='access token';
